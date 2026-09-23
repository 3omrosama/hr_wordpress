<?php
/**
 * Centralized Routing and URL Management for NDS HR.
 *
 * Normalizes routes and canonical URLs dynamically across root and subdirectory WordPress installations.
 * Prevents URL duplication bugs (such as /hr/hr/*) and provides a single source of truth for all HR links.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Router
 */
class NDS_HR_Router {

	/**
	 * Query variable constants.
	 */
	const QUERY_VAR_ROUTE  = 'nds_hr_route';
	const QUERY_VAR_TAB    = 'nds_hr_tab';
	const QUERY_VAR_PORTAL = 'nds_hr_portal';

	/**
	 * Check if WordPress installation home URL is in a subdirectory ending with '/hr'.
	 *
	 * @return bool
	 */
	public static function is_wp_in_hr_subpath() {
		$home_path = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
		return (bool) preg_match( '#(^|/)hr$#i', $home_path );
	}

	/**
	 * Get web host base URL (scheme + host + port), without subdirectory path.
	 *
	 * @return string E.g. "http://127.0.0.1" or "https://example.com"
	 */
	public static function get_site_root_url() {
		$home_url = home_url();
		$parts    = wp_parse_url( $home_url );
		$scheme   = isset( $parts['scheme'] ) ? $parts['scheme'] : 'http';
		$host     = isset( $parts['host'] ) ? $parts['host'] : '127.0.0.1';
		$port     = isset( $parts['port'] ) ? ':' . $parts['port'] : '';
		return $scheme . '://' . $host . $port;
	}

	/**
	 * Get the canonical base URL for the HR application (/hr/).
	 * Dynamically avoids duplicate '/hr/' if WordPress is installed in a '/hr' subdirectory.
	 *
	 * Examples:
	 * - WP at "http://127.0.0.1/hr/" -> returns "http://127.0.0.1/hr/"
	 * - WP at "https://example.com/" -> returns "https://example.com/hr/"
	 * - WP at "https://example.com/site/" -> returns "https://example.com/site/hr/"
	 *
	 * @return string Canonical trailing-slashed HR base URL.
	 */
	public static function get_hr_base_url() {
		$home_url = home_url();

		if ( self::is_wp_in_hr_subpath() ) {
			// WordPress home already ends with /hr, so home_url() is the canonical HR base
			return trailingslashit( $home_url );
		}

		// WordPress is installed in root or another subdirectory; append /hr/
		return trailingslashit( $home_url ) . 'hr/';
	}

	/**
	 * Get the canonical base URL for the Employee Self-Service Portal (/employee/).
	 *
	 * Examples:
	 * - WP at "http://127.0.0.1/hr/" -> returns "http://127.0.0.1/employee/"
	 * - WP at "https://example.com/" -> returns "https://example.com/employee/"
	 * - WP at "https://example.com/site/" -> returns "https://example.com/site/employee/"
	 *
	 * @return string Canonical trailing-slashed employee portal base URL.
	 */
	public static function get_employee_base_url() {
		if ( self::is_wp_in_hr_subpath() ) {
			// WordPress is installed under /hr/, but employee portal sits at host root /employee/
			return trailingslashit( self::get_site_root_url() ) . 'employee/';
		}

		$home_url = home_url();
		return trailingslashit( $home_url ) . 'employee/';
	}

	/**
	 * Centralized URL builder for all NDS HR routes.
	 *
	 * @param string $route Route key ('setup', 'login', 'logout', 'reset-password', 'admin', 'employee', 'home').
	 * @param array  $args  Optional query arguments (or ['tab' => 'subtab']).
	 * @return string Fully qualified, normalized URL.
	 */
	public static function url( $route = 'admin', $args = array() ) {
		$route_key = strtolower( trim( $route, '/' ) );
		$base_url  = '';

		switch ( $route_key ) {
			case 'setup':
				$base_url = self::get_hr_base_url() . 'setup/';
				break;

			case 'login':
				$base_url = self::get_hr_base_url() . 'login/';
				break;

			case 'logout':
				$base_url = self::get_hr_base_url() . 'logout/';
				break;

			case 'reset_password':
			case 'reset-password':
				$base_url = self::get_hr_base_url() . 'reset-password/';
				break;

			case 'employee':
			case 'portal':
				$tab = isset( $args['tab'] ) ? sanitize_key( $args['tab'] ) : '';
				if ( ! empty( $tab ) && 'dashboard' !== $tab ) {
					$base_url = self::get_employee_base_url() . $tab . '/';
					unset( $args['tab'] );
				} else {
					$base_url = self::get_employee_base_url();
					unset( $args['tab'] );
				}
				break;

			case 'home':
			case 'site':
				$base_url = trailingslashit( home_url() );
				break;

			case 'admin':
			case 'dashboard':
			case 'hr':
			default:
				$tab = isset( $args['tab'] ) ? sanitize_key( $args['tab'] ) : '';
				if ( ! empty( $tab ) && 'dashboard' !== $tab ) {
					$base_url = self::get_hr_base_url() . $tab . '/';
					unset( $args['tab'] );
				} else {
					$base_url = self::get_hr_base_url();
					unset( $args['tab'] );
				}
				break;
		}

		if ( ! empty( $args ) && is_array( $args ) ) {
			$base_url = add_query_arg( $args, $base_url );
		}

		return $base_url;
	}

	/**
	 * Register rewrite endpoints with WordPress rewrite system.
	 */
	public static function register_rewrite_rules() {
		// 1. Standard canonical rules when WP is installed at root or general subdirectory
		add_rewrite_rule( '^hr/setup/?$', 'index.php?nds_hr_route=setup', 'top' );
		add_rewrite_rule( '^hr/login/?$', 'index.php?nds_hr_route=login', 'top' );
		add_rewrite_rule( '^hr/logout/?$', 'index.php?nds_hr_route=logout', 'top' );
		add_rewrite_rule( '^hr/reset-password/?$', 'index.php?nds_hr_route=reset_password', 'top' );
		add_rewrite_rule( '^hr/([a-z0-9_-]+)/?$', 'index.php?nds_hr_route=admin&nds_hr_tab=$matches[1]', 'top' );
		add_rewrite_rule( '^hr/?$', 'index.php?nds_hr_route=admin', 'top' );
		add_rewrite_rule( '^login/?$', 'index.php?nds_hr_route=login', 'top' );

		// 2. Rules when WP is installed in a /hr/ subdirectory (where request path strips /hr)
		add_rewrite_rule( '^setup/?$', 'index.php?nds_hr_route=setup', 'top' );
		add_rewrite_rule( '^logout/?$', 'index.php?nds_hr_route=logout', 'top' );
		add_rewrite_rule( '^reset-password/?$', 'index.php?nds_hr_route=reset_password', 'top' );

		// 3. Employee portal rules
		add_rewrite_rule( '^employee/([a-z0-9_-]+)/?$', 'index.php?nds_hr_portal=1&nds_hr_tab=$matches[1]', 'top' );
		add_rewrite_rule( '^employee/?$', 'index.php?nds_hr_portal=1', 'top' );
		add_rewrite_rule( '^hr/employee/([a-z0-9_-]+)/?$', 'index.php?nds_hr_portal=1&nds_hr_tab=$matches[1]', 'top' );
		add_rewrite_rule( '^hr/employee/?$', 'index.php?nds_hr_portal=1', 'top' );
	}

	/**
	 * Register query variables for NDS HR routing.
	 *
	 * @param array $vars
	 * @return array
	 */
	public static function register_query_vars( $vars ) {
		$vars[] = self::QUERY_VAR_ROUTE;
		$vars[] = self::QUERY_VAR_TAB;
		$vars[] = self::QUERY_VAR_PORTAL;
		$vars[] = 'nds_hr_login';
		return $vars;
	}

	/**
	 * Parse incoming request deterministically regardless of rewrite rule cache or WP installation path.
	 *
	 * @param WP $wp Current WordPress environment instance.
	 */
	public static function parse_request( $wp ) {
		$wp_req = isset( $wp->request ) ? trim( $wp->request, '/' ) : '';

		$raw_uri   = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path_only = (string) wp_parse_url( $raw_uri, PHP_URL_PATH );
		$full_path = trim( $path_only, '/' );

		// Normalize paths to lowercase for matching
		$path_lower = strtolower( $wp_req );
		$full_lower = strtolower( $full_path );

		$is_hr_sub = self::is_wp_in_hr_subpath();

		// 1. SETUP ROUTE: /hr/setup/
		if ( 'hr/setup' === $path_lower || 'setup' === $path_lower || 'hr/setup' === $full_lower ) {
			$wp->query_vars[ self::QUERY_VAR_ROUTE ] = 'setup';
			$wp->matched_rule                        = '^hr/setup/?$';
			return;
		}

		// 2. LOGIN ROUTE: /hr/login/ or /login/
		if ( 'hr/login' === $path_lower || 'login' === $path_lower || 'hr/login' === $full_lower || 'login' === $full_lower ) {
			$wp->query_vars[ self::QUERY_VAR_ROUTE ] = 'login';
			$wp->matched_rule                        = '^hr/login/?$';
			return;
		}

		// 3. LOGOUT ROUTE: /hr/logout/
		if ( 'hr/logout' === $path_lower || 'logout' === $path_lower || 'hr/logout' === $full_lower ) {
			$wp->query_vars[ self::QUERY_VAR_ROUTE ] = 'logout';
			$wp->matched_rule                        = '^hr/logout/?$';
			return;
		}

		// 4. RESET PASSWORD ROUTE: /hr/reset-password/
		if ( 'hr/reset-password' === $path_lower || 'reset-password' === $path_lower || 'hr/reset-password' === $full_lower ) {
			$wp->query_vars[ self::QUERY_VAR_ROUTE ] = 'reset_password';
			$wp->matched_rule                        = '^hr/reset-password/?$';
			return;
		}

		// 5. EMPLOYEE PORTAL SUB-TABS: /employee/{tab}/ or /hr/employee/{tab}/
		if ( preg_match( '#^(?:hr/)?employee/([a-z0-9_-]+)$#i', $path_lower, $matches ) ||
		     preg_match( '#^(?:hr/)?employee/([a-z0-9_-]+)$#i', $full_lower, $matches ) ) {
			$wp->query_vars[ self::QUERY_VAR_PORTAL ] = 1;
			$wp->query_vars[ self::QUERY_VAR_TAB ]    = sanitize_key( $matches[1] );
			$wp->matched_rule                         = '^employee/([a-z0-9_-]+)/?$';
			return;
		}

		// 6. EMPLOYEE PORTAL MAIN: /employee/ or /hr/employee/
		if ( 'employee' === $path_lower || 'hr/employee' === $path_lower || 'employee' === $full_lower || 'hr/employee' === $full_lower ) {
			$wp->query_vars[ self::QUERY_VAR_PORTAL ] = 1;
			$wp->query_vars[ self::QUERY_VAR_TAB ]    = 'dashboard';
			$wp->matched_rule                         = '^employee/?$';
			return;
		}

		// 7. HR ADMIN SUB-TABS: /hr/{tab}/
		if ( preg_match( '#^hr/([a-z0-9_-]+)$#i', $path_lower, $matches ) ||
		     preg_match( '#^hr/([a-z0-9_-]+)$#i', $full_lower, $matches ) ) {
			$wp->query_vars[ self::QUERY_VAR_ROUTE ] = 'admin';
			$wp->query_vars[ self::QUERY_VAR_TAB ]   = sanitize_key( $matches[1] );
			$wp->matched_rule                        = '^hr/([a-z0-9_-]+)/?$';
			return;
		}

		// 8. HR ADMIN MAIN: /hr/
		// Matches when visiting /hr/ directly, or when WP is installed in /hr/ and visiting the root of that installation
		if ( 'hr' === $path_lower || 'hr' === $full_lower || ( $is_hr_sub && '' === $path_lower && 'hr' === $full_lower ) ) {
			$wp->query_vars[ self::QUERY_VAR_ROUTE ] = 'admin';
			$wp->query_vars[ self::QUERY_VAR_TAB ]   = 'dashboard';
			$wp->matched_rule                        = '^hr/?$';
			return;
		}

		// 9. When WP is installed in /hr/ and subpath tabs like /hr/employees/ are visited ($path_lower is 'employees')
		if ( $is_hr_sub && ! empty( $path_lower ) && preg_match( '#^([a-z0-9_-]+)$#i', $path_lower, $matches ) ) {
			$known_tabs = array( 'dashboard', 'employees', 'roles', 'audit-logs', 'settings', 'reports', 'departments', 'profile' );
			if ( in_array( $matches[1], $known_tabs, true ) ) {
				$wp->query_vars[ self::QUERY_VAR_ROUTE ] = 'admin';
				$wp->query_vars[ self::QUERY_VAR_TAB ]   = sanitize_key( $matches[1] );
				$wp->matched_rule                        = '^([a-z0-9_-]+)/?$';
				return;
			}
		}
	}
}
