<?php
/**
 * Audit Logging Service.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Audit_Logger
 */
class NDS_HR_Audit_Logger {

	/**
	 * Log an administrative or employee action.
	 *
	 * @param string $action      E.g., 'employee_created', 'employee_deactivated'.
	 * @param string $entity_type E.g., 'employee', 'department'.
	 * @param int    $entity_id   Database ID of the affected entity.
	 * @param array  $old_values  Previous state snapshot.
	 * @param array  $new_values  Updated state snapshot.
	 * @param int    $user_id     Actor WP User ID (defaults to current user).
	 * @return int|false Inserted log ID on success, false on failure.
	 */
	public static function log( $action, $entity_type, $entity_id, array $old_values = array(), array $new_values = array(), $user_id = 0 ) {
		global $wpdb;

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$table = NDS_HR_Database::audit_logs_table();

		$data = array(
			'user_id'     => absint( $user_id ),
			'action'      => sanitize_text_field( $action ),
			'entity_type' => sanitize_text_field( $entity_type ),
			'entity_id'   => absint( $entity_id ),
			'old_values'  => ! empty( $old_values ) ? wp_json_encode( $old_values ) : null,
			'new_values'  => ! empty( $new_values ) ? wp_json_encode( $new_values ) : null,
			'ip_address'  => NDS_HR_Security::get_client_ip(),
			'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			'created_at'  => current_time( 'mysql' ),
		);

		$format = array( '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' );

		$result = $wpdb->insert( $table, $data, $format );

		return false !== $result ? $wpdb->insert_id : false;
	}

	/**
	 * Retrieve audit logs with filtering and pagination.
	 *
	 * @param array $args
	 * @return array
	 */
	public static function get_logs( array $args = array() ) {
		global $wpdb;

		$table = NDS_HR_Database::audit_logs_table();

		$defaults = array(
			'per_page'    => 20,
			'page'        => 1,
			'entity_type' => '',
			'entity_id'   => 0,
			'action'      => '',
			'user_id'     => 0,
			'orderby'     => 'id',
			'order'       => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$where = array( '1=1' );
		$params = array();

		if ( ! empty( $args['entity_type'] ) ) {
			$where[]  = 'entity_type = %s';
			$params[] = sanitize_text_field( $args['entity_type'] );
		}

		if ( ! empty( $args['entity_id'] ) ) {
			$where[]  = 'entity_id = %d';
			$params[] = absint( $args['entity_id'] );
		}

		if ( ! empty( $args['action'] ) ) {
			$where[]  = 'action = %s';
			$params[] = sanitize_text_field( $args['action'] );
		}

		if ( ! empty( $args['user_id'] ) ) {
			$where[]  = 'user_id = %d';
			$params[] = absint( $args['user_id'] );
		}

		$where_clause = implode( ' AND ', $where );

		$offset = ( absint( $args['page'] ) - 1 ) * absint( $args['per_page'] );
		$limit  = absint( $args['per_page'] );

		$order = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		$sql = "SELECT * FROM {$table} WHERE {$where_clause} ORDER BY id {$order} LIMIT {$offset}, {$limit}";

		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $params );
		}

		return $wpdb->get_results( $sql );
	}

	/**
	 * Count total audit logs matching criteria.
	 *
	 * @param array $args
	 * @return int
	 */
	public static function count_logs( array $args = array() ) {
		global $wpdb;

		$table = NDS_HR_Database::audit_logs_table();

		$where = array( '1=1' );
		$params = array();

		if ( ! empty( $args['entity_type'] ) ) {
			$where[]  = 'entity_type = %s';
			$params[] = sanitize_text_field( $args['entity_type'] );
		}

		if ( ! empty( $args['entity_id'] ) ) {
			$where[]  = 'entity_id = %d';
			$params[] = absint( $args['entity_id'] );
		}

		$where_clause = implode( ' AND ', $where );
		$sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_clause}";

		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $params );
		}

		return (int) $wpdb->get_var( $sql );
	}
}
