<?php
/**
 * Independent Session Engine for NDS HR.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Session
 */
class NDS_HR_Session {

	/**
	 * Cookie name for session token.
	 */
	const COOKIE_NAME = 'nds_hr_session_token';

	/**
	 * Default session lifetime in seconds (14 days).
	 */
	const SESSION_LIFETIME = 1209600; // 14 days

	/**
	 * Active authenticated HR user object for current request.
	 *
	 * @var object|null
	 */
	protected static $current_user = null;

	/**
	 * Has session lookup been executed for this request?
	 *
	 * @var bool
	 */
	protected static $lookup_performed = false;

	/**
	 * Create a new secure session for an HR User ID.
	 *
	 * @param int  $hr_user_id
	 * @param bool $remember
	 * @return string|false Raw session token for cookie, or false on error.
	 */
	public static function create_session( $hr_user_id, $remember = false ) {
		global $wpdb;

		$hr_user_id = absint( $hr_user_id );
		if ( ! $hr_user_id ) {
			return false;
		}

		$sessions_table = NDS_HR_Database::sessions_table();
		$lifetime       = $remember ? ( self::SESSION_LIFETIME * 2 ) : self::SESSION_LIFETIME;
		$expires_at     = gmdate( 'Y-m-d H:i:s', time() + $lifetime );

		// Generate cryptographically secure random token (64 hex characters)
		try {
			$raw_token = bin2hex( random_bytes( 32 ) );
		} catch ( Exception $e ) {
			$raw_token = wp_generate_password( 64, false, false );
		}

		$ip_address = NDS_HR_Security::get_client_ip();
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		$inserted = $wpdb->insert(
			$sessions_table,
			array(
				'session_token' => $raw_token,
				'hr_user_id'    => $hr_user_id,
				'ip_address'    => $ip_address,
				'user_agent'    => $user_agent,
				'expires_at'    => $expires_at,
			),
			array( '%s', '%d', '%s', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return false;
		}

		// Set HttpOnly, Secure, SameSite cookie
		self::set_session_cookie( $raw_token, time() + $lifetime );

		// Update user's last login timestamp
		$users_table = NDS_HR_Database::users_table();
		$wpdb->update(
			$users_table,
			array( 'last_login_at' => current_time( 'mysql' ) ),
			array( 'id' => $hr_user_id ),
			array( '%s' ),
			array( '%d' )
		);

		return $raw_token;
	}

	/**
	 * Set the HTTP-only secure session cookie.
	 *
	 * @param string $token
	 * @param int    $expires_timestamp
	 */
	public static function set_session_cookie( $token, $expires_timestamp ) {
		$secure = is_ssl();
		$path   = COOKIEPATH ? COOKIEPATH : '/';

		if ( PHP_VERSION_ID >= 70300 ) {
			setcookie(
				self::COOKIE_NAME,
				$token,
				array(
					'expires'  => $expires_timestamp,
					'path'     => $path,
					'domain'   => COOKIE_DOMAIN,
					'secure'   => $secure,
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
		} else {
			setcookie(
				self::COOKIE_NAME,
				$token,
				$expires_timestamp,
				$path . '; samesite=Lax',
				COOKIE_DOMAIN,
				$secure,
				true
			);
		}

		$_COOKIE[ self::COOKIE_NAME ] = $token;
	}

	/**
	 * Clear the session cookie and delete session record from DB.
	 */
	public static function destroy_session() {
		global $wpdb;

		$token = self::get_cookie_token();
		if ( ! empty( $token ) ) {
			$sessions_table = NDS_HR_Database::sessions_table();
			$wpdb->delete(
				$sessions_table,
				array( 'session_token' => $token ),
				array( '%s' )
			);
		}

		// Clear cookie
		$path = COOKIEPATH ? COOKIEPATH : '/';
		if ( PHP_VERSION_ID >= 70300 ) {
			setcookie(
				self::COOKIE_NAME,
				'',
				array(
					'expires'  => time() - 3600,
					'path'     => $path,
					'domain'   => COOKIE_DOMAIN,
					'secure'   => is_ssl(),
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
		} else {
			setcookie( self::COOKIE_NAME, '', time() - 3600, $path, COOKIE_DOMAIN, is_ssl(), true );
		}

		unset( $_COOKIE[ self::COOKIE_NAME ] );
		self::$current_user      = null;
		self::$lookup_performed = true;
	}

	/**
	 * Retrieve current session token from cookie.
	 *
	 * @return string
	 */
	public static function get_cookie_token() {
		if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			return sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) );
		}
		return '';
	}

	/**
	 * Validate active session and return authenticated HR user record.
	 *
	 * @return object|null User database object or null.
	 */
	public static function get_authenticated_user() {
		if ( self::$lookup_performed ) {
			return self::$current_user;
		}

		self::$lookup_performed = true;
		$token = self::get_cookie_token();

		if ( empty( $token ) ) {
			self::$current_user = null;
			return null;
		}

		global $wpdb;

		$sessions_table = NDS_HR_Database::sessions_table();
		$users_table    = NDS_HR_Database::users_table();
		$roles_table    = NDS_HR_Database::roles_table();

		// Query session joined with user and role
		$query = "
			SELECT 
				u.id,
				u.username,
				u.email,
				u.status,
				u.first_name,
				u.last_name,
				u.display_name,
				u.require_password_change,
				u.role_id,
				r.slug AS role_slug,
				r.name AS role_name,
				s.expires_at
			FROM {$sessions_table} s
			INNER JOIN {$users_table} u ON s.hr_user_id = u.id
			LEFT JOIN {$roles_table} r ON u.role_id = r.id
			WHERE s.session_token = %s
			LIMIT 1
		";

		$user = $wpdb->get_row( $wpdb->prepare( $query, $token ) );

		if ( ! $user ) {
			self::$current_user = null;
			return null;
		}

		// Verify session expiry
		if ( strtotime( $user->expires_at ) < time() ) {
			// Expired session - purge it
			$wpdb->delete( $sessions_table, array( 'session_token' => $token ), array( '%s' ) );
			self::$current_user = null;
			return null;
		}

		// Verify user status
		if ( 'active' !== $user->status ) {
			self::$current_user = null;
			return null;
		}

		self::$current_user = $user;
		return self::$current_user;
	}

	/**
	 * Check if there is an actively authenticated HR user.
	 *
	 * @return bool
	 */
	public static function is_logged_in() {
		return null !== self::get_authenticated_user();
	}

	/**
	 * Get current HR User ID.
	 *
	 * @return int
	 */
	public static function get_current_user_id() {
		$user = self::get_authenticated_user();
		return $user ? (int) $user->id : 0;
	}
}
