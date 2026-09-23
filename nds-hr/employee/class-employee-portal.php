<?php
/**
 * Frontend Employee Portal Orchestrator.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Employee_Portal
 */
class NDS_HR_Employee_Portal {

	/**
	 * Plugin instance.
	 *
	 * @var NDS_HR_Plugin
	 */
	protected $plugin;

	/**
	 * Employee Dashboard Controller.
	 *
	 * @var NDS_HR_Employee_Dashboard
	 */
	public $dashboard;

	/**
	 * Employee Profile Controller.
	 *
	 * @var NDS_HR_Employee_Profile
	 */
	public $profile;

	/**
	 * Constructor.
	 *
	 * @param NDS_HR_Plugin $plugin
	 */
	public function __construct( NDS_HR_Plugin $plugin ) {
		$this->plugin    = $plugin;
		$this->dashboard = new NDS_HR_Employee_Dashboard( $plugin );
		$this->profile   = new NDS_HR_Employee_Profile( $plugin );
	}

	/**
	 * Register frontend rewrite rules for /employee/ route.
	 */
	public function register_rewrite_endpoints() {
		add_rewrite_rule( '^employee/?$', 'index.php?nds_hr_portal=1', 'top' );
		add_rewrite_rule( '^employee/profile/?$', 'index.php?nds_hr_portal=1&nds_hr_tab=profile', 'top' );
		add_rewrite_rule( '^employee/([a-z0-9_-]+)/?$', 'index.php?nds_hr_portal=1&nds_hr_tab=$matches[1]', 'top' );
	}

	/**
	 * Register public query variables.
	 *
	 * @param array $vars
	 * @return array
	 */
	public function register_query_vars( $vars ) {
		$vars[] = 'nds_hr_portal';
		$vars[] = 'nds_hr_tab';
		return $vars;
	}

	/**
	 * Intercept template rendering when nds_hr_portal query var is present.
	 */
	public function render_portal_template() {
		if ( 1 !== (int) get_query_var( 'nds_hr_portal' ) ) {
			return;
		}

		// Clear any WordPress 404 status and set HTTP 200 OK
		global $wp_query;
		if ( is_object( $wp_query ) ) {
			$wp_query->is_404 = false;
		}
		status_header( 200 );

		$this->enqueue_portal_assets();
		$this->render_portal_view();
		exit;
	}

	/**
	 * Render portal via WordPress shortcode [nds_hr_portal].
	 *
	 * @return string
	 */
	public function render_portal_shortcode() {
		$this->enqueue_portal_assets();
		ob_start();
		$this->render_portal_view();
		return ob_get_clean();
	}

	/**
	 * Enqueue assets for the Employee Portal.
	 */
	public function enqueue_portal_assets() {
		wp_enqueue_style(
			'nds-hr-portal-css',
			NDS_HR_URL . 'assets/css/employee-portal.css',
			array(),
			NDS_HR_VERSION
		);

		NDS_HR_I18n::maybe_enqueue_rtl();

		wp_enqueue_script(
			'nds-hr-portal-js',
			NDS_HR_URL . 'assets/js/employee-portal.js',
			array( 'jquery' ),
			NDS_HR_VERSION,
			true
		);

		wp_localize_script(
			'nds-hr-portal-js',
			'ndsHrPortalData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( NDS_HR_Security::NONCE_ACTION ),
				'isRtl'   => NDS_HR_I18n::is_rtl(),
			)
		);
	}

	/**
	 * Main portal view renderer with authentication and ownership validation.
	 */
	public function render_portal_view() {
		$hr_user         = NDS_HR_Session::get_authenticated_user();
		$is_hr_logged_in = ( null !== $hr_user );

		// 1. Guard check: visitor must be logged into NDS HR
		if ( ! $is_hr_logged_in ) {
			include NDS_HR_PATH . 'templates/employee/login-required.php';
			return;
		}

		// If HR user session requires first password change, redirect to login change screen
		if ( '1' === (string) $hr_user->require_password_change ) {
			wp_safe_redirect( NDS_HR_Router::url( 'login', array( 'require_pw_change' => 1 ) ) );
			exit;
		}

		$repo = $this->plugin->employees->repository;

		// 2. Fetch employee record linked to current NDS HR session
		$employee = $repo->find_by_hr_user_id( (int) $hr_user->id );

		// If administrative user is browsing without an employee record, allow viewing preview
		$can_admin = NDS_HR_Permissions::can_access_admin( (int) $hr_user->id );

		if ( ! $employee && $can_admin ) {
			$all_employees = $repo->get_all( array( 'per_page' => 10 ) );
			include NDS_HR_PATH . 'templates/employee/admin-portal-preview.php';
			return;
		}

		// Non-admin user with no linked employee profile
		if ( ! $employee ) {
			include NDS_HR_PATH . 'templates/employee/unlinked-account.php';
			return;
		}

		// Tab routing
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ( get_query_var( 'nds_hr_tab' ) ? sanitize_key( get_query_var( 'nds_hr_tab' ) ) : 'dashboard' );

		include NDS_HR_PATH . 'templates/employee/portal-layout.php';
	}
}
