<?php
/**
 * Plugin Name:       NDS HR
 * Plugin URI:        https://nds-hr.local
 * Description:       A modern, production-ready HR Management System for WordPress. Includes employee management, custom database tables, role-based access control, audit logs, and employee self-service portal.
 * Version:           2.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            NDS HR Team
 * Author URI:        https://nds-hr.local
 * Text Domain:       nds-hr
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package NDS_HR
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version, database version, and routing version.
 */
define( 'NDS_HR_VERSION', '2.0.0' );
define( 'NDS_HR_DB_VERSION', '2.3.0' );
define( 'NDS_HR_ROUTING_VERSION', '2.2.0' );

/**
 * Path and URL constants.
 */
define( 'NDS_HR_FILE', __FILE__ );
define( 'NDS_HR_PATH', plugin_dir_path( __FILE__ ) );
define( 'NDS_HR_URL', plugin_dir_url( __FILE__ ) );
define( 'NDS_HR_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Require the core plugin bootstrap class.
 */
require_once NDS_HR_PATH . 'includes/class-plugin.php';

/**
 * The code that runs during plugin activation.
 */
function nds_hr_activate() {
	require_once NDS_HR_PATH . 'includes/class-database.php';
	require_once NDS_HR_PATH . 'includes/class-roles.php';

	// Run initial database tables creation via dbDelta.
	NDS_HR_Database::install();

	// Register custom roles and capabilities.
	NDS_HR_Roles::register_roles();

	// Flush rewrite rules for employee portal and independent canonical HR routes.
	add_rewrite_rule( '^hr/setup/?$', 'index.php?nds_hr_route=setup', 'top' );
	add_rewrite_rule( '^hr/login/?$', 'index.php?nds_hr_route=login', 'top' );
	add_rewrite_rule( '^hr/logout/?$', 'index.php?nds_hr_route=logout', 'top' );
	add_rewrite_rule( '^hr/reset-password/?$', 'index.php?nds_hr_route=reset_password', 'top' );
	add_rewrite_rule( '^hr/([a-z0-9_-]+)/?$', 'index.php?nds_hr_route=admin&nds_hr_tab=$matches[1]', 'top' );
	add_rewrite_rule( '^hr/?$', 'index.php?nds_hr_route=admin', 'top' );
	add_rewrite_rule( '^login/?$', 'index.php?nds_hr_route=login', 'top' );
	add_rewrite_rule( '^employee/?$', 'index.php?nds_hr_portal=1', 'top' );
	flush_rewrite_rules();

	// Store activation state and version.
	update_option( 'nds_hr_version', NDS_HR_VERSION );
	update_option( 'nds_hr_rewrite_version', NDS_HR_ROUTING_VERSION );
	update_option( 'nds_hr_installed_at', current_time( 'mysql' ) );
}
register_activation_hook( __FILE__, 'nds_hr_activate' );

/**
 * The code that runs during plugin deactivation.
 * Note: Data is preserved on deactivation according to architectural requirements.
 */
function nds_hr_deactivate() {
	// Flush rewrite rules on deactivation.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'nds_hr_deactivate' );

/**
 * Begins execution of the plugin.
 *
 * @return NDS_HR_Plugin
 */
function nds_hr() {
	return NDS_HR_Plugin::get_instance();
}

// Initialize the plugin.
add_action( 'plugins_loaded', 'nds_hr' );
