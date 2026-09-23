<?php
/**
 * Admin Employee Management Controller.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Admin_Employees
 */
class NDS_HR_Admin_Employees {

	/**
	 * Plugin instance.
	 *
	 * @var NDS_HR_Plugin
	 */
	protected $plugin;

	/**
	 * Transient key prefix for temporary credential display.
	 */
	const TRANSIENT_CREDS_PREFIX = 'nds_hr_temp_creds_';

	/**
	 * Constructor.
	 *
	 * @param NDS_HR_Plugin $plugin
	 */
	public function __construct( NDS_HR_Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Process form actions on admin_init.
	 */
	public function handle_form_submissions() {
		// Only handle our actions
		if ( ! isset( $_POST['nds_hr_admin_action'] ) && ! isset( $_GET['nds_hr_action'] ) ) {
			return;
		}

		$repo    = $this->plugin->employees->repository;
		$service = $this->plugin->employees->service;

		// 1. Save / Update Employee Form (POST)
		if ( isset( $_POST['nds_hr_admin_action'] ) && 'save_employee' === $_POST['nds_hr_admin_action'] ) {
			NDS_HR_Security::check_admin_referer_or_die( 'nds_hr_save_employee' );

			if ( ! NDS_HR_Permissions::can_manage_employees() ) {
				wp_die( esc_html__( 'Unauthorized to manage employees.', 'nds-hr' ) );
			}

			$employee_db_id = isset( $_POST['employee_db_id'] ) ? absint( $_POST['employee_db_id'] ) : 0;
			$clean_data     = NDS_HR_Security::sanitize_employee_input( $_POST );

			// Check validation errors on phone
			if ( ! empty( $clean_data['_phone_error'] ) && is_wp_error( $clean_data['_phone_error'] ) ) {
				$err_msg = $clean_data['_phone_error']->get_error_message();
				$redirect_url = $employee_db_id > 0
					? add_query_arg( array( 'page' => 'nds-hr-employees', 'action' => 'edit', 'id' => $employee_db_id, 'error' => urlencode( $err_msg ) ), admin_url( 'admin.php' ) )
					: add_query_arg( array( 'page' => 'nds-hr-employees', 'action' => 'add', 'error' => urlencode( $err_msg ) ), admin_url( 'admin.php' ) );
				wp_safe_redirect( $redirect_url );
				exit;
			}

			$existing_emp = $employee_db_id > 0 ? $repo->find_by_id( $employee_db_id ) : null;

			// Handle Profile Photo Upload
			if ( ! empty( $clean_data['remove_profile_photo'] ) ) {
				$clean_data['profile_photo_url'] = '';
			} elseif ( isset( $_FILES['profile_photo'] ) && ! empty( $_FILES['profile_photo']['name'] ) ) {
				$photo_upload = NDS_HR_Security::handle_profile_photo_upload(
					$_FILES['profile_photo'],
					$existing_emp ? (string) $existing_emp->profile_photo_url : ''
				);

				if ( is_wp_error( $photo_upload ) ) {
					$redirect_url = $employee_db_id > 0
						? add_query_arg( array( 'page' => 'nds-hr-employees', 'action' => 'edit', 'id' => $employee_db_id, 'error' => urlencode( $photo_upload->get_error_message() ) ), admin_url( 'admin.php' ) )
						: add_query_arg( array( 'page' => 'nds-hr-employees', 'action' => 'add', 'error' => urlencode( $photo_upload->get_error_message() ) ), admin_url( 'admin.php' ) );
					wp_safe_redirect( $redirect_url );
					exit;
				}

				$clean_data['profile_photo_url'] = $photo_upload;
			} elseif ( $existing_emp && empty( $clean_data['profile_photo_url'] ) ) {
				$clean_data['profile_photo_url'] = $existing_emp->profile_photo_url;
			}

			// Handle Contract Document Upload
			if ( ! empty( $clean_data['remove_contract_document'] ) ) {
				$clean_data['contract_document_url']  = '';
				$clean_data['contract_document_name'] = '';
			} elseif ( isset( $_FILES['contract_document'] ) && ! empty( $_FILES['contract_document']['name'] ) ) {
				$doc_upload = NDS_HR_Security::handle_contract_document_upload(
					$_FILES['contract_document'],
					$existing_emp && isset( $existing_emp->contract_document_url ) ? (string) $existing_emp->contract_document_url : '',
					$existing_emp && isset( $existing_emp->contract_document_name ) ? (string) $existing_emp->contract_document_name : ''
				);

				if ( is_wp_error( $doc_upload ) ) {
					$redirect_url = $employee_db_id > 0
						? add_query_arg( array( 'page' => 'nds-hr-employees', 'action' => 'edit', 'id' => $employee_db_id, 'error' => urlencode( $doc_upload->get_error_message() ) ), admin_url( 'admin.php' ) )
						: add_query_arg( array( 'page' => 'nds-hr-employees', 'action' => 'add', 'error' => urlencode( $doc_upload->get_error_message() ) ), admin_url( 'admin.php' ) );
					wp_safe_redirect( $redirect_url );
					exit;
				}

				$clean_data['contract_document_url']  = $doc_upload['url'];
				$clean_data['contract_document_name'] = $doc_upload['name'];
			} elseif ( $existing_emp && isset( $existing_emp->contract_document_url ) ) {
				$clean_data['contract_document_url']  = $existing_emp->contract_document_url;
				$clean_data['contract_document_name'] = isset( $existing_emp->contract_document_name ) ? $existing_emp->contract_document_name : '';
			}

			// Handle independent NDS HR account options
			$account_options = array(
				'create_account'          => ! empty( $_POST['create_hr_account'] ) || ( isset( $_POST['account_action'] ) && 'create' === $_POST['account_action'] ),
				'role'                    => ! empty( $_POST['hr_account_role'] ) ? sanitize_key( $_POST['hr_account_role'] ) : ( ! empty( $_POST['account_role'] ) ? sanitize_key( $_POST['account_role'] ) : 'hr_employee' ),
				'username'                => isset( $_POST['account_username'] ) ? sanitize_user( wp_unslash( $_POST['account_username'] ) ) : '',
				'password'                => isset( $_POST['account_password'] ) ? wp_unslash( $_POST['account_password'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'confirm_password'        => isset( $_POST['account_password_confirm'] ) ? wp_unslash( $_POST['account_password_confirm'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'require_password_change' => ! empty( $_POST['require_password_change'] ),
				'send_notification'       => ! empty( $_POST['send_account_notification'] ),
			);

			if ( $employee_db_id > 0 ) {
				// Update existing
				$result = $service->update_employee( $employee_db_id, $clean_data, $account_options );
				if ( is_wp_error( $result ) ) {
					wp_safe_redirect(
						add_query_arg(
							array(
								'page'   => 'nds-hr-employees',
								'action' => 'edit',
								'id'     => $employee_db_id,
								'error'  => urlencode( $result->get_error_message() ),
							),
							admin_url( 'admin.php' )
						)
					);
					exit;
				}

				wp_safe_redirect(
					add_query_arg(
						array(
							'page'    => 'nds-hr-employees',
							'message' => 'updated',
						),
						admin_url( 'admin.php' )
					)
				);
				exit;
			} else {
				// Create new employee
				$new_result = $service->create_employee( $clean_data, $account_options );
				if ( is_wp_error( $new_result ) ) {
					wp_safe_redirect(
						add_query_arg(
							array(
								'page'   => 'nds-hr-employees',
								'action' => 'add',
								'error'  => urlencode( $new_result->get_error_message() ),
							),
							admin_url( 'admin.php' )
						)
					);
					exit;
				}

				$redirect_args = array(
					'page'    => 'nds-hr-employees',
					'message' => 'created',
				);

				// If account was created with temporary credentials, store a short-lived transient (60 seconds) for the current admin only
				if ( is_array( $new_result ) && ! empty( $new_result['temporary_password'] ) ) {
					$admin_actor_id = NDS_HR_Session::get_current_user_id() ?: get_current_user_id();
					$transient_key  = 'nds_hr_temp_' . $admin_actor_id . '_' . $new_result['employee_db_id'];
					set_transient(
						$transient_key,
						array(
							'employee_code'           => $new_result['employee_code'],
							'username'                => $new_result['username'],
							'temporary_password'      => $new_result['temporary_password'],
							'require_password_change' => ! empty( $new_result['require_password_change'] ),
						),
						60 // 60 seconds lifetime; displayed once then discarded
					);
					$redirect_args['creds_token'] = $new_result['employee_db_id'];
				}

				wp_safe_redirect(
					add_query_arg(
						$redirect_args,
						admin_url( 'admin.php' )
					)
				);
				exit;
			}
		}

		// 2. Status toggle (Deactivate / Reactivate) (GET)
		if ( isset( $_GET['nds_hr_action'] ) && in_array( $_GET['nds_hr_action'], array( 'deactivate', 'reactivate' ), true ) ) {
			$action = sanitize_key( $_GET['nds_hr_action'] );
			$id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

			NDS_HR_Security::check_admin_referer_or_die( 'nds_hr_status_' . $id );

			if ( ! NDS_HR_Permissions::can_manage_employees() ) {
				wp_die( esc_html__( 'Unauthorized action.', 'nds-hr' ) );
			}

			$target_status = 'deactivate' === $action ? 'inactive' : 'active';
			$service->change_status( $id, $target_status );

			wp_safe_redirect(
				add_query_arg(
					array(
						'page'    => 'nds-hr-employees',
						'message' => 'deactivate' === $action ? 'deactivated' : 'reactivated',
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}

		// 3. Delete Employee (GET)
		if ( isset( $_GET['nds_hr_action'] ) && 'delete' === $_GET['nds_hr_action'] ) {
			$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

			NDS_HR_Security::check_admin_referer_or_die( 'nds_hr_delete_' . $id );

			if ( ! NDS_HR_Permissions::can_delete_employees() ) {
				wp_die( esc_html__( 'Unauthorized to delete employees.', 'nds-hr' ) );
			}

			$service->delete_employee( $id );

			wp_safe_redirect(
				add_query_arg(
					array(
						'page'    => 'nds-hr-employees',
						'message' => 'deleted',
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}
	}

	/**
	 * Main Employee Management Page Router.
	 */
	public function render_employees_page() {
		if ( ! NDS_HR_Permissions::can_manage_employees() && ! NDS_HR_Permissions::can_view_all_employees() ) {
			wp_die( esc_html__( 'Access denied. You do not have permission to view employees.', 'nds-hr' ) );
		}

		$repo    = $this->plugin->employees->repository;
		$service = $this->plugin->employees->service;
		$action  = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';

		// Sub-page: Add Employee Form
		if ( 'add' === $action ) {
			if ( ! NDS_HR_Permissions::can_manage_employees() ) {
				wp_die( esc_html__( 'Unauthorized to add new employees.', 'nds-hr' ) );
			}

			$departments = $repo->get_departments();
			$positions   = $repo->get_positions();

			// Generate next suggested employee code via service layer
			$next_code = $service->generate_employee_id();

			include NDS_HR_PATH . 'templates/admin/employee-form.php';
			return;
		}

		// Sub-page: Edit Employee Form
		if ( 'edit' === $action ) {
			if ( ! NDS_HR_Permissions::can_manage_employees() ) {
				wp_die( esc_html__( 'Unauthorized to edit employees.', 'nds-hr' ) );
			}

			$id       = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
			$employee = $repo->find_by_id( $id );

			if ( ! $employee ) {
				wp_die( esc_html__( 'Employee not found.', 'nds-hr' ) );
			}

			$departments = $repo->get_departments();
			$positions   = $repo->get_positions();

			// Fetch linked independent NDS HR user record
			global $wpdb;
			$users_table = NDS_HR_Database::users_table();
			$roles_table = NDS_HR_Database::roles_table();
			$target_user_id = ! empty( $employee->hr_user_id ) ? (int) $employee->hr_user_id : ( ! empty( $employee->user_id ) ? (int) $employee->user_id : 0 );
			
			$hr_user = null;
			if ( $target_user_id > 0 ) {
				$hr_user = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT u.*, r.slug AS role_slug, r.name AS role_name 
						 FROM {$users_table} u 
						 LEFT JOIN {$roles_table} r ON u.role_id = r.id 
						 WHERE u.id = %d LIMIT 1",
						$target_user_id
					)
				);
			}

			include NDS_HR_PATH . 'templates/admin/employee-form.php';
			return;
		}

		// Sub-page: View Employee Details
		if ( 'view' === $action ) {
			$id       = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
			$employee = $repo->find_by_id( $id );

			if ( ! $employee ) {
				wp_die( esc_html__( 'Employee not found.', 'nds-hr' ) );
			}

			// Fetch linked independent NDS HR user record
			global $wpdb;
			$users_table = NDS_HR_Database::users_table();
			$roles_table = NDS_HR_Database::roles_table();
			$target_user_id = ! empty( $employee->hr_user_id ) ? (int) $employee->hr_user_id : ( ! empty( $employee->user_id ) ? (int) $employee->user_id : 0 );
			
			$hr_user = null;
			if ( $target_user_id > 0 ) {
				$hr_user = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT u.*, r.slug AS role_slug, r.name AS role_name 
						 FROM {$users_table} u 
						 LEFT JOIN {$roles_table} r ON u.role_id = r.id 
						 WHERE u.id = %d LIMIT 1",
						$target_user_id
					)
				);
			}

			$employee_logs = NDS_HR_Audit_Logger::get_logs( array( 'entity_type' => 'employee', 'entity_id' => $id, 'per_page' => 15 ) );

			include NDS_HR_PATH . 'templates/admin/employee-view.php';
			return;
		}

		// Default: Employees List with search, filter, and pagination
		$search        = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$department_id = isset( $_GET['department_id'] ) ? absint( $_GET['department_id'] ) : 0;
		$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		$page          = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$per_page      = 10;

		$query_args = array(
			'search'            => $search,
			'department_id'     => $department_id,
			'employment_status' => $status_filter,
			'page'              => $page,
			'per_page'          => $per_page,
		);

		$employees   = $repo->get_all( $query_args );
		$total_count = $repo->count( $query_args );
		$total_pages = ceil( $total_count / $per_page );
		$departments = $repo->get_departments();

		// Check if credentials modal/badge should be shown from transient
		$created_credentials = null;
		if ( isset( $_GET['creds_token'] ) ) {
			$token_id       = absint( $_GET['creds_token'] );
			$admin_actor_id = NDS_HR_Session::get_current_user_id() ?: get_current_user_id();
			$transient_key  = 'nds_hr_temp_' . $admin_actor_id . '_' . $token_id;
			$temp_data      = get_transient( $transient_key );
			if ( $temp_data ) {
				$created_credentials = $temp_data;
				delete_transient( $transient_key ); // Destroy immediately after fetching
			}
		}

		include NDS_HR_PATH . 'templates/admin/employees-list.php';
	}
}
