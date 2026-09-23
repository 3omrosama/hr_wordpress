<?php
/**
 * Role and Capability Management Engine.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Roles
 */
class NDS_HR_Roles {

	/**
	 * Role slugs.
	 */
	const ROLE_ADMIN    = 'nds_hr_admin';
	const ROLE_MANAGER  = 'nds_hr_manager';
	const ROLE_OFFICER  = 'nds_hr_officer';
	const ROLE_EMPLOYEE = 'nds_hr_employee';

	/**
	 * Option key for custom role capabilities overrides.
	 */
	const OPTION_ROLE_CAPS = 'nds_hr_role_capabilities';

	/**
	 * Capabilities grouped logically by module for matrix display and registration.
	 *
	 * @return array
	 */
	public static function get_capabilities_by_group() {
		return array(
			'access' => array(
				'label' => __( 'System Access', 'nds-hr' ),
				'capabilities' => array(
					'nds_hr_access_admin'    => __( 'Access HR Administration (/wp-admin/)', 'nds-hr' ),
					'nds_hr_access_employee' => __( 'Access Employee Self-Service Portal (/employee/)', 'nds-hr' ),
				),
			),
			'employees' => array(
				'label' => __( 'Employee Management', 'nds-hr' ),
				'capabilities' => array(
					'nds_hr_view_employees'   => __( 'View Employee Directory', 'nds-hr' ),
					'nds_hr_manage_employees' => __( 'Create Employees & Manage Records', 'nds-hr' ),
					'nds_hr_edit_employees'   => __( 'Edit Existing Employee Profiles', 'nds-hr' ),
					'nds_hr_delete_employees' => __( 'Delete Employee Records', 'nds-hr' ),
				),
			),
			'departments' => array(
				'label' => __( 'Organization & Structure', 'nds-hr' ),
				'capabilities' => array(
					'nds_hr_view_departments'   => __( 'View Departments & Designations', 'nds-hr' ),
					'nds_hr_manage_departments' => __( 'Manage Departments & Positions', 'nds-hr' ),
				),
			),
			'self_service' => array(
				'label' => __( 'Employee Self-Service', 'nds-hr' ),
				'capabilities' => array(
					'nds_hr_view_own_profile' => __( 'View Own Profile', 'nds-hr' ),
					'nds_hr_edit_own_profile' => __( 'Edit Own Contact Details', 'nds-hr' ),
				),
			),
			'roles_settings' => array(
				'label' => __( 'Administration & Security', 'nds-hr' ),
				'capabilities' => array(
					'nds_hr_view_audit_logs' => __( 'View Audit & Compliance Logs', 'nds-hr' ),
					'nds_hr_manage_roles'    => __( 'Manage Roles & Permissions', 'nds-hr' ),
					'nds_hr_manage_settings' => __( 'Manage System Global Settings', 'nds-hr' ),
				),
			),
			'attendance' => array(
				'label' => __( 'Attendance (Future Foundation)', 'nds-hr' ),
				'capabilities' => array(
					'nds_hr_manage_attendance'   => __( 'Manage Company Attendance Records', 'nds-hr' ),
					'nds_hr_view_own_attendance' => __( 'View Personal Attendance History', 'nds-hr' ),
				),
			),
			'leaves' => array(
				'label' => __( 'Leave Management (Future Foundation)', 'nds-hr' ),
				'capabilities' => array(
					'nds_hr_manage_leaves'   => __( 'Manage Leave Types & Balances', 'nds-hr' ),
					'nds_hr_approve_leaves'  => __( 'Approve/Reject Leave Requests', 'nds-hr' ),
					'nds_hr_view_own_leaves' => __( 'Submit & View Own Leaves', 'nds-hr' ),
				),
			),
			'payroll' => array(
				'label' => __( 'Payroll (Future Foundation)', 'nds-hr' ),
				'capabilities' => array(
					'nds_hr_manage_payroll'    => __( 'Process Payroll & Salary Sheets', 'nds-hr' ),
					'nds_hr_view_payroll'      => __( 'View Workforce Payroll Reports', 'nds-hr' ),
					'nds_hr_view_own_payslips' => __( 'View Personal Payslips', 'nds-hr' ),
				),
			),
		);
	}

	/**
	 * Flat list of all registered NDS HR capabilities with descriptions.
	 *
	 * @return array<string, string>
	 */
	public static function get_all_capabilities() {
		$flat = array();
		foreach ( self::get_capabilities_by_group() as $group ) {
			foreach ( $group['capabilities'] as $cap => $desc ) {
				$flat[ $cap ] = $desc;
			}
		}
		return $flat;
	}

	/**
	 * Default role capability presets.
	 *
	 * @return array<string, array<string, bool>>
	 */
	public static function get_default_role_matrix() {
		return array(
			'administrator' => array_fill_keys( array_keys( self::get_all_capabilities() ), true ),
			self::ROLE_ADMIN => array_fill_keys( array_keys( self::get_all_capabilities() ), true ),
			self::ROLE_MANAGER => array(
				'nds_hr_access_admin'        => true,
				'nds_hr_access_employee'     => true,
				'nds_hr_view_employees'      => true,
				'nds_hr_manage_employees'    => true,
				'nds_hr_edit_employees'      => true,
				'nds_hr_delete_employees'    => false,
				'nds_hr_view_departments'    => true,
				'nds_hr_manage_departments'  => false,
				'nds_hr_view_own_profile'    => true,
				'nds_hr_edit_own_profile'    => true,
				'nds_hr_view_audit_logs'     => true,
				'nds_hr_manage_roles'        => false,
				'nds_hr_manage_settings'     => false,
				'nds_hr_manage_attendance'   => true,
				'nds_hr_view_own_attendance' => true,
				'nds_hr_manage_leaves'       => false,
				'nds_hr_approve_leaves'      => true,
				'nds_hr_view_own_leaves'     => true,
				'nds_hr_manage_payroll'      => false,
				'nds_hr_view_payroll'        => true,
				'nds_hr_view_own_payslips'   => true,
			),
			self::ROLE_OFFICER => array(
				'nds_hr_access_admin'        => true,
				'nds_hr_access_employee'     => true,
				'nds_hr_view_employees'      => true,
				'nds_hr_manage_employees'    => false,
				'nds_hr_edit_employees'      => false,
				'nds_hr_delete_employees'    => false,
				'nds_hr_view_departments'    => true,
				'nds_hr_manage_departments'  => false,
				'nds_hr_view_own_profile'    => true,
				'nds_hr_edit_own_profile'    => true,
				'nds_hr_view_audit_logs'     => false,
				'nds_hr_manage_roles'        => false,
				'nds_hr_manage_settings'     => false,
				'nds_hr_manage_attendance'   => false,
				'nds_hr_view_own_attendance' => true,
				'nds_hr_manage_leaves'       => false,
				'nds_hr_approve_leaves'      => false,
				'nds_hr_view_own_leaves'     => true,
				'nds_hr_manage_payroll'      => false,
				'nds_hr_view_payroll'        => false,
				'nds_hr_view_own_payslips'   => true,
			),
			self::ROLE_EMPLOYEE => array(
				'nds_hr_access_admin'        => false,
				'nds_hr_access_employee'     => true,
				'nds_hr_view_employees'      => false,
				'nds_hr_manage_employees'    => false,
				'nds_hr_edit_employees'      => false,
				'nds_hr_delete_employees'    => false,
				'nds_hr_view_departments'    => false,
				'nds_hr_manage_departments'  => false,
				'nds_hr_view_own_profile'    => true,
				'nds_hr_edit_own_profile'    => true,
				'nds_hr_view_audit_logs'     => false,
				'nds_hr_manage_roles'        => false,
				'nds_hr_manage_settings'     => false,
				'nds_hr_manage_attendance'   => false,
				'nds_hr_view_own_attendance' => true,
				'nds_hr_manage_leaves'       => false,
				'nds_hr_approve_leaves'      => false,
				'nds_hr_view_own_leaves'     => true,
				'nds_hr_manage_payroll'      => false,
				'nds_hr_view_payroll'        => false,
				'nds_hr_view_own_payslips'   => true,
			),
		);
	}

	/**
	 * Get all roles directly from custom NDS HR roles table.
	 *
	 * @return array
	 */
	public static function get_custom_roles() {
		global $wpdb;
		$roles_table = NDS_HR_Database::roles_table();
		$results     = $wpdb->get_results( "SELECT * FROM {$roles_table} ORDER BY id ASC", ARRAY_A );
		if ( empty( $results ) ) {
			return self::get_manageable_roles();
		}
		$roles = array();
		foreach ( $results as $row ) {
			$roles[ $row['slug'] ] = array(
				'id'          => (int) $row['id'],
				'name'        => $row['name'],
				'description' => $row['description'],
				'is_system'   => (bool) $row['is_system'],
			);
		}
		return $roles;
	}

	/**
	 * Count assigned NDS HR users per role.
	 *
	 * @return array<string, int>
	 */
	public static function get_hr_user_counts_by_role() {
		global $wpdb;
		$users_table = NDS_HR_Database::users_table();
		$roles_table = NDS_HR_Database::roles_table();

		$counts = array();
		$results = $wpdb->get_results(
			"SELECT r.slug, COUNT(u.id) as user_count 
			 FROM {$roles_table} r 
			 LEFT JOIN {$users_table} u ON r.id = u.role_id 
			 GROUP BY r.slug",
			ARRAY_A
		);

		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				$counts[ $row['slug'] ] = (int) $row['user_count'];
			}
		}

		return $counts;
	}

	/**
	 * Get managed HR roles with labels and descriptions.
	 *
	 * @return array
	 */
	public static function get_manageable_roles() {
		return array(
			'administrator' => array(
				'name'        => __( 'WordPress Administrator', 'nds-hr' ),
				'description' => __( 'Full access to WordPress administrative features and all HR modules.', 'nds-hr' ),
				'is_system'   => true,
			),
			self::ROLE_ADMIN => array(
				'name'        => __( 'HR Administrator', 'nds-hr' ),
				'description' => __( 'Complete administrative control over all HR modules, roles, and settings.', 'nds-hr' ),
				'is_system'   => false,
			),
			self::ROLE_MANAGER => array(
				'name'        => __( 'HR Manager', 'nds-hr' ),
				'description' => __( 'Workforce management, approvals, team operations, and audit review.', 'nds-hr' ),
				'is_system'   => false,
			),
			self::ROLE_OFFICER => array(
				'name'        => __( 'HR Officer', 'nds-hr' ),
				'description' => __( 'Read-only administrative dashboard and directory access.', 'nds-hr' ),
				'is_system'   => false,
			),
			self::ROLE_EMPLOYEE => array(
				'name'        => __( 'Employee', 'nds-hr' ),
				'description' => __( 'Self-service portal access only. Blocked from wp-admin.', 'nds-hr' ),
				'is_system'   => false,
			),
		);
	}

	/**
	 * Register or sync custom roles and capabilities in WordPress.
	 */
	public static function register_roles() {
		$all_caps     = self::get_all_capabilities();
		$matrix       = self::get_role_matrix();
		$manage_roles = self::get_manageable_roles();

		// Ensure custom roles exist
		foreach ( $manage_roles as $role_slug => $role_meta ) {
			if ( 'administrator' === $role_slug ) {
				continue;
			}

			$role_caps = isset( $matrix[ $role_slug ] ) ? array_filter( $matrix[ $role_slug ] ) : array();

			// Add basic WP read permission for standard authentication
			$role_caps['read'] = true;
			if ( $role_slug !== self::ROLE_EMPLOYEE ) {
				$role_caps['upload_files'] = true;
			}

			$existing_role = get_role( $role_slug );
			if ( ! $existing_role ) {
				add_role( $role_slug, $role_meta['name'], $role_caps );
			} else {
				// Sync capabilities to match matrix
				foreach ( $all_caps as $cap => $desc ) {
					if ( ! empty( $matrix[ $role_slug ][ $cap ] ) ) {
						$existing_role->add_cap( $cap, true );
					} else {
						$existing_role->remove_cap( $cap );
					}
				}
			}
		}

		// Ensure WordPress Administrator has all capabilities
		$wp_admin = get_role( 'administrator' );
		if ( $wp_admin ) {
			foreach ( $all_caps as $cap => $desc ) {
				$wp_admin->add_cap( $cap, true );
			}
		}
	}

	/**
	 * Retrieve active role capabilities matrix (defaults merged with saved option).
	 *
	 * @return array
	 */
	public static function get_role_matrix() {
		$defaults = self::get_default_role_matrix();
		$saved    = get_option( self::OPTION_ROLE_CAPS, array() );

		if ( ! is_array( $saved ) || empty( $saved ) ) {
			return $defaults;
		}

		// Deep merge saved matrix with defaults
		$matrix = $defaults;
		foreach ( $saved as $role => $caps ) {
			if ( isset( $matrix[ $role ] ) && is_array( $caps ) ) {
				foreach ( $caps as $cap => $granted ) {
					$matrix[ $role ][ $cap ] = (bool) $granted;
				}
			}
		}

		return $matrix;
	}

	/**
	 * Update capabilities for roles and sync into WordPress Role objects and custom database tables.
	 *
	 * @param array $submitted_matrix [ role => [ cap => 1 ] ]
	 * @return bool|WP_Error
	 */
	public static function save_role_matrix( array $submitted_matrix ) {
		global $wpdb;

		$all_caps               = self::get_all_capabilities();
		$manageable             = self::get_manageable_roles();
		$sanitized_matrix       = array();
		$roles_table            = NDS_HR_Database::roles_table();
		$permissions_table      = NDS_HR_Database::permissions_table();
		$role_permissions_table = NDS_HR_Database::role_permissions_table();

		// Fetch existing permissions mapping
		$perms_rows = $wpdb->get_results( "SELECT id, slug FROM {$permissions_table}", OBJECT_K );
		$roles_rows = $wpdb->get_results( "SELECT id, slug FROM {$roles_table}", OBJECT_K );

		foreach ( $manageable as $role_slug => $role_meta ) {
			$sanitized_matrix[ $role_slug ] = array();
			$role_obj = get_role( $role_slug );
			$db_role_id = isset( $roles_rows[ $role_slug ] ) ? (int) $roles_rows[ $role_slug ]->id : 0;

			// If mapped to custom database role, clear current DB assignments first
			if ( $db_role_id > 0 ) {
				$wpdb->delete( $role_permissions_table, array( 'role_id' => $db_role_id ), array( '%d' ) );
			}

			foreach ( $all_caps as $cap => $desc ) {
				// Administrator always retains manage_roles and access_admin
				if ( 'administrator' === $role_slug && in_array( $cap, array( 'nds_hr_access_admin', 'nds_hr_manage_roles', 'nds_hr_manage_settings' ), true ) ) {
					$granted = true;
				} else {
					$granted = ! empty( $submitted_matrix[ $role_slug ][ $cap ] );
				}

				$sanitized_matrix[ $role_slug ][ $cap ] = $granted;

				// Sync into WP role object
				if ( $role_obj ) {
					if ( $granted ) {
						$role_obj->add_cap( $cap, true );
					} else {
						$role_obj->remove_cap( $cap );
					}
				}

				// Sync into custom role_permissions database table
				if ( $granted && $db_role_id > 0 && isset( $perms_rows[ $cap ] ) ) {
					$wpdb->insert(
						$role_permissions_table,
						array(
							'role_id'       => $db_role_id,
							'permission_id' => (int) $perms_rows[ $cap ]->id,
						),
						array( '%d', '%d' )
					);
				}
			}
		}

		update_option( self::OPTION_ROLE_CAPS, $sanitized_matrix );
		NDS_HR_Permissions::flush_cache();

		// Log security audit
		NDS_HR_Audit_Logger::log(
			'role_permissions_updated',
			'role_matrix',
			0,
			null,
			array( 'updated_roles' => array_keys( $sanitized_matrix ) )
		);

		return true;
	}

	/**
	 * Reset role permissions matrix to default installation presets.
	 */
	public static function reset_role_matrix_to_defaults() {
		global $wpdb;
		delete_option( self::OPTION_ROLE_CAPS );
		self::register_roles();

		// Re-seed DB defaults
		$role_permissions_table = NDS_HR_Database::role_permissions_table();
		$wpdb->query( "TRUNCATE TABLE {$role_permissions_table}" );
		NDS_HR_Database::seed_auth_defaults();
		NDS_HR_Permissions::flush_cache();

		NDS_HR_Audit_Logger::log(
			'role_permissions_reset',
			'role_matrix',
			0,
			null,
			array( 'action' => 'reset_to_defaults' )
		);
	}

	/**
	 * Clean up roles upon complete uninstallation.
	 */
	public static function remove_roles() {
		remove_role( self::ROLE_ADMIN );
		remove_role( self::ROLE_MANAGER );
		remove_role( self::ROLE_OFFICER );
		remove_role( self::ROLE_EMPLOYEE );

		$wp_admin = get_role( 'administrator' );
		if ( $wp_admin ) {
			foreach ( self::get_all_capabilities() as $cap => $desc ) {
				$wp_admin->remove_cap( $cap );
			}
		}

		delete_option( self::OPTION_ROLE_CAPS );
	}
}
