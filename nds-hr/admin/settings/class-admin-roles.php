<?php
/**
 * Admin Roles and Permissions Controller.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Admin_Roles
 */
class NDS_HR_Admin_Roles {

	/**
	 * Plugin instance.
	 *
	 * @var NDS_HR_Plugin
	 */
	protected $plugin;

	/**
	 * Constructor.
	 *
	 * @param NDS_HR_Plugin $plugin
	 */
	public function __construct( NDS_HR_Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Process form actions for Roles & Permissions on admin_init.
	 */
	public function handle_form_submissions() {
		if ( ! isset( $_POST['nds_hr_roles_action'] ) ) {
			return;
		}

		NDS_HR_Security::check_admin_referer_or_die( 'nds_hr_save_roles_permissions' );

		if ( ! NDS_HR_Permissions::can_manage_roles() ) {
			wp_die( esc_html__( 'Unauthorized. You do not have permission to manage HR roles and permissions.', 'nds-hr' ) );
		}

		$action = sanitize_key( $_POST['nds_hr_roles_action'] );

		// Reset to defaults
		if ( 'reset_roles' === $action ) {
			NDS_HR_Roles::reset_role_matrix_to_defaults();

			wp_safe_redirect(
				add_query_arg(
					array(
						'page'    => 'nds-hr-roles',
						'message' => 'reset_success',
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}

		// Save custom matrix
		if ( 'save_roles' === $action ) {
			$raw_matrix = isset( $_POST['matrix'] ) && is_array( $_POST['matrix'] ) ? $_POST['matrix'] : array();

			NDS_HR_Roles::save_role_matrix( $raw_matrix );

			wp_safe_redirect(
				add_query_arg(
					array(
						'page'    => 'nds-hr-roles',
						'message' => 'saved_success',
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}
	}

	/**
	 * Render the Roles & Permissions Management Screen.
	 */
	public function render_roles_page() {
		if ( ! NDS_HR_Permissions::can_manage_roles() ) {
			wp_die( esc_html__( 'You do not have permission to manage HR roles and permissions.', 'nds-hr' ) );
		}

		$message = isset( $_GET['message'] ) ? sanitize_key( $_GET['message'] ) : '';

		$groups           = NDS_HR_Roles::get_capabilities_by_group();
		$manageable_roles = NDS_HR_Roles::get_manageable_roles();
		$matrix           = NDS_HR_Roles::get_role_matrix();

		// Count NDS HR users in each role
		$hr_role_counts = NDS_HR_Roles::get_hr_user_counts_by_role();
		$role_counts    = array();
		foreach ( array_keys( $manageable_roles ) as $role_slug ) {
			if ( 'administrator' === $role_slug ) {
				$counts = count_users();
				$role_counts[ $role_slug ] = isset( $counts['avail_roles']['administrator'] ) ? (int) $counts['avail_roles']['administrator'] : 0;
			} else {
				$role_counts[ $role_slug ] = isset( $hr_role_counts[ $role_slug ] ) ? (int) $hr_role_counts[ $role_slug ] : 0;
			}
		}

		include NDS_HR_PATH . 'templates/admin/roles-permissions.php';
	}
}
