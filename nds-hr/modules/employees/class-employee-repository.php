<?php
/**
 * Employee Data Access Repository.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Employee_Repository
 */
class NDS_HR_Employee_Repository {

	/**
	 * Find an employee by internal primary database ID.
	 *
	 * @param int $id
	 * @return object|null
	 */
	public function find_by_id( $id ) {
		global $wpdb;
		$table = NDS_HR_Database::employees_table();
		$dept_table = NDS_HR_Database::departments_table();
		$pos_table  = NDS_HR_Database::positions_table();

		$sql = $wpdb->prepare(
			"SELECT e.*, d.name AS department_name, p.title AS position_title
			FROM {$table} e
			LEFT JOIN {$dept_table} d ON e.department_id = d.id
			LEFT JOIN {$pos_table} p ON e.position_id = p.id
			WHERE e.id = %d LIMIT 1",
			absint( $id )
		);

		return $wpdb->get_row( $sql );
	}

	/**
	 * Find an employee by public Employee ID (e.g. NDS-00001).
	 *
	 * @param string $employee_id
	 * @return object|null
	 */
	public function find_by_employee_id( $employee_id ) {
		global $wpdb;
		$table = NDS_HR_Database::employees_table();

		$sql = $wpdb->prepare(
			"SELECT * FROM {$table} WHERE employee_id = %s LIMIT 1",
			sanitize_text_field( $employee_id )
		);

		return $wpdb->get_row( $sql );
	}

	/**
	 * Find an employee by linked NDS HR User ID (with legacy fallback).
	 *
	 * @param int $hr_user_id
	 * @return object|null
	 */
	public function find_by_hr_user_id( $hr_user_id ) {
		global $wpdb;
		$table      = NDS_HR_Database::employees_table();
		$dept_table = NDS_HR_Database::departments_table();
		$pos_table  = NDS_HR_Database::positions_table();

		$sql = $wpdb->prepare(
			"SELECT e.*, d.name AS department_name, p.title AS position_title
			FROM {$table} e
			LEFT JOIN {$dept_table} d ON e.department_id = d.id
			LEFT JOIN {$pos_table} p ON e.position_id = p.id
			WHERE e.hr_user_id = %d LIMIT 1",
			absint( $hr_user_id )
		);

		$emp = $wpdb->get_row( $sql );
		if ( $emp ) {
			return $emp;
		}

		// Non-destructive fallback: check legacy user_id
		return $this->find_by_user_id( $hr_user_id );
	}

	/**
	 * Find an employee by linked WordPress User ID.
	 *
	 * @param int $user_id
	 * @return object|null
	 */
	public function find_by_user_id( $user_id ) {
		global $wpdb;
		$table = NDS_HR_Database::employees_table();
		$dept_table = NDS_HR_Database::departments_table();
		$pos_table  = NDS_HR_Database::positions_table();

		$sql = $wpdb->prepare(
			"SELECT e.*, d.name AS department_name, p.title AS position_title
			FROM {$table} e
			LEFT JOIN {$dept_table} d ON e.department_id = d.id
			LEFT JOIN {$pos_table} p ON e.position_id = p.id
			WHERE (e.hr_user_id = %d OR e.user_id = %d) LIMIT 1",
			absint( $user_id ),
			absint( $user_id )
		);

		return $wpdb->get_row( $sql );
	}

	/**
	 * Get paginated list of employees with search and filters.
	 *
	 * @param array $args
	 * @return array
	 */
	public function get_all( array $args = array() ) {
		global $wpdb;

		$table      = NDS_HR_Database::employees_table();
		$dept_table = NDS_HR_Database::departments_table();
		$pos_table  = NDS_HR_Database::positions_table();

		$defaults = array(
			'search'            => '',
			'department_id'     => 0,
			'position_id'       => 0,
			'employment_status' => '',
			'page'              => 1,
			'per_page'          => 10,
			'orderby'           => 'e.id',
			'order'             => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$where  = array( '1=1' );
		$params = array();

		// Search across multiple fields
		if ( ! empty( $args['search'] ) ) {
			$search_term = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where[]     = '(e.full_name LIKE %s OR e.email LIKE %s OR e.employee_id LIKE %s OR e.phone LIKE %s OR e.national_id LIKE %s)';
			$params[]    = $search_term;
			$params[]    = $search_term;
			$params[]    = $search_term;
			$params[]    = $search_term;
			$params[]    = $search_term;
		}

		// Filter by Department
		if ( ! empty( $args['department_id'] ) ) {
			$where[]  = 'e.department_id = %d';
			$params[] = absint( $args['department_id'] );
		}

		// Filter by Position
		if ( ! empty( $args['position_id'] ) ) {
			$where[]  = 'e.position_id = %d';
			$params[] = absint( $args['position_id'] );
		}

		// Filter by Employment Status
		if ( ! empty( $args['employment_status'] ) ) {
			$where[]  = 'e.employment_status = %s';
			$params[] = sanitize_text_field( $args['employment_status'] );
		}

		$where_clause = implode( ' AND ', $where );

		// Pagination calculation
		$page     = max( 1, absint( $args['page'] ) );
		$per_page = max( 1, min( 100, absint( $args['per_page'] ) ) );
		$offset   = ( $page - 1 ) * $per_page;

		$order = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		$allowed_orderby = array( 'e.id', 'e.employee_id', 'e.full_name', 'e.hire_date', 'e.employment_status' );
		$orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'e.id';

		$sql = "SELECT e.*, d.name AS department_name, p.title AS position_title
				FROM {$table} e
				LEFT JOIN {$dept_table} d ON e.department_id = d.id
				LEFT JOIN {$pos_table} p ON e.position_id = p.id
				WHERE {$where_clause}
				ORDER BY {$orderby} {$order}
				LIMIT {$offset}, {$per_page}";

		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $params );
		}

		return $wpdb->get_results( $sql );
	}

	/**
	 * Count total employees matching search/filter criteria.
	 *
	 * @param array $args
	 * @return int
	 */
	public function count( array $args = array() ) {
		global $wpdb;

		$table = NDS_HR_Database::employees_table();

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $args['search'] ) ) {
			$search_term = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where[]     = '(full_name LIKE %s OR email LIKE %s OR employee_id LIKE %s OR phone LIKE %s OR national_id LIKE %s)';
			$params[]    = $search_term;
			$params[]    = $search_term;
			$params[]    = $search_term;
			$params[]    = $search_term;
			$params[]    = $search_term;
		}

		if ( ! empty( $args['department_id'] ) ) {
			$where[]  = 'department_id = %d';
			$params[] = absint( $args['department_id'] );
		}

		if ( ! empty( $args['employment_status'] ) ) {
			$where[]  = 'employment_status = %s';
			$params[] = sanitize_text_field( $args['employment_status'] );
		}

		$where_clause = implode( ' AND ', $where );
		$sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_clause}";

		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $params );
		}

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Generate an atomic, unique Employee ID in format NDS-00001.
	 *
	 * @return string
	 */
	public function generate_unique_employee_id() {
		global $wpdb;

		$table = NDS_HR_Database::employees_table();

		// Counter option fallback
		$current_num = (int) get_option( 'nds_hr_last_employee_number', 0 );

		// Also check existing max number in database to prevent desync
		$max_in_db = $wpdb->get_var( "SELECT MAX(id) FROM {$table}" );
		$next_num  = max( $current_num, (int) $max_in_db ) + 1;

		$candidate_id = sprintf( 'NDS-%05d', $next_num );

		// Verify uniqueness loop
		while ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE employee_id = %s LIMIT 1", $candidate_id ) ) ) {
			$next_num++;
			$candidate_id = sprintf( 'NDS-%05d', $next_num );
		}

		update_option( 'nds_hr_last_employee_number', $next_num );

		return $candidate_id;
	}

	/**
	 * Insert a new employee record.
	 *
	 * @param array $data
	 * @return int|false
	 */
	public function insert( array $data ) {
		global $wpdb;

		$table = NDS_HR_Database::employees_table();

		if ( empty( $data['employee_id'] ) ) {
			$data['employee_id'] = $this->generate_unique_employee_id();
		}

		$data['created_at'] = current_time( 'mysql' );
		$data['updated_at'] = current_time( 'mysql' );

		$result = $wpdb->insert( $table, $data );

		return false !== $result ? $wpdb->insert_id : false;
	}

	/**
	 * Update an existing employee record.
	 *
	 * @param int   $id
	 * @param array $data
	 * @return bool
	 */
	public function update( $id, array $data ) {
		global $wpdb;

		$table = NDS_HR_Database::employees_table();
		$data['updated_at'] = current_time( 'mysql' );

		$result = $wpdb->update(
			$table,
			$data,
			array( 'id' => absint( $id ) )
		);

		return false !== $result;
	}

	/**
	 * Delete an employee record.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete( $id ) {
		global $wpdb;
		$table = NDS_HR_Database::employees_table();

		return false !== $wpdb->delete( $table, array( 'id' => absint( $id ) ) );
	}

	/**
	 * Get list of all departments.
	 *
	 * @return array
	 */
	public function get_departments() {
		global $wpdb;
		$table = NDS_HR_Database::departments_table();
		return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY name ASC" );
	}

	/**
	 * Get list of positions, optionally filtered by department.
	 *
	 * @param int $department_id
	 * @return array
	 */
	public function get_positions( $department_id = 0 ) {
		global $wpdb;
		$table = NDS_HR_Database::positions_table();

		if ( ! empty( $department_id ) ) {
			return $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM {$table} WHERE department_id = %d OR department_id IS NULL ORDER BY title ASC", absint( $department_id ) )
			);
		}

		return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY title ASC" );
	}

	/**
	 * Get counts grouped by status for dashboard cards.
	 *
	 * @return array
	 */
	public function get_status_counts() {
		global $wpdb;
		$table = NDS_HR_Database::employees_table();

		$counts = array(
			'total'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ),
			'active'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE employment_status = 'active'" ),
			'inactive'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE employment_status = 'inactive'" ),
			'terminated'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE employment_status = 'terminated'" ),
			'departments' => (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . NDS_HR_Database::departments_table() ),
		);

		return $counts;
	}
}
