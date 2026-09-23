<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package NDS_HR
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Architectural Requirement:
 * Default behavior MUST preserve all HR data on uninstall.
 * Only drop tables and settings if an explicit administrator setting has been deliberately enabled.
 */
$delete_data = get_option( 'nds_hr_delete_data_on_uninstall', false );

if ( ! empty( $delete_data ) && ( 'yes' === $delete_data || true === $delete_data ) ) {
	global $wpdb;

	// Drop custom tables in reverse order of foreign relationships
	$tables = array(
		$wpdb->prefix . 'nds_hr_audit_logs',
		$wpdb->prefix . 'nds_hr_employees',
		$wpdb->prefix . 'nds_hr_positions',
		$wpdb->prefix . 'nds_hr_departments',
	);

	foreach ( $tables as $table ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
	}

	// Remove plugin options
	delete_option( 'nds_hr_version' );
	delete_option( 'nds_hr_db_version' );
	delete_option( 'nds_hr_installed_at' );
	delete_option( 'nds_hr_last_employee_number' );
	delete_option( 'nds_hr_delete_data_on_uninstall' );
	delete_option( 'nds_hr_settings' );

	// Clean up custom roles
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-roles.php';
	NDS_HR_Roles::remove_roles();
}
