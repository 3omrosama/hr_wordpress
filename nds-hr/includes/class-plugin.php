<?php
/**
 * Main NDS HR Plugin class.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Plugin
 */
class NDS_HR_Plugin {

	/**
	 * Single instance of the plugin.
	 *
	 * @var NDS_HR_Plugin|null
	 */
	protected static $instance = null;

	/**
	 * Database instance.
	 *
	 * @var NDS_HR_Database
	 */
	public $database;

	/**
	 * Roles instance.
	 *
	 * @var NDS_HR_Roles
	 */
	public $roles;

	/**
	 * Permissions helper.
	 *
	 * @var NDS_HR_Permissions
	 */
	public $permissions;

	/**
	 * Security helper.
	 *
	 * @var NDS_HR_Security
	 */
	public $security;

	/**
	 * Auth helper.
	 *
	 * @var NDS_HR_Auth
	 */
	public $auth;

	/**
	 * Internationalization helper.
	 *
	 * @var NDS_HR_I18n
	 */
	public $i18n;

	/**
	 * Audit Logger.
	 *
	 * @var NDS_HR_Audit_Logger
	 */
	public $audit;

	/**
	 * Employee module.
	 *
	 * @var NDS_HR_Employees
	 */
	public $employees;

	/**
	 * Custom Fields core engine.
	 *
	 * @var NDS_HR_Custom_Fields
	 */
	public $custom_fields;

	/**
	 * Admin controller.
	 *
	 * @var NDS_HR_Admin|null
	 */
	public $admin = null;

	/**
	 * Employee portal controller.
	 *
	 * @var NDS_HR_Employee_Portal|null
	 */
	public $portal = null;

	/**
	 * Main instance accessor.
	 *
	 * @return NDS_HR_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->load_dependencies();
		$this->init_components();
		$this->define_hooks();
	}

	/**
	 * Load core class dependencies.
	 */
	protected function load_dependencies() {
		// Core Infrastructure
		require_once NDS_HR_PATH . 'includes/class-database.php';
		require_once NDS_HR_PATH . 'includes/class-settings.php';
		require_once NDS_HR_PATH . 'includes/class-custom-fields.php';
		require_once NDS_HR_PATH . 'includes/class-session.php';
		require_once NDS_HR_PATH . 'includes/class-roles.php';
		require_once NDS_HR_PATH . 'includes/class-permissions.php';
		require_once NDS_HR_PATH . 'includes/class-security.php';
		require_once NDS_HR_PATH . 'includes/class-router.php';
		require_once NDS_HR_PATH . 'includes/class-auth.php';
		require_once NDS_HR_PATH . 'includes/class-i18n.php';
		require_once NDS_HR_PATH . 'includes/class-audit-logger.php';

		// Employees Module
		require_once NDS_HR_PATH . 'modules/employees/class-employee-repository.php';
		require_once NDS_HR_PATH . 'modules/employees/class-employee-service.php';
		require_once NDS_HR_PATH . 'modules/employees/class-employees.php';

		// Admin & Dashboard
		require_once NDS_HR_PATH . 'admin/dashboard/class-admin-dashboard.php';
		require_once NDS_HR_PATH . 'admin/employees/class-admin-employees.php';
		require_once NDS_HR_PATH . 'admin/settings/class-admin-roles.php';
		require_once NDS_HR_PATH . 'admin/settings/class-admin-settings.php';
		require_once NDS_HR_PATH . 'admin/class-admin.php';

		// Frontend Portal
		require_once NDS_HR_PATH . 'employee/dashboard/class-employee-dashboard.php';
		require_once NDS_HR_PATH . 'employee/profile/class-employee-profile.php';
		require_once NDS_HR_PATH . 'employee/class-employee-portal.php';
	}

	/**
	 * Initialize core components.
	 */
	protected function init_components() {
		$this->database      = new NDS_HR_Database();
		$this->roles         = new NDS_HR_Roles();
		$this->permissions   = new NDS_HR_Permissions();
		$this->security      = new NDS_HR_Security();
		$this->auth          = new NDS_HR_Auth();
		$this->i18n          = new NDS_HR_I18n();
		$this->audit         = new NDS_HR_Audit_Logger();
		$this->custom_fields = new NDS_HR_Custom_Fields( $this );
		$this->employees     = new NDS_HR_Employees( $this );
		$this->admin         = new NDS_HR_Admin( $this );
		$this->portal        = new NDS_HR_Employee_Portal( $this );
	}

	/**
	 * Register global WordPress hooks.
	 */
	protected function define_hooks() {
		add_action( 'init', array( $this->i18n, 'load_plugin_textdomain' ) );
		add_action( 'init', array( $this->portal, 'register_rewrite_endpoints' ) );
		add_filter( 'query_vars', array( $this->portal, 'register_query_vars' ) );
		add_action( 'template_redirect', array( $this->portal, 'render_portal_template' ) );
		add_shortcode( 'nds_hr_portal', array( $this->portal, 'render_portal_shortcode' ) );

		// Check for database updates on admin init
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this->database, 'check_updates' ) );
		}
	}
}
