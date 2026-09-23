<?php
/**
 * Permissions and Centralized Authorization Engine for NDS HR.
 * Evaluates capabilities against custom NDS HR roles and permissions tables,
 * with fallback to WP admin capabilities for seamless WP administrator coexistence.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Permissions
 */
class NDS_HR_Permissions {

	/**
	 * Request-level in-memory cache of user capabilities [ user_id => [ cap => bool ] ].
	 *
	 * @var array<int, array<string, bool>>
	 */
	protected static $capabilities_cache = array();

	/**
	 * Check if an HR user (or current session) has a specific capability.
	 *
	 * @param string   $capability Capability slug (e.g. 'nds_hr_access_admin').
	 * @param int|null $hr_user_id Optional HR user ID. Null defaults to current session user.
	 * @return bool
	 */
	public static function can( $capability, $hr_user_id = null ) {
		// Normalize capability aliases
		if ( 'settings.view' === $capability ) {
			$capability = 'nds_hr_view_settings';
		} elseif ( 'settings.manage' === $capability ) {
			$capability = 'nds_hr_manage_settings';
		}

		// 1. If no specific HR user requested, check the active NDS HR session
		if ( null === $hr_user_id ) {
			$hr_user = NDS_HR_Session::get_authenticated_user();
			if ( $hr_user ) {
				return self::user_has_capability( (int) $hr_user->id, (int) $hr_user->role_id, $capability );
			}

			// Coexistence fallback: If a WordPress Administrator is logged in via WP backend
			if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
				return true;
			}

			// Check WP capability if standard user is logged in
			if ( is_user_logged_in() && current_user_can( $capability ) ) {
				return true;
			}

			return false;
		}

		// 2. Specific HR user ID check
		$hr_user_id = absint( $hr_user_id );
		if ( ! $hr_user_id ) {
			return false;
		}

		return self::hr_user_can( $hr_user_id, $capability );
	}

	/**
	 * Check if a specific NDS HR user ID has the specified capability.
	 *
	 * @param int    $hr_user_id
	 * @param string $capability
	 * @return bool
	 */
	public static function hr_user_can( $hr_user_id, $capability ) {
		global $wpdb;

		if ( isset( self::$capabilities_cache[ $hr_user_id ][ $capability ] ) ) {
			return self::$capabilities_cache[ $hr_user_id ][ $capability ];
		}

		$users_table = NDS_HR_Database::users_table();
		$role_id     = $wpdb->get_var( $wpdb->prepare( "SELECT role_id FROM {$users_table} WHERE id = %d AND status = 'active' LIMIT 1", $hr_user_id ) );

		if ( ! $role_id ) {
			self::$capabilities_cache[ $hr_user_id ][ $capability ] = false;
			return false;
		}

		return self::user_has_capability( $hr_user_id, (int) $role_id, $capability );
	}

	/**
	 * Evaluate capability from custom role_permissions table.
	 *
	 * @param int    $hr_user_id
	 * @param int    $role_id
	 * @param string $capability
	 * @return bool
	 */
	protected static function user_has_capability( $hr_user_id, $role_id, $capability ) {
		if ( isset( self::$capabilities_cache[ $hr_user_id ][ $capability ] ) ) {
			return self::$capabilities_cache[ $hr_user_id ][ $capability ];
		}

		global $wpdb;

		$roles_table            = NDS_HR_Database::roles_table();
		$permissions_table      = NDS_HR_Database::permissions_table();
		$role_permissions_table = NDS_HR_Database::role_permissions_table();

		// Check if this role is hr_admin (system super-admin for HR)
		$role_slug = $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM {$roles_table} WHERE id = %d LIMIT 1", $role_id ) );
		if ( 'hr_admin' === $role_slug || 'administrator' === $role_slug ) {
			self::$capabilities_cache[ $hr_user_id ][ $capability ] = true;
			return true;
		}

		// Query role_permissions join
		$query = "
			SELECT 1 
			FROM {$role_permissions_table} rp
			INNER JOIN {$permissions_table} p ON rp.permission_id = p.id
			WHERE rp.role_id = %d AND p.slug = %s
			LIMIT 1
		";

		$has_cap = (bool) $wpdb->get_var( $wpdb->prepare( $query, $role_id, $capability ) );
		self::$capabilities_cache[ $hr_user_id ][ $capability ] = $has_cap;

		return $has_cap;
	}

	/**
	 * Clear in-memory capability cache.
	 */
	public static function flush_cache() {
		self::$capabilities_cache = array();
	}

	/**
	 * Check if user can access the HR administration console.
	 *
	 * @param int|null $hr_user_id
	 * @return bool
	 */
	public static function can_access_admin( $hr_user_id = null ) {
		return self::can( 'nds_hr_access_admin', $hr_user_id );
	}

	/**
	 * Check if user can access the frontend Employee Self-Service Portal (/employee/).
	 *
	 * @param int|null $hr_user_id
	 * @return bool
	 */
	public static function can_access_portal( $hr_user_id = null ) {
		return self::can( 'nds_hr_access_employee', $hr_user_id ) || self::can( 'nds_hr_view_own_profile', $hr_user_id );
	}

	/**
	 * Check if user can manage roles and capability assignments.
	 *
	 * @param int|null $hr_user_id
	 * @return bool
	 */
	public static function can_manage_roles( $hr_user_id = null ) {
		return self::can( 'nds_hr_manage_roles', $hr_user_id );
	}

	/**
	 * Check if user can view the employee directory.
	 *
	 * @param int|null $hr_user_id
	 * @return bool
	 */
	public static function can_view_employees( $hr_user_id = null ) {
		return self::can( 'nds_hr_view_employees', $hr_user_id ) || self::can( 'nds_hr_manage_employees', $hr_user_id );
	}

	/**
	 * Check if user can create or manage employees.
	 *
	 * @param int|null $hr_user_id
	 * @return bool
	 */
	public static function can_manage_employees( $hr_user_id = null ) {
		return self::can( 'nds_hr_manage_employees', $hr_user_id );
	}

	/**
	 * Check if user can edit an employee record.
	 *
	 * @param int      $employee_db_id Employee internal database ID.
	 * @param int|null $hr_user_id     HR User ID or null for current.
	 * @return bool
	 */
	public static function can_edit_employee( $employee_db_id = 0, $hr_user_id = null ) {
		return self::can( 'nds_hr_edit_employees', $hr_user_id ) || self::can( 'nds_hr_manage_employees', $hr_user_id );
	}

	/**
	 * Check if user can delete an employee record.
	 *
	 * @param int|null $hr_user_id
	 * @return bool
	 */
	public static function can_delete_employees( $hr_user_id = null ) {
		return self::can( 'nds_hr_delete_employees', $hr_user_id );
	}

	/**
	 * Check if user can view a given employee record.
	 * HR Managers can view all; employees can ONLY view their own record.
	 *
	 * @param int      $employee_db_id Employee internal database ID.
	 * @param int|null $hr_user_id     Optional HR user ID.
	 * @return bool
	 */
	public static function can_view_employee( $employee_db_id, $hr_user_id = null ) {
		if ( self::can_view_employees( $hr_user_id ) ) {
			return true;
		}

		$uid = $hr_user_id ? absint( $hr_user_id ) : NDS_HR_Session::get_current_user_id();
		if ( ! $uid ) {
			// Legacy fallback for WP user session
			$wp_uid = get_current_user_id();
			if ( $wp_uid ) {
				return self::is_legacy_wp_employee_owner( $employee_db_id, $wp_uid );
			}
			return false;
		}

		return self::is_employee_owner( $employee_db_id, $uid );
	}

	/**
	 * Ownership check: verify if an employee record belongs to the given HR user.
	 *
	 * @param int $employee_db_id
	 * @param int $hr_user_id
	 * @return bool
	 */
	public static function is_employee_owner( $employee_db_id, $hr_user_id ) {
		global $wpdb;

		if ( empty( $employee_db_id ) || empty( $hr_user_id ) ) {
			return false;
		}

		$table = NDS_HR_Database::employees_table();
		$owner = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE id = %d AND hr_user_id = %d LIMIT 1",
				$employee_db_id,
				$hr_user_id
			)
		);

		return ! empty( $owner );
	}

	/**
	 * Non-destructive legacy fallback: check ownership via legacy user_id column.
	 *
	 * @param int $employee_db_id
	 * @param int $wp_user_id
	 * @return bool
	 */
	public static function is_legacy_wp_employee_owner( $employee_db_id, $wp_user_id ) {
		global $wpdb;

		if ( empty( $employee_db_id ) || empty( $wp_user_id ) ) {
			return false;
		}

		$table = NDS_HR_Database::employees_table();
		$owner = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE id = %d AND user_id = %d LIMIT 1",
				$employee_db_id,
				$wp_user_id
			)
		);

		return ! empty( $owner );
	}

	/**
	 * Check if user can view audit and compliance logs.
	 *
	 * @param int|null $hr_user_id
	 * @return bool
	 */
	public static function can_view_audit_logs( $hr_user_id = null ) {
		return self::can( 'nds_hr_view_audit_logs', $hr_user_id );
	}

	/**
	 * Check if user can manage departments and positions.
	 *
	 * @param int|null $hr_user_id
	 * @return bool
	 */
	public static function can_manage_departments( $hr_user_id = null ) {
		return self::can( 'nds_hr_manage_departments', $hr_user_id );
	}

	/**
	 * Check if user can view settings.
	 *
	 * @param int|null $hr_user_id
	 * @return bool
	 */
	public static function can_view_settings( $hr_user_id = null ) {
		return self::can( 'nds_hr_view_settings', $hr_user_id ) || self::can( 'nds_hr_manage_settings', $hr_user_id );
	}

	/**
	 * Check if user can manage/modify settings.
	 *
	 * @param int|null $hr_user_id
	 * @return bool
	 */
	public static function can_manage_settings( $hr_user_id = null ) {
		return self::can( 'nds_hr_manage_settings', $hr_user_id );
	}
}
