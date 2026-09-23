<?php
/**
 * Admin Dashboard Controller.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Admin_Dashboard
 */
class NDS_HR_Admin_Dashboard {

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
	 * Render the main Admin Dashboard view.
	 */
	public function render_dashboard_page() {
		if ( ! NDS_HR_Permissions::can_view_employees() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access the HR Dashboard.', 'nds-hr' ) );
		}

		$repo = $this->plugin->employees->repository;

		// Metric Cards for Phase 1
		$counts = $repo->get_status_counts();

		// Recent employee onboardings
		$recent_employees = $repo->get_all(
			array(
				'page'     => 1,
				'per_page' => 5,
				'orderby'  => 'e.id',
				'order'    => 'DESC',
			)
		);

		// Recent audit log activity
		$recent_logs = NDS_HR_Audit_Logger::get_logs(
			array(
				'page'     => 1,
				'per_page' => 6,
			)
		);

		// Future modules readiness blueprint metadata
		$future_modules = NDS_HR_Database::get_future_modules_schema_registry();

		include NDS_HR_PATH . 'templates/admin/dashboard.php';
	}
}
