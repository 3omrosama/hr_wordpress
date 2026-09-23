<?php
/**
 * Main Admin Interface Orchestrator.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Admin
 */
class NDS_HR_Admin {

	/**
	 * Plugin instance.
	 *
	 * @var NDS_HR_Plugin
	 */
	protected $plugin;

	/**
	 * Admin Dashboard controller.
	 *
	 * @var NDS_HR_Admin_Dashboard
	 */
	public $dashboard;

	/**
	 * Admin Employees controller.
	 *
	 * @var NDS_HR_Admin_Employees
	 */
	public $employees;

	/**
	 * Admin Roles & Permissions controller.
	 *
	 * @var NDS_HR_Admin_Roles
	 */
	public $roles_controller;

	/**
	 * Constructor.
	 *
	 * @param NDS_HR_Plugin $plugin
	 */
	public function __construct( NDS_HR_Plugin $plugin ) {
		$this->plugin           = $plugin;
		$this->dashboard        = new NDS_HR_Admin_Dashboard( $plugin );
		$this->employees        = new NDS_HR_Admin_Employees( $plugin );
		$this->roles_controller = new NDS_HR_Admin_Roles( $plugin );

		add_action( 'admin_menu', array( $this, 'register_admin_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_init', array( $this->employees, 'handle_form_submissions' ) );
		add_action( 'admin_init', array( $this->roles_controller, 'handle_form_submissions' ) );
	}

	/**
	 * Register NDS HR admin menu and submenus.
	 */
	public function register_admin_menus() {
		$main_cap = 'nds_hr_access_admin';

		// Main top-level Menu
		add_menu_page(
			__( 'NDS HR System', 'nds-hr' ),
			__( 'NDS HR', 'nds-hr' ),
			$main_cap,
			'nds-hr',
			array( $this->dashboard, 'render_dashboard_page' ),
			'dashicons-id-alt',
			30
		);

		// Submenu: Dashboard
		add_submenu_page(
			'nds-hr',
			__( 'NDS HR Dashboard', 'nds-hr' ),
			__( 'Dashboard', 'nds-hr' ),
			$main_cap,
			'nds-hr',
			array( $this->dashboard, 'render_dashboard_page' )
		);

		// Submenu: Employees
		add_submenu_page(
			'nds-hr',
			__( 'Employee Management', 'nds-hr' ),
			__( 'Employees', 'nds-hr' ),
			'nds_hr_view_employees',
			'nds-hr-employees',
			array( $this->employees, 'render_employees_page' )
		);

		// Submenu: Roles & Permissions
		add_submenu_page(
			'nds-hr',
			__( 'Roles & Permissions', 'nds-hr' ),
			__( 'Roles & Permissions', 'nds-hr' ),
			'nds_hr_manage_roles',
			'nds-hr-roles',
			array( $this->roles_controller, 'render_roles_page' )
		);

		// Submenu: Audit Logs
		add_submenu_page(
			'nds-hr',
			__( 'HR Audit Logs', 'nds-hr' ),
			__( 'Audit Logs', 'nds-hr' ),
			'nds_hr_view_audit_logs',
			'nds-hr-audit-logs',
			array( $this, 'render_audit_logs_page' )
		);
	}

	/**
	 * Enqueue admin styles and scripts only on NDS HR pages.
	 *
	 * @param string $hook_suffix
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( strpos( $hook_suffix, 'nds-hr' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'nds-hr-admin-css',
			NDS_HR_URL . 'assets/css/admin.css',
			array(),
			NDS_HR_VERSION
		);

		// RTL support
		NDS_HR_I18n::maybe_enqueue_rtl();

		wp_enqueue_script(
			'nds-hr-admin-js',
			NDS_HR_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			NDS_HR_VERSION,
			true
		);

		wp_localize_script(
			'nds-hr-admin-js',
			'ndsHrAdminData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( NDS_HR_Security::NONCE_ACTION ),
				'isRtl'   => NDS_HR_I18n::is_rtl(),
				'i18n'    => array(
					'confirmDeactivate' => __( 'Are you sure you want to deactivate this employee?', 'nds-hr' ),
					'confirmReactivate' => __( 'Are you sure you want to reactivate this employee?', 'nds-hr' ),
					'confirmDelete'     => __( 'Are you sure you want to permanently delete this employee record? This cannot be undone.', 'nds-hr' ),
					'saving'            => __( 'Saving...', 'nds-hr' ),
				),
			)
		);
	}

	/**
	 * Render the Audit Logs admin view.
	 */
	public function render_audit_logs_page() {
		if ( ! NDS_HR_Permissions::can_view_audit_logs() ) {
			wp_die( esc_html__( 'You do not have permission to view audit logs.', 'nds-hr' ) );
		}

		$page     = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$per_page = 20;

		$action_filter = isset( $_GET['log_action'] ) ? sanitize_text_field( wp_unslash( $_GET['log_action'] ) ) : '';

		$logs = NDS_HR_Audit_Logger::get_logs(
			array(
				'page'     => $page,
				'per_page' => $per_page,
				'action'   => $action_filter,
			)
		);

		$total_logs  = NDS_HR_Audit_Logger::count_logs( array( 'action' => $action_filter ) );
		$total_pages = ceil( $total_logs / $per_page );

		include NDS_HR_PATH . 'templates/admin/audit-logs.php';
	}
}
