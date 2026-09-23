<?php
/**
 * Internationalization and RTL/LTR Localization Management.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_I18n
 */
class NDS_HR_I18n {

	/**
	 * Load plugin textdomain from languages directory.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'nds-hr',
			false,
			dirname( plugin_basename( NDS_HR_FILE ) ) . '/languages/'
		);
	}

	/**
	 * Check if current context is right-to-left.
	 *
	 * @return bool
	 */
	public static function is_rtl() {
		// Check WordPress native is_rtl() or explicit user preference cookie/query param
		if ( isset( $_GET['nds_hr_lang'] ) && 'ar' === sanitize_key( $_GET['nds_hr_lang'] ) ) {
			return true;
		}
		if ( isset( $_COOKIE['nds_hr_direction'] ) && 'rtl' === sanitize_key( $_COOKIE['nds_hr_direction'] ) ) {
			return true;
		}
		return is_rtl();
	}

	/**
	 * Get HTML text direction attribute.
	 *
	 * @return string 'rtl' or 'ltr'
	 */
	public static function get_direction() {
		return self::is_rtl() ? 'rtl' : 'ltr';
	}

	/**
	 * Enqueue RTL stylesheet if active language is RTL.
	 */
	public static function maybe_enqueue_rtl() {
		if ( self::is_rtl() ) {
			wp_enqueue_style(
				'nds-hr-rtl',
				NDS_HR_URL . 'assets/css/rtl.css',
				array(),
				NDS_HR_VERSION
			);
		}
	}
}
