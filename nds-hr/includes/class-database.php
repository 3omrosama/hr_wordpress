<?php
/**
 * Database Management and Schema Migration Class.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Database
 */
class NDS_HR_Database {

	/**
	 * Database version option key.
	 */
	const DB_VERSION_KEY = 'nds_hr_db_version';

	/**
	 * Table name accessors.
	 *
	 * @param string $name Short table identifier.
	 * @return string Full prefixed table name.
	 */
	public static function get_table_name( $name ) {
		global $wpdb;
		return $wpdb->prefix . 'nds_hr_' . $name;
	}

	/**
	 * Shortcut table name helpers.
	 */
	public static function employees_table() {
		return self::get_table_name( 'employees' );
	}

	public static function departments_table() {
		return self::get_table_name( 'departments' );
	}

	public static function positions_table() {
		return self::get_table_name( 'positions' );
	}

	public static function audit_logs_table() {
		return self::get_table_name( 'audit_logs' );
	}

	public static function users_table() {
		return self::get_table_name( 'users' );
	}

	public static function roles_table() {
		return self::get_table_name( 'roles' );
	}

	public static function permissions_table() {
		return self::get_table_name( 'permissions' );
	}

	public static function role_permissions_table() {
		return self::get_table_name( 'role_permissions' );
	}

	public static function sessions_table() {
		return self::get_table_name( 'sessions' );
	}

	/**
	 * Run on plugin activation or manual update.
	 */
	public static function install() {
		$current_db_version = get_option( self::DB_VERSION_KEY, '0.0.0' );

		if ( version_compare( $current_db_version, NDS_HR_DB_VERSION, '<' ) ) {
			self::run_migrations( $current_db_version );
			update_option( self::DB_VERSION_KEY, NDS_HR_DB_VERSION );
		}
	}

	/**
	 * Check for database updates during runtime.
	 */
	public function check_updates() {
		$installed_version = get_option( self::DB_VERSION_KEY, '0.0.0' );
		if ( version_compare( $installed_version, NDS_HR_DB_VERSION, '<' ) ) {
			self::install();
		}
	}

	/**
	 * Execute migrations based on version.
	 *
	 * @param string $from_version Currently installed version.
	 */
	protected static function run_migrations( $from_version ) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Phase 1 Initial Schema
		if ( version_compare( $from_version, '1.0.0', '<' ) ) {
			self::create_phase_1_tables();
			self::seed_initial_defaults();
		}

		// Phase 2 Independent Authentication & Authorization Schema (v2.0.0)
		if ( version_compare( $from_version, '2.0.0', '<' ) ) {
			self::create_auth_tables();
			self::seed_auth_defaults();
			self::upgrade_employees_table_non_destructively();
		}

		// Phase 2.1 Employee Form Refinements & Contract Schema (v2.1.0)
		if ( version_compare( $from_version, '2.1.0', '<' ) ) {
			self::upgrade_employees_table_v2_1();
		}
	}

	/**
	 * Non-destructively add contract columns and phone_country_code to employees table.
	 */
	protected static function upgrade_employees_table_v2_1() {
		global $wpdb;

		$employees_table = self::employees_table();

		$columns_to_add = array(
			'contract_type'          => "varchar(50) NOT NULL DEFAULT 'permanent' AFTER `employment_status`",
			'contract_start_date'    => "date DEFAULT NULL AFTER `contract_type`",
			'contract_end_date'      => "date DEFAULT NULL AFTER `contract_start_date`",
			'contract_document_url'  => "text DEFAULT NULL AFTER `contract_end_date`",
			'contract_document_name' => "varchar(255) DEFAULT '' AFTER `contract_document_url`",
			'phone_country_code'     => "varchar(10) DEFAULT '+20' AFTER `phone`",
		);

		foreach ( $columns_to_add as $column_name => $definition ) {
			$column_exists = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = %s",
					$employees_table,
					$column_name
				)
			);

			if ( empty( $column_exists ) ) {
				$wpdb->query( "ALTER TABLE `{$employees_table}` ADD COLUMN `{$column_name}` {$definition}" );
			}
		}
	}

	/**
	 * Non-destructively add hr_user_id to employees table, preserving user_id and existing records.
	 */
	protected static function upgrade_employees_table_non_destructively() {
		global $wpdb;

		$employees_table = self::employees_table();

		// Check if column hr_user_id already exists
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = 'hr_user_id'",
				$employees_table
			)
		);

		if ( empty( $column_exists ) ) {
			// Non-destructive addition: keep user_id intact
			$wpdb->query( "ALTER TABLE `{$employees_table}` ADD COLUMN `hr_user_id` bigint(20) unsigned DEFAULT NULL AFTER `user_id`, ADD KEY `hr_user_id` (`hr_user_id`)" );
		}
	}

	/**
	 * Create all custom tables required for independent NDS HR authentication & RBAC.
	 */
	protected static function create_auth_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$users_table            = self::users_table();
		$roles_table            = self::roles_table();
		$permissions_table      = self::permissions_table();
		$role_permissions_table = self::role_permissions_table();
		$sessions_table         = self::sessions_table();

		// 1. Roles table
		$sql_roles = "CREATE TABLE {$roles_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			slug varchar(50) NOT NULL,
			name varchar(100) NOT NULL,
			description text DEFAULT NULL,
			is_system tinyint(1) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug)
		) {$charset_collate};";

		// 2. Permissions table
		$sql_permissions = "CREATE TABLE {$permissions_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			slug varchar(100) NOT NULL,
			module varchar(50) NOT NULL,
			label varchar(150) NOT NULL,
			description text DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug),
			KEY module (module)
		) {$charset_collate};";

		// 3. Role Permissions table
		$sql_role_permissions = "CREATE TABLE {$role_permissions_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			role_id bigint(20) unsigned NOT NULL,
			permission_id bigint(20) unsigned NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY role_perm (role_id, permission_id),
			KEY role_id (role_id),
			KEY permission_id (permission_id)
		) {$charset_collate};";

		// 4. Users table (stores hashed passwords and SHA-256 hashed reset tokens only)
		$sql_users = "CREATE TABLE {$users_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			username varchar(60) NOT NULL,
			email varchar(100) NOT NULL,
			password_hash varchar(255) NOT NULL,
			role_id bigint(20) unsigned NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'active',
			first_name varchar(100) DEFAULT '',
			last_name varchar(100) DEFAULT '',
			display_name varchar(200) NOT NULL,
			require_password_change tinyint(1) NOT NULL DEFAULT 0,
			last_login_at datetime DEFAULT NULL,
			password_reset_token_hash varchar(64) DEFAULT NULL,
			password_reset_expires_at datetime DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY username (username),
			UNIQUE KEY email (email),
			KEY role_id (role_id),
			KEY status (status),
			KEY reset_token_hash (password_reset_token_hash)
		) {$charset_collate};";

		// 5. Sessions table (stores session tokens with SHA-256 hash or secure 64-char token)
		$sql_sessions = "CREATE TABLE {$sessions_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			session_token varchar(128) NOT NULL,
			hr_user_id bigint(20) unsigned NOT NULL,
			ip_address varchar(100) DEFAULT '',
			user_agent text DEFAULT NULL,
			expires_at datetime NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY session_token (session_token),
			KEY hr_user_id (hr_user_id),
			KEY expires_at (expires_at)
		) {$charset_collate};";

		dbDelta( $sql_roles );
		dbDelta( $sql_permissions );
		dbDelta( $sql_role_permissions );
		dbDelta( $sql_users );
		dbDelta( $sql_sessions );
	}

	/**
	 * Seed initial default roles and permissions in the custom database tables.
	 */
	public static function seed_auth_defaults() {
		global $wpdb;

		$roles_table            = self::roles_table();
		$permissions_table      = self::permissions_table();
		$role_permissions_table = self::role_permissions_table();

		// 1. Seed Roles if empty
		$existing_roles = $wpdb->get_results( "SELECT id, slug FROM {$roles_table}", OBJECT_K );
		$roles_map = array();

		if ( ! empty( $existing_roles ) ) {
			foreach ( $existing_roles as $slug => $row ) {
				$roles_map[ $slug ] = (int) $row->id;
			}
		}

		$default_roles = array(
			'hr_admin' => array(
				'name'        => __( 'HR Administrator', 'nds-hr' ),
				'description' => __( 'Full administrative authority over NDS HR settings, roles, employees, and audit logs.', 'nds-hr' ),
				'is_system'   => 1,
			),
			'hr_manager' => array(
				'name'        => __( 'HR Manager', 'nds-hr' ),
				'description' => __( 'Supervises all organizational departments, employee records, and reporting.', 'nds-hr' ),
				'is_system'   => 1,
			),
			'hr_officer' => array(
				'name'        => __( 'HR Officer', 'nds-hr' ),
				'description' => __( 'Operational day-to-day employee record data entry and maintenance.', 'nds-hr' ),
				'is_system'   => 1,
			),
			'hr_employee' => array(
				'name'        => __( 'Employee', 'nds-hr' ),
				'description' => __( 'Standard employee with self-service portal access to view their own profile.', 'nds-hr' ),
				'is_system'   => 1,
			),
		);

		foreach ( $default_roles as $slug => $r_data ) {
			if ( ! isset( $roles_map[ $slug ] ) ) {
				$wpdb->insert(
					$roles_table,
					array(
						'slug'        => $slug,
						'name'        => $r_data['name'],
						'description' => $r_data['description'],
						'is_system'   => $r_data['is_system'],
					),
					array( '%s', '%s', '%s', '%d' )
				);
				$roles_map[ $slug ] = $wpdb->insert_id;
			}
		}

		// 2. Seed Permissions
		$default_permissions = array(
			// Access
			array( 'slug' => 'nds_hr_access_admin', 'module' => 'access', 'label' => __( 'Access HR Administration Console', 'nds-hr' ) ),
			array( 'slug' => 'nds_hr_access_employee', 'module' => 'access', 'label' => __( 'Access Employee Self-Service Portal', 'nds-hr' ) ),
			// Employees
			array( 'slug' => 'nds_hr_view_employees', 'module' => 'employees', 'label' => __( 'View Employee Directory', 'nds-hr' ) ),
			array( 'slug' => 'nds_hr_manage_employees', 'module' => 'employees', 'label' => __( 'Create and Manage Employee Records', 'nds-hr' ) ),
			array( 'slug' => 'nds_hr_edit_employees', 'module' => 'employees', 'label' => __( 'Edit Existing Employee Profiles', 'nds-hr' ) ),
			array( 'slug' => 'nds_hr_delete_employees', 'module' => 'employees', 'label' => __( 'Delete Employee Records', 'nds-hr' ) ),
			// Departments
			array( 'slug' => 'nds_hr_view_departments', 'module' => 'departments', 'label' => __( 'View Departments & Positions', 'nds-hr' ) ),
			array( 'slug' => 'nds_hr_manage_departments', 'module' => 'departments', 'label' => __( 'Manage Departments & Positions', 'nds-hr' ) ),
			// Self Service
			array( 'slug' => 'nds_hr_view_own_profile', 'module' => 'self_service', 'label' => __( 'View Own Employee Profile', 'nds-hr' ) ),
			array( 'slug' => 'nds_hr_edit_own_profile', 'module' => 'self_service', 'label' => __( 'Update Own Contact Details', 'nds-hr' ) ),
			// Roles & Security
			array( 'slug' => 'nds_hr_manage_roles', 'module' => 'security', 'label' => __( 'Manage HR Roles & Permissions', 'nds-hr' ) ),
			array( 'slug' => 'nds_hr_view_audit_logs', 'module' => 'security', 'label' => __( 'View Security and Audit Logs', 'nds-hr' ) ),
			array( 'slug' => 'nds_hr_manage_settings', 'module' => 'security', 'label' => __( 'Configure HR System Settings', 'nds-hr' ) ),
		);

		$perm_map = array();
		$existing_perms = $wpdb->get_results( "SELECT id, slug FROM {$permissions_table}", OBJECT_K );
		if ( ! empty( $existing_perms ) ) {
			foreach ( $existing_perms as $slug => $row ) {
				$perm_map[ $slug ] = (int) $row->id;
			}
		}

		foreach ( $default_permissions as $p ) {
			if ( ! isset( $perm_map[ $p['slug'] ] ) ) {
				$wpdb->insert(
					$permissions_table,
					array(
						'slug'   => $p['slug'],
						'module' => $p['module'],
						'label'  => $p['label'],
					),
					array( '%s', '%s', '%s' )
				);
				$perm_map[ $p['slug'] ] = $wpdb->insert_id;
			}
		}

		// 3. Seed Default Role Permissions Matrix
		$role_assignments = array(
			'hr_admin' => array_keys( $perm_map ),
			'hr_manager' => array(
				'nds_hr_access_admin',
				'nds_hr_access_employee',
				'nds_hr_view_employees',
				'nds_hr_manage_employees',
				'nds_hr_edit_employees',
				'nds_hr_view_departments',
				'nds_hr_manage_departments',
				'nds_hr_view_own_profile',
				'nds_hr_edit_own_profile',
				'nds_hr_view_audit_logs',
			),
			'hr_officer' => array(
				'nds_hr_access_admin',
				'nds_hr_access_employee',
				'nds_hr_view_employees',
				'nds_hr_manage_employees',
				'nds_hr_edit_employees',
				'nds_hr_view_departments',
				'nds_hr_view_own_profile',
				'nds_hr_edit_own_profile',
			),
			'hr_employee' => array(
				'nds_hr_access_employee',
				'nds_hr_view_own_profile',
				'nds_hr_edit_own_profile',
			),
		);

		foreach ( $role_assignments as $r_slug => $p_slugs ) {
			if ( ! isset( $roles_map[ $r_slug ] ) ) {
				continue;
			}
			$role_id = $roles_map[ $r_slug ];

			foreach ( $p_slugs as $p_slug ) {
				if ( ! isset( $perm_map[ $p_slug ] ) ) {
					continue;
				}
				$perm_id = $perm_map[ $p_slug ];

				// Check if mapping exists
				$exists = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT id FROM {$role_permissions_table} WHERE role_id = %d AND permission_id = %d",
						$role_id,
						$perm_id
					)
				);

				if ( ! $exists ) {
					$wpdb->insert(
						$role_permissions_table,
						array(
							'role_id'       => $role_id,
							'permission_id' => $perm_id,
						),
						array( '%d', '%d' )
					);
				}
			}
		}
	}

	/**
	 * Create all custom tables required for Phase 1.
	 */
	protected static function create_phase_1_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$departments_table = self::departments_table();
		$positions_table   = self::positions_table();
		$employees_table   = self::employees_table();
		$audit_logs_table  = self::audit_logs_table();

		// 1. Departments table
		$sql_departments = "CREATE TABLE {$departments_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(150) NOT NULL,
			code varchar(50) NOT NULL,
			description text DEFAULT NULL,
			manager_id bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY code (code),
			KEY manager_id (manager_id)
		) {$charset_collate};";

		// 2. Positions table
		$sql_positions = "CREATE TABLE {$positions_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			department_id bigint(20) unsigned DEFAULT NULL,
			title varchar(150) NOT NULL,
			code varchar(50) NOT NULL,
			description text DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY code (code),
			KEY department_id (department_id)
		) {$charset_collate};";

		// 3. Employees table
		$sql_employees = "CREATE TABLE {$employees_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			employee_id varchar(50) NOT NULL,
			user_id bigint(20) unsigned DEFAULT NULL,
			hr_user_id bigint(20) unsigned DEFAULT NULL,
			first_name varchar(100) NOT NULL,
			last_name varchar(100) NOT NULL,
			full_name varchar(200) NOT NULL,
			email varchar(100) NOT NULL,
			phone varchar(50) DEFAULT '',
			phone_country_code varchar(10) DEFAULT '+20',
			mobile varchar(50) DEFAULT '',
			national_id varchar(50) DEFAULT '',
			date_of_birth date DEFAULT NULL,
			gender varchar(20) DEFAULT 'male',
			hire_date date NOT NULL,
			department_id bigint(20) unsigned DEFAULT NULL,
			position_id bigint(20) unsigned DEFAULT NULL,
			employment_status varchar(30) NOT NULL DEFAULT 'active',
			contract_type varchar(50) NOT NULL DEFAULT 'permanent',
			contract_start_date date DEFAULT NULL,
			contract_end_date date DEFAULT NULL,
			contract_document_url text DEFAULT NULL,
			contract_document_name varchar(255) DEFAULT '',
			manager_id bigint(20) unsigned DEFAULT NULL,
			basic_salary decimal(12,2) NOT NULL DEFAULT 0.00,
			profile_photo_url text DEFAULT NULL,
			address text DEFAULT NULL,
			emergency_contact_name varchar(100) DEFAULT '',
			emergency_contact_phone varchar(50) DEFAULT '',
			emergency_contact_relationship varchar(50) DEFAULT '',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY employee_id (employee_id),
			KEY user_id (user_id),
			KEY hr_user_id (hr_user_id),
			KEY department_id (department_id),
			KEY position_id (position_id),
			KEY status (employment_status),
			KEY hire_date (hire_date),
			KEY email (email)
		) {$charset_collate};";

		// 4. Audit Logs table
		$sql_audit_logs = "CREATE TABLE {$audit_logs_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			action varchar(100) NOT NULL,
			entity_type varchar(50) NOT NULL,
			entity_id bigint(20) unsigned NOT NULL DEFAULT 0,
			old_values longtext DEFAULT NULL,
			new_values longtext DEFAULT NULL,
			ip_address varchar(100) DEFAULT '',
			user_agent text DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY action (action),
			KEY entity_lookup (entity_type, entity_id),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) {$charset_collate};";

		// Execute with dbDelta
		dbDelta( $sql_departments );
		dbDelta( $sql_positions );
		dbDelta( $sql_employees );
		dbDelta( $sql_audit_logs );
	}

	/**
	 * Seed initial demo departments and positions if table is empty.
	 */
	protected static function seed_initial_defaults() {
		global $wpdb;

		$departments_table = self::departments_table();
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$departments_table}" );

		if ( 0 === (int) $count ) {
			// Seed Core Departments
			$wpdb->insert(
				$departments_table,
				array(
					'name'        => 'Human Resources',
					'code'        => 'HR',
					'description' => 'Human resources and people management department.',
				),
				array( '%s', '%s', '%s' )
			);
			$hr_id = $wpdb->insert_id;

			$wpdb->insert(
				$departments_table,
				array(
					'name'        => 'Information Technology',
					'code'        => 'IT',
					'description' => 'Software engineering, systems and technical infrastructure.',
				),
				array( '%s', '%s', '%s' )
			);
			$it_id = $wpdb->insert_id;

			$wpdb->insert(
				$departments_table,
				array(
					'name'        => 'Finance & Accounting',
					'code'        => 'FIN',
					'description' => 'Financial planning, accounting and payroll administration.',
				),
				array( '%s', '%s', '%s' )
			);
			$fin_id = $wpdb->insert_id;

			$wpdb->insert(
				$departments_table,
				array(
					'name'        => 'Sales & Marketing',
					'code'        => 'SALES',
					'description' => 'Business development, client relations and marketing.',
				),
				array( '%s', '%s', '%s' )
			);
			$sales_id = $wpdb->insert_id;

			// Seed Core Positions
			$positions_table = self::positions_table();

			$default_positions = array(
				array( 'department_id' => $hr_id, 'title' => 'HR Director', 'code' => 'HR-DIR' ),
				array( 'department_id' => $hr_id, 'title' => 'HR Specialist', 'code' => 'HR-SPEC' ),
				array( 'department_id' => $it_id, 'title' => 'Senior Software Engineer', 'code' => 'IT-SWE' ),
				array( 'department_id' => $it_id, 'title' => 'DevOps Specialist', 'code' => 'IT-DEVOPS' ),
				array( 'department_id' => $fin_id, 'title' => 'Financial Analyst', 'code' => 'FIN-ANALYST' ),
				array( 'department_id' => $fin_id, 'title' => 'Accountant', 'code' => 'FIN-ACC' ),
				array( 'department_id' => $sales_id, 'title' => 'Sales Executive', 'code' => 'SALES-EXEC' ),
			);

			foreach ( $default_positions as $pos ) {
				$wpdb->insert(
					$positions_table,
					array(
						'department_id' => $pos['department_id'],
						'title'         => $pos['title'],
						'code'          => $pos['code'],
						'description'   => $pos['title'] . ' role in company.',
					),
					array( '%d', '%s', '%s', '%s' )
				);
			}
		}
	}

	/**
	 * Architectural Schema Registry for Future Modules.
	 * Returns blueprint schema metadata for Phase 2-6 tables.
	 *
	 * @return array
	 */
	public static function get_future_modules_schema_registry() {
		return array(
			'attendance' => array(
				'table'       => 'nds_hr_attendance',
				'phase'       => 2,
				'description' => 'Daily check-in / check-out records, status (present, late, absent), duration, geolocation.',
			),
			'leave_types' => array(
				'table'       => 'nds_hr_leave_types',
				'phase'       => 3,
				'description' => 'Annual, Sick, Maternity, Emergency leaves configuration, carry-over rules, allowances.',
			),
			'leave_balances' => array(
				'table'       => 'nds_hr_leave_balances',
				'phase'       => 3,
				'description' => 'Yearly allocated, taken, pending, and remaining balances per employee.',
			),
			'leave_requests' => array(
				'table'       => 'nds_hr_leave_requests',
				'phase'       => 3,
				'description' => 'Employee leave requests, date ranges, reasons, approval workflow status, approver ID.',
			),
			'payroll' => array(
				'table'       => 'nds_hr_payroll',
				'phase'       => 4,
				'description' => 'Monthly payroll runs, cycle period, payment date, totals, status (draft, processed, paid).',
			),
			'payroll_items' => array(
				'table'       => 'nds_hr_payroll_items',
				'phase'       => 4,
				'description' => 'Individual line items per employee: allowances, deductions, overtime, bonuses, tax.',
			),
			'payslips' => array(
				'table'       => 'nds_hr_payslips',
				'phase'       => 5,
				'description' => 'Generated payslip documents, unique reference number, net pay summary, distribution status.',
			),
			'notifications' => array(
				'table'       => 'nds_hr_notifications',
				'phase'       => 6,
				'description' => 'In-app and email alert queue for leaves, onboarding, approvals, and payroll.',
			),
		);
	}
}
