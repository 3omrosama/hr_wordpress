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
	 * Parse date in DD/MM/YYYY, DD-MM-YYYY, or YYYY-MM-DD format into MySQL YYYY-MM-DD format.
	 *
	 * @param string $raw_date
	 * @return string|null Formatted date string or null if empty/invalid.
	 */
	public static function parse_date_to_sql( $raw_date ) {
		if ( empty( $raw_date ) ) {
			return null;
		}

		$cleaned = trim( sanitize_text_field( $raw_date ) );
		if ( empty( $cleaned ) ) {
			return null;
		}

		// 1. Check DD/MM/YYYY or DD-MM-YYYY
		if ( preg_match( '/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $cleaned, $matches ) ) {
			$day   = (int) $matches[1];
			$month = (int) $matches[2];
			$year  = (int) $matches[3];

			if ( checkdate( $month, $day, $year ) ) {
				return sprintf( '%04d-%02d-%02d', $year, $month, $day );
			}
		}

		// 2. Check YYYY-MM-DD
		if ( preg_match( '/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $cleaned, $matches ) ) {
			$year  = (int) $matches[1];
			$month = (int) $matches[2];
			$day   = (int) $matches[3];

			if ( checkdate( $month, $day, $year ) ) {
				return sprintf( '%04d-%02d-%02d', $year, $month, $day );
			}
		}

		// 3. Fallback DateTime parsing
		try {
			$dt = new DateTime( $cleaned );
			return $dt->format( 'Y-m-d' );
		} catch ( Exception $e ) {
			return null;
		}
	}

	/**
	 * Format SQL date (YYYY-MM-DD) for user-facing display in DD/MM/YYYY format.
	 *
	 * @param string $sql_date
	 * @return string
	 */
	public static function format_date_for_display( $sql_date ) {
		if ( empty( $sql_date ) || '0000-00-00' === $sql_date ) {
			return '';
		}

		$parts = explode( '-', $sql_date );
		if ( 3 === count( $parts ) && checkdate( (int) $parts[1], (int) $parts[2], (int) $parts[0] ) ) {
			return sprintf( '%02d/%02d/%04d', (int) $parts[2], (int) $parts[1], (int) $parts[0] );
		}

		$time = strtotime( $sql_date );
		return $time ? date( 'd/m/Y', $time ) : '';
	}

	/**
	 * Validate and normalize personal mobile number for Egypt (+20) and Saudi Arabia (+966).
	 *
	 * @param string $country_code '+20' or '+966'.
	 * @param string $raw_number   Input string.
	 * @return array|WP_Error Array with normalized components or WP_Error on invalid input.
	 */
	public static function validate_and_normalize_phone( $country_code, $raw_number ) {
		$raw_number   = trim( (string) $raw_number );
		$country_code = in_array( $country_code, array( '+20', '+966' ), true ) ? $country_code : '+20';

		// Optional field: empty is valid
		if ( empty( $raw_number ) ) {
			return array(
				'is_valid'      => true,
				'country_code'  => $country_code,
				'local_number'  => '',
				'international' => '',
			);
		}

		// Extract only digits
		$digits = preg_replace( '/\D/', '', $raw_number );

		if ( '+20' === $country_code ) {
			// Egypt: Strip leading country code 20 or 0020 if present
			if ( 0 === strpos( $digits, '0020' ) ) {
				$digits = substr( $digits, 4 );
			} elseif ( 0 === strpos( $digits, '20' ) && strlen( $digits ) >= 12 ) {
				$digits = substr( $digits, 2 );
			}

			// Strip single leading zero (e.g. 01012345678 -> 1012345678)
			if ( 0 === strpos( $digits, '0' ) && strlen( $digits ) === 11 ) {
				$digits = substr( $digits, 1 );
			}

			// Egypt mobile format: exactly 10 digits starting with 10, 11, 12, or 15
			if ( ! preg_match( '/^1[0125]\d{8}$/', $digits ) ) {
				return new WP_Error(
					'invalid_egypt_phone',
					__( 'Invalid Egyptian mobile number. Must be 10 digits starting with 10, 11, 12, or 15 (e.g., 010 1234 5678).', 'nds-hr' )
				);
			}

			return array(
				'is_valid'      => true,
				'country_code'  => '+20',
				'local_number'  => $digits,
				'international' => '+20 ' . substr( $digits, 0, 2 ) . ' ' . substr( $digits, 2, 4 ) . ' ' . substr( $digits, 6 ),
			);
		} elseif ( '+966' === $country_code ) {
			// Saudi Arabia: Strip leading country code 966 or 00966 if present
			if ( 0 === strpos( $digits, '00966' ) ) {
				$digits = substr( $digits, 5 );
			} elseif ( 0 === strpos( $digits, '966' ) && strlen( $digits ) >= 11 ) {
				$digits = substr( $digits, 3 );
			}

			// Strip single leading zero (e.g. 0501234567 -> 501234567)
			if ( 0 === strpos( $digits, '0' ) && strlen( $digits ) === 10 ) {
				$digits = substr( $digits, 1 );
			}

			// Saudi mobile format: exactly 9 digits starting with 5
			if ( ! preg_match( '/^5\d{8}$/', $digits ) ) {
				return new WP_Error(
					'invalid_saudi_phone',
					__( 'Invalid Saudi mobile number. Must be 9 digits starting with 5 (e.g., 050 123 4567).', 'nds-hr' )
				);
			}

			return array(
				'is_valid'      => true,
				'country_code'  => '+966',
				'local_number'  => $digits,
				'international' => '+966 ' . substr( $digits, 0, 2 ) . ' ' . substr( $digits, 2, 3 ) . ' ' . substr( $digits, 5 ),
			);
		}

		return new WP_Error( 'unsupported_country', __( 'Selected country code is not supported.', 'nds-hr' ) );
	}

	/**
	 * Compute human-readable contract duration.
	 *
	 * @param string|null $start_date SQL start date YYYY-MM-DD.
	 * @param string|null $end_date   SQL end date YYYY-MM-DD.
	 * @return string
	 */
	public static function calculate_contract_duration( $start_date, $end_date ) {
		if ( empty( $start_date ) ) {
			return __( 'Not specified', 'nds-hr' );
		}

		if ( empty( $end_date ) ) {
			return __( 'Open-ended / Indefinite contract', 'nds-hr' );
		}

		try {
			$d1 = new DateTime( $start_date );
			$d2 = new DateTime( $end_date );

			if ( $d2 < $d1 ) {
				return __( 'End date precedes start date', 'nds-hr' );
			}

			$diff = $d1->diff( $d2 );
			$parts = array();

			if ( $diff->y > 0 ) {
				$parts[] = sprintf( _n( '%d Year', '%d Years', $diff->y, 'nds-hr' ), $diff->y );
			}
			if ( $diff->m > 0 ) {
				$parts[] = sprintf( _n( '%d Month', '%d Months', $diff->m, 'nds-hr' ), $diff->m );
			}
			if ( $diff->d > 0 && empty( $parts ) ) {
				$parts[] = sprintf( _n( '%d Day', '%d Days', $diff->d, 'nds-hr' ), $diff->d );
			}

			return ! empty( $parts ) ? implode( ', ', $parts ) : __( '1 Day', 'nds-hr' );
		} catch ( Exception $e ) {
			return __( 'Open-ended / Indefinite', 'nds-hr' );
		}
	}

	/**
	 * Handle secure profile photo file upload (JPG/PNG only, max 5MB, strict MIME check).
	 *
	 * @param array  $file_entry        $_FILES['profile_photo'].
	 * @param string $existing_photo_url Current stored URL if replacing.
	 * @return string|WP_Error URL string on success, or WP_Error.
	 */
	public static function handle_profile_photo_upload( $file_entry, $existing_photo_url = '' ) {
		if ( empty( $file_entry ) || ! isset( $file_entry['name'] ) || empty( $file_entry['name'] ) ) {
			return $existing_photo_url;
		}

		if ( ! isset( $file_entry['error'] ) || UPLOAD_ERR_OK !== $file_entry['error'] ) {
			if ( isset( $file_entry['error'] ) && UPLOAD_ERR_NO_FILE === $file_entry['error'] ) {
				return $existing_photo_url;
			}
			return new WP_Error( 'upload_error', __( 'File upload encountered an error. Please try again.', 'nds-hr' ) );
		}

		// Maximum size: 5 MB
		$max_size = 5 * 1024 * 1024;
		if ( $file_entry['size'] > $max_size ) {
			return new WP_Error( 'file_too_large', __( 'Profile photo exceeds maximum permitted size (5 MB).', 'nds-hr' ) );
		}

		$tmp_path = $file_entry['tmp_name'];
		if ( ! is_uploaded_file( $tmp_path ) ) {
			return new WP_Error( 'invalid_upload', __( 'Invalid file upload source.', 'nds-hr' ) );
		}

		// Server-side Image inspection using getimagesize()
		$image_info = @getimagesize( $tmp_path );
		if ( false === $image_info ) {
			return new WP_Error( 'not_an_image', __( 'The uploaded file is not a valid image.', 'nds-hr' ) );
		}

		$allowed_mime_types = array(
			'image/jpeg' => 'jpg',
			'image/jpg'  => 'jpg',
			'image/pjpeg'=> 'jpg',
			'image/png'  => 'png',
		);

		$detected_mime = $image_info['mime'];
		if ( ! isset( $allowed_mime_types[ $detected_mime ] ) ) {
			return new WP_Error( 'invalid_image_type', __( 'Only JPG, JPEG, and PNG images are permitted.', 'nds-hr' ) );
		}

		$extension = $allowed_mime_types[ $detected_mime ];

		// Verify file content does not contain PHP opening tags
		$content_peek = @file_get_contents( $tmp_path, false, null, 0, 2048 );
		if ( false !== $content_peek && preg_match( '/<\?php|<\?=|style\s*=.*expression/i', $content_peek ) ) {
			return new WP_Error( 'security_violation', __( 'File failed security validation.', 'nds-hr' ) );
		}

		$upload_dir = wp_upload_dir();
		$target_dir = $upload_dir['basedir'] . '/nds-hr/photos';
		$target_url = $upload_dir['baseurl'] . '/nds-hr/photos';

		if ( ! file_exists( $target_dir ) ) {
			wp_mkdir_p( $target_dir );
			// Put silent index.php
			@file_put_contents( $target_dir . '/index.php', '<?php // Silence is golden.' );
		}

		try {
			$random_suffix = bin2hex( random_bytes( 12 ) );
		} catch ( Exception $e ) {
			$random_suffix = wp_generate_password( 24, false, false );
		}

		$safe_filename = 'photo_' . time() . '_' . $random_suffix . '.' . $extension;
		$destination   = $target_dir . '/' . $safe_filename;

		if ( ! move_uploaded_file( $tmp_path, $destination ) ) {
			return new WP_Error( 'move_failed', __( 'Could not save profile photo to storage directory.', 'nds-hr' ) );
		}

		return $target_url . '/' . $safe_filename;
	}

	/**
	 * Handle secure contract document file upload (PDF, DOC, DOCX, max 10MB).
	 *
	 * @param array  $file_entry       $_FILES['contract_document'].
	 * @param string $existing_doc_url Current stored URL if replacing.
	 * @param string $existing_doc_name Current stored filename.
	 * @return array|WP_Error Array with 'url' and 'name', or WP_Error.
	 */
	public static function handle_contract_document_upload( $file_entry, $existing_doc_url = '', $existing_doc_name = '' ) {
		if ( empty( $file_entry ) || ! isset( $file_entry['name'] ) || empty( $file_entry['name'] ) ) {
			return array(
				'url'  => $existing_doc_url,
				'name' => $existing_doc_name,
			);
		}

		if ( ! isset( $file_entry['error'] ) || UPLOAD_ERR_OK !== $file_entry['error'] ) {
			if ( isset( $file_entry['error'] ) && UPLOAD_ERR_NO_FILE === $file_entry['error'] ) {
				return array(
					'url'  => $existing_doc_url,
					'name' => $existing_doc_name,
				);
			}
			return new WP_Error( 'upload_error', __( 'Contract document upload failed. Please try again.', 'nds-hr' ) );
		}

		// Maximum size: 10 MB
		$max_size = 10 * 1024 * 1024;
		if ( $file_entry['size'] > $max_size ) {
			return new WP_Error( 'file_too_large', __( 'Contract document exceeds maximum permitted size (10 MB).', 'nds-hr' ) );
		}

		$tmp_path      = $file_entry['tmp_name'];
		$original_name = sanitize_file_name( $file_entry['name'] );
		$ext           = strtolower( pathinfo( $original_name, PATHINFO_EXTENSION ) );

		$allowed_exts = array( 'pdf', 'doc', 'docx' );
		if ( ! in_array( $ext, $allowed_exts, true ) ) {
			return new WP_Error( 'invalid_doc_type', __( 'Only PDF, DOC, and DOCX files are allowed for contracts.', 'nds-hr' ) );
		}

		if ( ! is_uploaded_file( $tmp_path ) ) {
			return new WP_Error( 'invalid_upload', __( 'Invalid file upload source.', 'nds-hr' ) );
		}

		// Check magic bytes for PDF and Office files
		$handle = fopen( $tmp_path, 'rb' );
		$header = $handle ? fread( $handle, 8 ) : '';
		if ( $handle ) {
			fclose( $handle );
		}

		if ( 'pdf' === $ext && 0 !== strpos( $header, '%PDF' ) ) {
			return new WP_Error( 'invalid_pdf', __( 'Corrupted or invalid PDF document.', 'nds-hr' ) );
		}

		$upload_dir = wp_upload_dir();
		$target_dir = $upload_dir['basedir'] . '/nds-hr/contracts';
		$target_url = $upload_dir['baseurl'] . '/nds-hr/contracts';

		if ( ! file_exists( $target_dir ) ) {
			wp_mkdir_p( $target_dir );
			@file_put_contents( $target_dir . '/index.php', '<?php // Silence is golden.' );
			// Write htaccess to prevent PHP script execution
			@file_put_contents( $target_dir . '/.htaccess', "<Files *.php>\nDeny from all\n</Files>" );
		}

		try {
			$random_suffix = bin2hex( random_bytes( 12 ) );
		} catch ( Exception $e ) {
			$random_suffix = wp_generate_password( 24, false, false );
		}

		$safe_filename = 'contract_' . time() . '_' . $random_suffix . '.' . $ext;
		$destination   = $target_dir . '/' . $safe_filename;

		if ( ! move_uploaded_file( $tmp_path, $destination ) ) {
			return new WP_Error( 'move_failed', __( 'Could not save contract document to storage directory.', 'nds-hr' ) );
		}

		return array(
			'url'  => $target_url . '/' . $safe_filename,
			'name' => $original_name,
		);
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
		$clean['first_name']   = isset( $raw_input['first_name'] ) ? sanitize_text_field( wp_unslash( $raw_input['first_name'] ) ) : '';
		$clean['last_name']    = isset( $raw_input['last_name'] ) ? sanitize_text_field( wp_unslash( $raw_input['last_name'] ) ) : '';
		$clean['display_name'] = isset( $raw_input['display_name'] ) ? sanitize_text_field( wp_unslash( $raw_input['display_name'] ) ) : '';
		if ( empty( $clean['display_name'] ) ) {
			$clean['display_name'] = trim( $clean['first_name'] . ' ' . $clean['last_name'] );
		}
		$clean['full_name'] = $clean['display_name'];

		// Contact Info & Normalized Mobile
		$clean['email']              = isset( $raw_input['email'] ) ? sanitize_email( wp_unslash( $raw_input['email'] ) ) : '';
		$clean['phone_country_code'] = isset( $raw_input['phone_country_code'] ) && in_array( $raw_input['phone_country_code'], array( '+20', '+966' ), true ) ? $raw_input['phone_country_code'] : '+20';
		
		$raw_mobile = isset( $raw_input['mobile'] ) ? sanitize_text_field( wp_unslash( $raw_input['mobile'] ) ) : '';
		if ( empty( $raw_mobile ) && isset( $raw_input['phone'] ) ) {
			$raw_mobile = sanitize_text_field( wp_unslash( $raw_input['phone'] ) );
		}

		$phone_validation = self::validate_and_normalize_phone( $clean['phone_country_code'], $raw_mobile );
		if ( is_wp_error( $phone_validation ) ) {
			$clean['_phone_error'] = $phone_validation;
			$clean['mobile']       = $raw_mobile;
			$clean['phone']        = $raw_mobile;
		} else {
			$clean['mobile'] = $phone_validation['international'];
			$clean['phone']  = $phone_validation['international'];
		}

		// Identification & Bio
		$clean['national_id'] = isset( $raw_input['national_id'] ) ? sanitize_text_field( wp_unslash( $raw_input['national_id'] ) ) : '';
		
		// Date of Birth (Parsed from DD/MM/YYYY or YYYY-MM-DD)
		$raw_dob = isset( $raw_input['date_of_birth'] ) ? wp_unslash( $raw_input['date_of_birth'] ) : '';
		$clean['date_of_birth'] = self::parse_date_to_sql( $raw_dob );

		$gender = isset( $raw_input['gender'] ) ? sanitize_text_field( wp_unslash( $raw_input['gender'] ) ) : 'male';
		$clean['gender'] = in_array( $gender, array( 'male', 'female', 'other' ), true ) ? $gender : 'male';

		// Employment Details & Hire Date
		$raw_hire = isset( $raw_input['hire_date'] ) ? wp_unslash( $raw_input['hire_date'] ) : '';
		$clean['hire_date'] = self::parse_date_to_sql( $raw_hire ) ?: current_time( 'Y-m-d' );

		$clean['department_id'] = isset( $raw_input['department_id'] ) ? absint( $raw_input['department_id'] ) : 0;
		$clean['position_id']   = isset( $raw_input['position_id'] ) ? absint( $raw_input['position_id'] ) : 0;
		$clean['manager_id']    = isset( $raw_input['manager_id'] ) ? absint( $raw_input['manager_id'] ) : 0;

		$status = isset( $raw_input['employment_status'] ) ? sanitize_text_field( wp_unslash( $raw_input['employment_status'] ) ) : 'active';
		$clean['employment_status'] = in_array( $status, array( 'active', 'inactive', 'terminated', 'suspended' ), true ) ? $status : 'active';

		// Contract Information
		$contract_type = isset( $raw_input['contract_type'] ) ? sanitize_text_field( wp_unslash( $raw_input['contract_type'] ) ) : 'permanent';
		$valid_contract_types = array( 'permanent', 'fixed_term', 'temporary', 'probation', 'other' );
		$clean['contract_type'] = in_array( $contract_type, $valid_contract_types, true ) ? $contract_type : 'permanent';

		$raw_contract_start = isset( $raw_input['contract_start_date'] ) ? wp_unslash( $raw_input['contract_start_date'] ) : '';
		$clean['contract_start_date'] = self::parse_date_to_sql( $raw_contract_start );

		$raw_contract_end = isset( $raw_input['contract_end_date'] ) ? wp_unslash( $raw_input['contract_end_date'] ) : '';
		$clean['contract_end_date'] = self::parse_date_to_sql( $raw_contract_end );

		// Optional Basic Salary and ISO Currency Code (EGP / SAR)
		$raw_salary = isset( $raw_input['basic_salary'] ) ? trim( (string) wp_unslash( $raw_input['basic_salary'] ) ) : '';
		if ( '' === $raw_salary || ! is_numeric( str_replace( ',', '', $raw_salary ) ) ) {
			$clean['basic_salary'] = null;
		} else {
			$clean['basic_salary'] = max( 0.00, floatval( str_replace( ',', '', $raw_salary ) ) );
		}

		$salary_currency = isset( $raw_input['salary_currency'] ) ? strtoupper( sanitize_text_field( wp_unslash( $raw_input['salary_currency'] ) ) ) : 'EGP';
		$clean['salary_currency'] = in_array( $salary_currency, array( 'EGP', 'SAR' ), true ) ? $salary_currency : 'EGP';

		$clean['profile_photo_url'] = isset( $raw_input['profile_photo_url'] ) ? esc_url_raw( wp_unslash( $raw_input['profile_photo_url'] ) ) : '';
		$clean['address']           = isset( $raw_input['address'] ) ? sanitize_textarea_field( wp_unslash( $raw_input['address'] ) ) : '';

		// Emergency Contact
		$clean['emergency_contact_name']         = isset( $raw_input['emergency_contact_name'] ) ? sanitize_text_field( wp_unslash( $raw_input['emergency_contact_name'] ) ) : '';
		$clean['emergency_contact_phone']        = isset( $raw_input['emergency_contact_phone'] ) ? sanitize_text_field( wp_unslash( $raw_input['emergency_contact_phone'] ) ) : '';
		$clean['emergency_contact_relationship'] = isset( $raw_input['emergency_contact_relationship'] ) ? sanitize_text_field( wp_unslash( $raw_input['emergency_contact_relationship'] ) ) : '';

		// Employee ID code
		if ( ! empty( $raw_input['employee_id'] ) ) {
			$clean['employee_id'] = sanitize_text_field( wp_unslash( $raw_input['employee_id'] ) );
		}

		// Removal flags
		$clean['remove_profile_photo']     = ! empty( $raw_input['remove_profile_photo'] );
		$clean['remove_contract_document'] = ! empty( $raw_input['remove_contract_document'] );

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
