<?php
/**
 * Security and Data Sanitization Helper.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Security
 */
class NDS_HR_Security {

	/**
	 * Default action prefix for nonces.
	 */
	const NONCE_ACTION = 'nds_hr_security_action';
	const NONCE_NAME   = '_nds_hr_nonce';

	/**
	 * Generate a security nonce field for HTML forms.
	 *
	 * @param string $action Specific action name.
	 * @return string HTML input element.
	 */
	public static function nonce_field( $action = self::NONCE_ACTION ) {
		return wp_nonce_field( $action, self::NONCE_NAME, true, false );
	}

	/**
	 * Verify a security nonce from request.
	 *
	 * @param string $action Specific action name.
	 * @param string $query_arg Query argument holding the nonce.
	 * @return bool
	 */
	public static function verify_nonce( $action = self::NONCE_ACTION, $query_arg = self::NONCE_NAME ) {
		$nonce = isset( $_REQUEST[ $query_arg ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $query_arg ] ) ) : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $action ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Verify admin action with die on failure.
	 *
	 * @param string $action
	 */
	public static function check_admin_referer_or_die( $action = self::NONCE_ACTION ) {
		if ( ! self::verify_nonce( $action ) ) {
			wp_die(
				esc_html__( 'Security check failed. Please refresh the page and try again.', 'nds-hr' ),
				esc_html__( 'Security Error', 'nds-hr' ),
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * Deeply sanitize employee form input payload.
	 *
	 * @param array $raw_input
	 * @return array
	 */
	public static function sanitize_employee_input( array $raw_input ) {
		$clean = array();

		// Basic Names
		$clean['first_name'] = isset( $raw_input['first_name'] ) ? sanitize_text_field( wp_unslash( $raw_input['first_name'] ) ) : '';
		$clean['last_name']  = isset( $raw_input['last_name'] ) ? sanitize_text_field( wp_unslash( $raw_input['last_name'] ) ) : '';
		$clean['full_name']  = trim( $clean['first_name'] . ' ' . $clean['last_name'] );

		// Contact Info
		$clean['email']  = isset( $raw_input['email'] ) ? sanitize_email( wp_unslash( $raw_input['email'] ) ) : '';
		$clean['phone']  = isset( $raw_input['phone'] ) ? sanitize_text_field( wp_unslash( $raw_input['phone'] ) ) : '';
		$clean['mobile'] = isset( $raw_input['mobile'] ) ? sanitize_text_field( wp_unslash( $raw_input['mobile'] ) ) : '';

		// Identification & Bio
		$clean['national_id'] = isset( $raw_input['national_id'] ) ? sanitize_text_field( wp_unslash( $raw_input['national_id'] ) ) : '';
		
		$clean['date_of_birth'] = '';
		if ( ! empty( $raw_input['date_of_birth'] ) ) {
			$dob = sanitize_text_field( wp_unslash( $raw_input['date_of_birth'] ) );
			// Validate standard Y-m-d format
			$d = DateTime::createFromFormat( 'Y-m-d', $dob );
			if ( $d && $d->format( 'Y-m-d' ) === $dob ) {
				$clean['date_of_birth'] = $dob;
			}
		}

		$gender = isset( $raw_input['gender'] ) ? sanitize_text_field( wp_unslash( $raw_input['gender'] ) ) : 'male';
		$clean['gender'] = in_array( $gender, array( 'male', 'female', 'other' ), true ) ? $gender : 'male';

		// Employment Details
		$clean['hire_date'] = current_time( 'Y-m-d' );
		if ( ! empty( $raw_input['hire_date'] ) ) {
			$hd = sanitize_text_field( wp_unslash( $raw_input['hire_date'] ) );
			$d = DateTime::createFromFormat( 'Y-m-d', $hd );
			if ( $d && $d->format( 'Y-m-d' ) === $hd ) {
				$clean['hire_date'] = $hd;
			}
		}

		$clean['department_id'] = isset( $raw_input['department_id'] ) ? absint( $raw_input['department_id'] ) : 0;
		$clean['position_id']   = isset( $raw_input['position_id'] ) ? absint( $raw_input['position_id'] ) : 0;
		$clean['manager_id']    = isset( $raw_input['manager_id'] ) ? absint( $raw_input['manager_id'] ) : 0;

		$status = isset( $raw_input['employment_status'] ) ? sanitize_text_field( wp_unslash( $raw_input['employment_status'] ) ) : 'active';
		$clean['employment_status'] = in_array( $status, array( 'active', 'inactive', 'terminated', 'suspended' ), true ) ? $status : 'active';

		$clean['basic_salary'] = isset( $raw_input['basic_salary'] ) ? floatval( $raw_input['basic_salary'] ) : 0.00;
		if ( $clean['basic_salary'] < 0 ) {
			$clean['basic_salary'] = 0.00;
		}

		$clean['profile_photo_url'] = isset( $raw_input['profile_photo_url'] ) ? esc_url_raw( wp_unslash( $raw_input['profile_photo_url'] ) ) : '';
		$clean['address']           = isset( $raw_input['address'] ) ? sanitize_textarea_field( wp_unslash( $raw_input['address'] ) ) : '';

		// Emergency Contact
		$clean['emergency_contact_name']         = isset( $raw_input['emergency_contact_name'] ) ? sanitize_text_field( wp_unslash( $raw_input['emergency_contact_name'] ) ) : '';
		$clean['emergency_contact_phone']        = isset( $raw_input['emergency_contact_phone'] ) ? sanitize_text_field( wp_unslash( $raw_input['emergency_contact_phone'] ) ) : '';
		$clean['emergency_contact_relationship'] = isset( $raw_input['emergency_contact_relationship'] ) ? sanitize_text_field( wp_unslash( $raw_input['emergency_contact_relationship'] ) ) : '';

		// Linked WP User ID
		$clean['user_id'] = isset( $raw_input['user_id'] ) ? absint( $raw_input['user_id'] ) : 0;

		return $clean;
	}

	/**
	 * Client IP detection with sanitized proxy fallback.
	 *
	 * @return string
	 */
	public static function get_client_ip() {
		$ip = '';
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$ip = trim( $ips[0] );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '127.0.0.1';
	}
}
