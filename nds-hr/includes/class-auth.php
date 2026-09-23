<?php
/**
 * Unified Independent Authentication and Routing Engine for NDS HR.
 * Completely isolates NDS HR from WordPress users, authenticating directly against
 * wp_nds_hr_users and managing independent sessions via NDS_HR_Session.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Auth
 */
class NDS_HR_Auth {

	/**
	 * Query variable for NDS HR routes.
	 */
	const QUERY_VAR_ROUTE = 'nds_hr_route';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Capability-based wp-admin access protection
		add_action( 'admin_init', array( $this, 'restrict_admin_access' ) );

		// Intercept standard wp-login.php and redirect to canonical /hr/login/
		add_action( 'init', array( $this, 'intercept_standard_login' ) );

		// Register canonical /hr/ and /login/ rewrite rules
		add_action( 'init', array( $this, 'register_auth_endpoints' ) );
		add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 99 );
		add_filter( 'query_vars', array( $this, 'register_auth_query_vars' ) );
		add_action( 'parse_request', array( $this, 'parse_hr_requests' ) );
		add_action( 'template_redirect', array( $this, 'handle_auth_routes' ), 1 );

		// Filter login URL throughout WordPress
		add_filter( 'login_url', array( $this, 'filter_login_url' ), 10, 3 );
	}

	/**
	 * Register frontend rewrite rules for canonical /hr/ routes.
	 */
	public function register_auth_endpoints() {
		NDS_HR_Router::register_rewrite_rules();
	}

	/**
	 * Safely and conditionally flush rewrite rules only on routing version changes.
	 * Never flushes on every normal request.
	 */
	public function maybe_flush_rewrite_rules() {
		$stored_version = get_option( 'nds_hr_rewrite_version' );
		if ( defined( 'NDS_HR_ROUTING_VERSION' ) && NDS_HR_ROUTING_VERSION !== $stored_version ) {
			NDS_HR_Router::register_rewrite_rules();
			flush_rewrite_rules( false );
			update_option( 'nds_hr_rewrite_version', NDS_HR_ROUTING_VERSION );
		}
	}

	/**
	 * Register query variables for NDS HR routing.
	 *
	 * @param array $vars
	 * @return array
	 */
	public function register_auth_query_vars( $vars ) {
		return NDS_HR_Router::register_query_vars( $vars );
	}

	/**
	 * Explicitly parse and intercept NDS HR requests during WordPress request parsing.
	 * Guarantees that canonical routes (/hr/setup/, /hr/login/, /hr/, /employee/, etc.)
	 * are intercepted deterministically even if rewrite rules in DB are stale or permalinks are unconfigured.
	 *
	 * @param WP $wp Current WordPress environment instance.
	 */
	public function parse_hr_requests( $wp ) {
		NDS_HR_Router::parse_request( $wp );
	}

	/**
	 * Filter wp_login_url() to return canonical /hr/login/ URL.
	 *
	 * @param string $login_url
	 * @param string $redirect
	 * @param bool   $force_reauth
	 * @return string
	 */
	public function filter_login_url( $login_url, $redirect = '', $force_reauth = false ) {
		$args = array();
		if ( ! empty( $redirect ) ) {
			$args['redirect_to'] = $redirect;
		}
		if ( $force_reauth ) {
			$args['reauth'] = '1';
		}
		return NDS_HR_Router::url( 'login', $args );
	}

	/**
	 * Intercept standard wp-login.php requests and redirect to canonical /hr/login/.
	 * Pure redirection without any insecure query-parameter bypass.
	 */
	public function intercept_standard_login() {
		global $pagenow;

		if ( 'wp-login.php' === $pagenow && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
			$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'login';
			if ( 'login' === $action ) {
				$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';
				$args        = array();
				if ( ! empty( $redirect_to ) ) {
					$args['redirect_to'] = $redirect_to;
				}
				wp_safe_redirect( NDS_HR_Router::url( 'login', $args ) );
				exit;
			}
		}
	}

	/**
	 * Route and dispatch template handlers for /hr/ routes.
	 */
	public function handle_auth_routes() {
		$route = get_query_var( self::QUERY_VAR_ROUTE );
		if ( empty( $route ) && 1 === (int) get_query_var( 'nds_hr_login' ) ) {
			$route = 'login';
		}

		if ( empty( $route ) ) {
			return;
		}

		// Clear any WordPress 404 status and set HTTP 200 OK
		global $wp_query;
		if ( is_object( $wp_query ) ) {
			$wp_query->is_404 = false;
		}
		status_header( 200 );

		switch ( $route ) {
			case 'setup':
				$this->render_setup_route();
				break;

			case 'login':
				$this->render_login_route();
				break;

			case 'logout':
				$this->handle_logout_route();
				break;

			case 'reset_password':
				$this->render_reset_password_route();
				break;

			case 'admin':
				$this->render_admin_route();
				break;
		}
		exit;
	}

	/**
	 * Route: /hr/ (Standalone HR Administration Portal)
	 */
	protected function render_admin_route() {
		// 1. Authenticate HR session directly from wp_nds_hr_sessions
		$hr_user = NDS_HR_Session::get_authenticated_user();

		if ( ! $hr_user ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'redirect_to' => NDS_HR_Router::url( 'admin' ) ) ) );
			exit;
		}

		// 2. Require password change check
		if ( '1' === (string) $hr_user->require_password_change ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'require_pw_change' => 1 ) ) );
			exit;
		}

		// 3. Permission check for HR Administration
		$user_id = (int) $hr_user->id;
		if ( ! NDS_HR_Permissions::can_access_admin( $user_id ) ) {
			// Non-admin employee accessing /hr/ is redirected to /employee/
			wp_safe_redirect( NDS_HR_Router::url( 'employee' ) );
			exit;
		}

		// 4. Enqueue admin assets for frontend /hr/ view
		$plugin = nds_hr();
		if ( $plugin->admin ) {
			$plugin->admin->enqueue_admin_assets( 'nds-hr' );
		}

		// 5. Handle form submissions if triggered from /hr/
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			if ( isset( $_POST['nds_hr_employee_action'] ) && $plugin->admin && $plugin->admin->employees ) {
				$plugin->admin->employees->handle_form_submissions();
			}
			if ( isset( $_POST['nds_hr_roles_action'] ) && $plugin->admin && $plugin->admin->roles_controller ) {
				$plugin->admin->roles_controller->handle_form_submissions();
			}
		}

		if ( isset( $_GET['nds_hr_action'] ) && $plugin->admin && $plugin->admin->employees ) {
			$plugin->admin->employees->handle_form_submissions();
		}

		// 6. Determine Tab / View to render
		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ( get_query_var( 'nds_hr_tab' ) ? sanitize_key( get_query_var( 'nds_hr_tab' ) ) : 'dashboard' );
		if ( 'admin' === $tab ) {
			$tab = 'dashboard';
		}

		// Render standalone HR Admin shell
		include NDS_HR_PATH . 'templates/admin/admin-layout.php';
		exit;
	}

	/**
	 * Route: /hr/logout/
	 */
	protected function handle_logout_route() {
		$current_user = NDS_HR_Session::get_authenticated_user();
		if ( $current_user ) {
			NDS_HR_Audit_Logger::log(
				'user_logged_out',
				'hr_user',
				$current_user->id,
				array(),
				array( 'username' => $current_user->username )
			);
		}

		NDS_HR_Session::destroy_session();

		// If WP user was also logged in, sign them out safely
		if ( is_user_logged_in() ) {
			wp_logout();
		}

		wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'logged_out' => '1' ) ) );
		exit;
	}

	/**
	 * Check if any HR Administrator exists.
	 *
	 * @return bool
	 */
	public static function has_admin_user() {
		global $wpdb;
		$users_table = NDS_HR_Database::users_table();
		$roles_table = NDS_HR_Database::roles_table();

		$count = $wpdb->get_var(
			"SELECT COUNT(u.id) 
			 FROM {$users_table} u 
			 INNER JOIN {$roles_table} r ON u.role_id = r.id 
			 WHERE r.slug = 'hr_admin' AND u.status = 'active'"
		);

		return (int) $count > 0;
	}

	/**
	 * Route: /hr/setup/ - First HR Administrator Setup.
	 */
	protected function render_setup_route() {
		// If an administrator already exists, redirect to login
		if ( self::has_admin_user() ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login' ) );
			exit;
		}

		$error_message = '';
		$prefill_data  = array();

		// Handle setup submission
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['nds_hr_setup_action'] ) && 'create_first_admin' === $_POST['nds_hr_setup_action'] ) {
			if ( ! NDS_HR_Security::verify_nonce( 'nds_hr_setup_admin', 'nds_hr_setup_nonce' ) ) {
				$error_message = __( 'Security verification failed. Please refresh and try again.', 'nds-hr' );
			} else {
				$result = $this->process_first_admin_creation( $_POST );
				if ( is_wp_error( $result ) ) {
					$error_message = $result->get_error_message();
					$prefill_data  = array(
						'username'   => sanitize_user( wp_unslash( $_POST['username'] ?? '' ) ),
						'email'      => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
						'first_name' => sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) ),
						'last_name'  => sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) ),
					);
				} else {
					// Auto login created administrator
					NDS_HR_Session::create_session( $result['user_id'], true );
					wp_safe_redirect( NDS_HR_Router::url( 'admin' ) );
					exit;
				}
			}
		}

		include NDS_HR_PATH . 'templates/login/setup.php';
		exit;
	}

	/**
	 * Create the initial HR Administrator account in wp_nds_hr_users.
	 *
	 * @param array $data
	 * @return array|WP_Error
	 */
	protected function process_first_admin_creation( array $data ) {
		global $wpdb;

		$username   = sanitize_user( wp_unslash( $data['username'] ?? '' ) );
		$email      = sanitize_email( wp_unslash( $data['email'] ?? '' ) );
		$first_name = sanitize_text_field( wp_unslash( $data['first_name'] ?? '' ) );
		$last_name  = sanitize_text_field( wp_unslash( $data['last_name'] ?? '' ) );
		$password   = isset( $data['password'] ) ? $data['password'] : '';
		$confirm    = isset( $data['confirm_password'] ) ? $data['confirm_password'] : '';

		if ( empty( $username ) || strlen( $username ) < 3 ) {
			return new WP_Error( 'invalid_username', __( 'Username must be at least 3 characters long.', 'nds-hr' ) );
		}

		if ( empty( $email ) || ! is_email( $email ) ) {
			return new WP_Error( 'invalid_email', __( 'A valid email address is required.', 'nds-hr' ) );
		}

		if ( empty( $password ) || strlen( $password ) < 8 ) {
			return new WP_Error( 'weak_password', __( 'Password must be at least 8 characters long.', 'nds-hr' ) );
		}

		if ( $password !== $confirm ) {
			return new WP_Error( 'password_mismatch', __( 'Password confirmation does not match.', 'nds-hr' ) );
		}

		$users_table = NDS_HR_Database::users_table();
		$roles_table = NDS_HR_Database::roles_table();

		// Check uniqueness
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$users_table} WHERE username = %s OR email = %s LIMIT 1", $username, $email ) );
		if ( $exists ) {
			return new WP_Error( 'user_exists', __( 'A user with this username or email already exists.', 'nds-hr' ) );
		}

		// Get hr_admin role ID
		$admin_role_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$roles_table} WHERE slug = %s LIMIT 1", 'hr_admin' ) );
		if ( ! $admin_role_id ) {
			NDS_HR_Database::seed_auth_defaults();
			$admin_role_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$roles_table} WHERE slug = %s LIMIT 1", 'hr_admin' ) );
		}

		// Cryptographic password hash (never stored in plaintext)
		$password_hash = wp_hash_password( $password );
		$display_name  = trim( $first_name . ' ' . $last_name );
		if ( empty( $display_name ) ) {
			$display_name = $username;
		}

		$inserted = $wpdb->insert(
			$users_table,
			array(
				'username'                => $username,
				'email'                   => $email,
				'password_hash'           => $password_hash,
				'role_id'                 => (int) $admin_role_id,
				'status'                  => 'active',
				'first_name'              => $first_name,
				'last_name'               => $last_name,
				'display_name'            => $display_name,
				'require_password_change' => 0,
			),
			array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d' )
		);

		if ( false === $inserted ) {
			return new WP_Error( 'db_error', __( 'Failed to save the administrator account to the database.', 'nds-hr' ) );
		}

		$new_user_id = $wpdb->insert_id;

		// Audit the creation
		NDS_HR_Audit_Logger::log(
			'first_hr_admin_created',
			'hr_user',
			$new_user_id,
			array(),
			array( 'username' => $username, 'email' => $email )
		);

		return array(
			'user_id'  => $new_user_id,
			'username' => $username,
		);
	}

	/**
	 * Route: /hr/login/ - Unified Independent HR Login.
	 */
	protected function render_login_route() {
		// If no admin user exists, prompt setup flow first
		if ( ! self::has_admin_user() ) {
			wp_safe_redirect( NDS_HR_Router::url( 'setup' ) );
			exit;
		}

		// Handle first-login password change if active session requires it
		$this->handle_first_login_password_change();

		// If user is already authenticated via session
		$current_user = NDS_HR_Session::get_authenticated_user();
		if ( $current_user ) {
			if ( '1' === (string) $current_user->require_password_change ) {
				$this->render_login_view( true );
				exit;
			}

			$destination = $this->determine_post_login_url( $current_user );
			wp_safe_redirect( $destination );
			exit;
		}

		// Handle login form submission
		$this->handle_login_submission();

		$this->render_login_view( false );
		exit;
	}

	/**
	 * Handle login credentials verification.
	 */
	protected function handle_login_submission() {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['nds_hr_login_action'] ) || 'submit_login' !== $_POST['nds_hr_login_action'] ) {
			return;
		}

		if ( ! NDS_HR_Security::verify_nonce( 'nds_hr_unified_login', 'nds_hr_login_nonce' ) ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'login_error' => 'nonce_failed' ) ) );
			exit;
		}

		$identifier = isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '';
		$password   = isset( $_POST['pwd'] ) ? $_POST['pwd'] : '';
		$remember   = ! empty( $_POST['rememberme'] );

		if ( empty( $identifier ) ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'login_error' => 'empty_username' ) ) );
			exit;
		}

		if ( empty( $password ) ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'login_error' => 'empty_password', 'log' => $identifier ) ) );
			exit;
		}

		global $wpdb;
		$users_table = NDS_HR_Database::users_table();
		$roles_table = NDS_HR_Database::roles_table();

		// Query user from wp_nds_hr_users
		$query = "
			SELECT u.*, r.slug AS role_slug, r.name AS role_name 
			FROM {$users_table} u 
			LEFT JOIN {$roles_table} r ON u.role_id = r.id 
			WHERE (u.username = %s OR u.email = %s)
			LIMIT 1
		";

		$user = $wpdb->get_row( $wpdb->prepare( $query, $identifier, $identifier ) );

		if ( ! $user ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'login_error' => 'invalid_username', 'log' => $identifier ) ) );
			exit;
		}

		// Verify account status
		if ( 'active' !== $user->status ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'login_error' => 'account_inactive' ) ) );
			exit;
		}

		// Cryptographic password check
		if ( ! wp_check_password( $password, $user->password_hash, $user->id ) ) {
			// Audit failed login attempt
			NDS_HR_Audit_Logger::log(
				'login_failed_bad_password',
				'hr_user',
				$user->id,
				array(),
				array( 'identifier' => $identifier )
			);

			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'login_error' => 'incorrect_password', 'log' => $identifier ) ) );
			exit;
		}

		// Create independent session token
		NDS_HR_Session::create_session( $user->id, $remember );

		// Audit successful login
		NDS_HR_Audit_Logger::log(
			'user_logged_in',
			'hr_user',
			$user->id,
			array(),
			array( 'username' => $user->username, 'role' => $user->role_slug )
		);

		// If user must change password
		if ( '1' === (string) $user->require_password_change ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'require_pw_change' => 1 ) ) );
			exit;
		}

		// Validate custom redirect_to
		$redirect_param = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
		if ( ! empty( $redirect_param ) && false === strpos( $redirect_param, 'wp-login.php' ) && false === strpos( $redirect_param, '/hr/login' ) ) {
			$safe_destination = wp_validate_redirect( $redirect_param, '' );
			if ( ! empty( $safe_destination ) ) {
				wp_safe_redirect( $safe_destination );
				exit;
			}
		}

		$destination = $this->determine_post_login_url( $user );
		wp_safe_redirect( $destination );
		exit;
	}

	/**
	 * Process first login required password change.
	 */
	protected function handle_first_login_password_change() {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['nds_hr_login_action'] ) || 'change_required_password' !== $_POST['nds_hr_login_action'] ) {
			return;
		}

		$current_user = NDS_HR_Session::get_authenticated_user();
		if ( ! $current_user ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login' ) );
			exit;
		}

		if ( ! NDS_HR_Security::verify_nonce( 'nds_hr_password_change', 'nds_hr_pw_change_nonce' ) ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'require_pw_change' => 1, 'pw_error' => 'nonce_failed' ) ) );
			exit;
		}

		$new_password     = isset( $_POST['new_password'] ) ? $_POST['new_password'] : '';
		$confirm_password = isset( $_POST['confirm_password'] ) ? $_POST['confirm_password'] : '';

		if ( empty( $new_password ) || empty( $confirm_password ) ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'require_pw_change' => 1, 'pw_error' => 'empty_fields' ) ) );
			exit;
		}

		if ( $new_password !== $confirm_password ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'require_pw_change' => 1, 'pw_error' => 'password_mismatch' ) ) );
			exit;
		}

		if ( strlen( $new_password ) < 8 ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'require_pw_change' => 1, 'pw_error' => 'password_too_short' ) ) );
			exit;
		}

		global $wpdb;
		$users_table   = NDS_HR_Database::users_table();
		$password_hash = wp_hash_password( $new_password );

		$wpdb->update(
			$users_table,
			array(
				'password_hash'           => $password_hash,
				'require_password_change' => 0,
			),
			array( 'id' => $current_user->id ),
			array( '%s', '%d' ),
			array( '%d' )
		);

		NDS_HR_Audit_Logger::log(
			'first_login_password_changed',
			'hr_user',
			$current_user->id,
			array( 'status' => 'password_change_required' ),
			array( 'status' => 'password_updated_successfully' )
		);

		$destination = $this->determine_post_login_url( $current_user );
		wp_safe_redirect( $destination );
		exit;
	}

	/**
	 * Route: /hr/reset-password/
	 * Security requirement 1: Only store cryptographic hash (SHA-256) of reset token in database.
	 * The raw token exists solely in the URL/email flow.
	 */
	protected function render_reset_password_route() {
		global $wpdb;
		$users_table = NDS_HR_Database::users_table();

		$step          = isset( $_GET['token'] ) ? 'new_password' : 'request';
		$raw_token     = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
		$error_message = '';
		$success_msg   = '';

		// 1. Handle Request Password Reset Link submission
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['nds_hr_reset_action'] ) && 'request_reset' === $_POST['nds_hr_reset_action'] ) {
			if ( ! NDS_HR_Security::verify_nonce( 'nds_hr_request_reset', 'nds_hr_reset_nonce' ) ) {
				$error_message = __( 'Security verification failed. Please try again.', 'nds-hr' );
			} else {
				$identifier = isset( $_POST['identifier'] ) ? sanitize_text_field( wp_unslash( $_POST['identifier'] ) ) : '';
				if ( ! empty( $identifier ) ) {
					$user = $wpdb->get_row( $wpdb->prepare( "SELECT id, email, username FROM {$users_table} WHERE username = %s OR email = %s LIMIT 1", $identifier, $identifier ) );
					if ( $user ) {
						// Generate 64-char raw token
						try {
							$raw_reset_token = bin2hex( random_bytes( 32 ) );
						} catch ( Exception $e ) {
							$raw_reset_token = wp_generate_password( 64, false, false );
						}

						// Store SHA-256 hash in database
						$token_hash = hash( 'sha256', $raw_reset_token );
						$expires_at = gmdate( 'Y-m-d H:i:s', time() + 3600 ); // 1 hour validity

						$wpdb->update(
							$users_table,
							array(
								'password_reset_token_hash' => $token_hash,
								'password_reset_expires_at' => $expires_at,
							),
							array( 'id' => $user->id ),
							array( '%s', '%s' ),
							array( '%d' )
						);

						$reset_link = NDS_HR_Router::url( 'reset-password', array( 'token' => $raw_reset_token ) );

						// Send password reset email
						$subject = __( 'NDS HR — Password Reset Request', 'nds-hr' );
						$message = sprintf(
							__( "Hello %s,\n\nA password reset request was initiated for your NDS HR account.\n\nClick the link below to set a new password:\n%s\n\nThis link expires in 60 minutes.\nIf you did not request this, please disregard.", 'nds-hr' ),
							$user->username,
							$reset_link
						);
						wp_mail( $user->email, $subject, $message );

						NDS_HR_Audit_Logger::log(
							'password_reset_requested',
							'hr_user',
							$user->id,
							array(),
							array( 'email' => $user->email )
						);
					}
				}
				// Generic success message to prevent user enumeration
				$success_msg = __( 'If the provided account exists, instructions to reset your password have been sent to the associated email address.', 'nds-hr' );
			}
		}

		// 2. Handle Setting New Password with Raw Token
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['nds_hr_reset_action'] ) && 'set_new_password' === $_POST['nds_hr_reset_action'] ) {
			if ( ! NDS_HR_Security::verify_nonce( 'nds_hr_set_password', 'nds_hr_reset_nonce' ) ) {
				$error_message = __( 'Security verification failed. Please try again.', 'nds-hr' );
			} else {
				$submitted_token = isset( $_POST['reset_token'] ) ? sanitize_text_field( wp_unslash( $_POST['reset_token'] ) ) : '';
				$new_password    = isset( $_POST['new_password'] ) ? $_POST['new_password'] : '';
				$confirm_password = isset( $_POST['confirm_password'] ) ? $_POST['confirm_password'] : '';

				if ( empty( $submitted_token ) ) {
					$error_message = __( 'Missing reset token.', 'nds-hr' );
				} elseif ( empty( $new_password ) || strlen( $new_password ) < 8 ) {
					$error_message = __( 'Password must be at least 8 characters long.', 'nds-hr' );
				} elseif ( $new_password !== $confirm_password ) {
					$error_message = __( 'Passwords do not match.', 'nds-hr' );
				} else {
					// Hash submitted token to look up user
					$token_hash = hash( 'sha256', $submitted_token );
					$user = $wpdb->get_row(
						$wpdb->prepare(
							"SELECT id, username FROM {$users_table} WHERE password_reset_token_hash = %s AND password_reset_expires_at > UTC_TIMESTAMP() LIMIT 1",
							$token_hash
						)
					);

					if ( ! $user ) {
						$error_message = __( 'This password reset link is invalid or has expired. Please request a new one.', 'nds-hr' );
					} else {
						// Update password and clear reset token hash
						$password_hash = wp_hash_password( $new_password );
						$wpdb->update(
							$users_table,
							array(
								'password_hash'             => $password_hash,
								'password_reset_token_hash' => null,
								'password_reset_expires_at' => null,
								'require_password_change'   => 0,
							),
							array( 'id' => $user->id ),
							array( '%s', '%s', '%s', '%d' ),
							array( '%d' )
						);

						NDS_HR_Audit_Logger::log(
							'password_reset_completed',
							'hr_user',
							$user->id,
							array(),
							array( 'username' => $user->username )
						);

						wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'password_reset_success' => '1' ) ) );
						exit;
					}
				}
			}
		}

		include NDS_HR_PATH . 'templates/login/reset-password.php';
		exit;
	}

	/**
	 * Route user after login based on capability evaluation.
	 *
	 * @param object $user
	 * @return string Target destination URL.
	 */
	public function determine_post_login_url( $user ) {
		$user_id = is_object( $user ) ? (int) $user->id : 0;

		// 1. Check if user has administrative capability
		if ( NDS_HR_Permissions::can( 'nds_hr_access_admin', $user_id ) ) {
			return NDS_HR_Router::url( 'admin' );
		}

		// 2. Check if user has employee portal access capability
		if ( NDS_HR_Permissions::can( 'nds_hr_access_employee', $user_id ) || NDS_HR_Permissions::can( 'nds_hr_view_own_profile', $user_id ) ) {
			return NDS_HR_Router::url( 'employee' );
		}

		return NDS_HR_Router::url( 'home' );
	}

	/**
	 * Restrict access to /wp-admin/ for NDS HR users.
	 * NDS HR users are never exposed to wp-admin and are redirected to /hr/ or /employee/.
	 * Standard WordPress administrator accounts with manage_options are permitted for WP site administration.
	 */
	public function restrict_admin_access() {
		if ( wp_doing_ajax() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			return;
		}

		// If authenticated as NDS HR User
		$hr_user = NDS_HR_Session::get_authenticated_user();
		if ( $hr_user ) {
			if ( '1' === (string) $hr_user->require_password_change ) {
				wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'require_pw_change' => 1 ) ) );
				exit;
			}

			if ( NDS_HR_Permissions::can_access_admin( (int) $hr_user->id ) ) {
				wp_safe_redirect( NDS_HR_Router::url( 'admin' ) );
				exit;
			} else {
				wp_safe_redirect( NDS_HR_Router::url( 'employee' ) );
				exit;
			}
		}
	}

	/**
	 * Render the unified branded login view template.
	 *
	 * @param bool $require_pw_change
	 */
	protected function render_login_view( $require_pw_change = false ) {
		include NDS_HR_PATH . 'templates/login/login.php';
	}

	/**
	 * Create an independent NDS HR application user account.
	 * Non-destructive: Does NOT create or touch WordPress users.
	 *
	 * @param array $account_data Username, email, password, etc.
	 * @return array|WP_Error Array with hr_user_id and plain password (for session display only), or WP_Error.
	 */
	public static function create_employee_user_account( array $account_data ) {
		global $wpdb;

		$username   = sanitize_user( $account_data['username'] ?? '' );
		$email      = sanitize_email( $account_data['email'] ?? '' );
		$first_name = sanitize_text_field( $account_data['first_name'] ?? '' );
		$last_name  = sanitize_text_field( $account_data['last_name'] ?? '' );
		$full_name  = sanitize_text_field( $account_data['full_name'] ?? trim( $first_name . ' ' . $last_name ) );

		if ( empty( $username ) ) {
			return new WP_Error( 'empty_username', __( 'Username is required to create a user account.', 'nds-hr' ) );
		}

		if ( empty( $email ) || ! is_email( $email ) ) {
			return new WP_Error( 'invalid_email', __( 'A valid email address is required.', 'nds-hr' ) );
		}

		$users_table = NDS_HR_Database::users_table();
		$roles_table = NDS_HR_Database::roles_table();

		// Check uniqueness in wp_nds_hr_users
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$users_table} WHERE username = %s LIMIT 1", $username ) );
		if ( $exists ) {
			return new WP_Error( 'username_exists', __( 'This username is already registered in NDS HR.', 'nds-hr' ) );
		}

		$email_exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$users_table} WHERE email = %s LIMIT 1", $email ) );
		if ( $email_exists ) {
			return new WP_Error( 'email_exists', __( 'This email address is already in use in NDS HR.', 'nds-hr' ) );
		}

		// Auto-generate strong secure password if blank
		$provided_password = ! empty( $account_data['password'] ) ? $account_data['password'] : '';
		$password          = ! empty( $provided_password ) ? $provided_password : wp_generate_password( 14, true, true );

		// Look up role_id for hr_employee (default role)
		$role_slug = ! empty( $account_data['role'] ) ? sanitize_key( $account_data['role'] ) : 'hr_employee';
		$role_id   = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$roles_table} WHERE slug = %s LIMIT 1", $role_slug ) );

		if ( ! $role_id ) {
			$role_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$roles_table} WHERE slug = 'hr_employee' LIMIT 1" ) );
		}

		// Cryptographic password hash
		$password_hash = wp_hash_password( $password );
		$require_change = ! empty( $account_data['require_password_change'] ) ? 1 : 0;

		$inserted = $wpdb->insert(
			$users_table,
			array(
				'username'                => $username,
				'email'                   => $email,
				'password_hash'           => $password_hash,
				'role_id'                 => (int) $role_id,
				'status'                  => 'active',
				'first_name'              => $first_name,
				'last_name'               => $last_name,
				'display_name'            => $full_name,
				'require_password_change' => $require_change,
			),
			array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d' )
		);

		if ( false === $inserted ) {
			return new WP_Error( 'db_error', __( 'Failed to create NDS HR user record.', 'nds-hr' ) );
		}

		$hr_user_id = $wpdb->insert_id;

		return array(
			'user_id'                 => $hr_user_id,
			'hr_user_id'              => $hr_user_id,
			'username'                => $username,
			'email'                   => $email,
			'temporary_password'      => $password,
			'require_password_change' => (bool) $require_change,
		);
	}

	/**
	 * Link an NDS HR User to an Employee record in wp_nds_hr_employees.
	 *
	 * @param int $hr_user_id
	 * @param int $employee_db_id
	 */
	public static function link_user_to_employee( $hr_user_id, $employee_db_id ) {
		global $wpdb;
		$employees_table = NDS_HR_Database::employees_table();
		$wpdb->update(
			$employees_table,
			array( 'hr_user_id' => absint( $hr_user_id ) ),
			array( 'id' => absint( $employee_db_id ) ),
			array( '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Get Employee DB record ID linked to an HR User ID (with legacy user_id fallback).
	 *
	 * @param int $hr_user_id
	 * @return int|false
	 */
	public static function get_employee_id_by_user( $hr_user_id ) {
		global $wpdb;
		$table = NDS_HR_Database::employees_table();

		// Primary check: hr_user_id
		$emp_db_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE hr_user_id = %d LIMIT 1", $hr_user_id ) );
		if ( $emp_db_id ) {
			return absint( $emp_db_id );
		}

		// Non-destructive fallback: check legacy user_id column
		$legacy_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE user_id = %d LIMIT 1", $hr_user_id ) );
		if ( $legacy_id ) {
			return absint( $legacy_id );
		}

		return false;
	}
}
