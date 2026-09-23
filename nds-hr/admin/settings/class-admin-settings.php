<?php
/**
 * Admin Controller for NDS HR Settings Framework (Phase 1).
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Admin_Settings
 */
class NDS_HR_Admin_Settings {

	/**
	 * Plugin instance.
	 *
	 * @var NDS_HR_Plugin
	 */
	protected $plugin;

	/**
	 * Nonce action for settings forms.
	 */
	const NONCE_ACTION = 'nds_hr_save_settings_action';

	/**
	 * Constructor.
	 *
	 * @param NDS_HR_Plugin $plugin
	 */
	public function __construct( NDS_HR_Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Handle POST form submissions for settings sections.
	 */
	public function handle_form_submissions() {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		if ( empty( $_POST['nds_hr_settings_action'] ) ) {
			return;
		}

		$action = sanitize_key( wp_unslash( $_POST['nds_hr_settings_action'] ) );

		// 1. Verify CSRF Nonce
		if ( ! isset( $_POST['_nds_hr_settings_nonce'] ) || ! NDS_HR_Security::verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nds_hr_settings_nonce'] ) ), self::NONCE_ACTION ) ) {
			wp_die(
				esc_html__( 'Security check failed. Please refresh the page and try again.', 'nds-hr' ),
				esc_html__( 'Authorization Error', 'nds-hr' ),
				array( 'response' => 403 )
			);
		}

		// 2. Server-side permission check: user must have settings.manage capability
		if ( ! NDS_HR_Permissions::can_manage_settings() ) {
			wp_die(
				esc_html__( 'You do not have permission to modify system settings.', 'nds-hr' ),
				esc_html__( 'Permission Denied', 'nds-hr' ),
				array( 'response' => 403 )
			);
		}

		switch ( $action ) {
			case 'save_general':
				$this->process_save_general_settings();
				break;

			case 'save_localization':
				$this->process_save_localization_settings();
				break;

			default:
				break;
		}
	}

	/**
	 * Process General Settings save.
	 */
	protected function process_save_general_settings() {
		$company_name    = isset( $_POST['company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['company_name'] ) ) : '';
		$company_email   = isset( $_POST['company_email'] ) ? sanitize_email( wp_unslash( $_POST['company_email'] ) ) : '';
		$company_phone   = isset( $_POST['company_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['company_phone'] ) ) : '';
		$company_address = isset( $_POST['company_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['company_address'] ) ) : '';

		// Snapshot old values
		$old_values = array(
			'general_company_name'    => NDS_HR_Settings::get( 'general_company_name', '' ),
			'general_company_email'   => NDS_HR_Settings::get( 'general_company_email', '' ),
			'general_company_phone'   => NDS_HR_Settings::get( 'general_company_phone', '' ),
			'general_company_address' => NDS_HR_Settings::get( 'general_company_address', '' ),
		);

		$new_values = array(
			'general_company_name'    => $company_name,
			'general_company_email'   => $company_email,
			'general_company_phone'   => $company_phone,
			'general_company_address' => $company_address,
		);

		// Persist settings
		NDS_HR_Settings::set( 'general_company_name', $company_name, 'string', true );
		NDS_HR_Settings::set( 'general_company_email', $company_email, 'string', true );
		NDS_HR_Settings::set( 'general_company_phone', $company_phone, 'string', true );
		NDS_HR_Settings::set( 'general_company_address', $company_address, 'string', true );

		// Detect changes and generate safe audit log
		$changed_keys = array();
		foreach ( $new_values as $k => $v ) {
			if ( $old_values[ $k ] !== $v ) {
				$changed_keys[] = str_replace( 'general_', 'general.', $k );
			}
		}

		if ( ! empty( $changed_keys ) ) {
			$current_uid = NDS_HR_Session::get_current_user_id();
			NDS_HR_Audit_Logger::log(
				'settings_updated',
				'settings',
				0,
				array( 'changed_keys' => $changed_keys ),
				array( 'section' => 'general', 'changed_keys' => $changed_keys ),
				$current_uid
			);
		}

		// Redirect back with success flag
		$redirect_url = NDS_HR_Router::url( 'settings', array( 'section' => 'general', 'saved' => 1 ) );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Process Localization Settings save.
	 */
	protected function process_save_localization_settings() {
		$raw_lang         = isset( $_POST['default_language'] ) ? sanitize_key( wp_unslash( $_POST['default_language'] ) ) : 'en';
		$default_language = in_array( $raw_lang, array( 'en', 'ar' ), true ) ? $raw_lang : 'en';

		$old_lang = NDS_HR_Settings::get( 'localization_default_language', 'en' );

		NDS_HR_Settings::set( 'localization_default_language', $default_language, 'string', true );

		if ( $old_lang !== $default_language ) {
			$current_uid = NDS_HR_Session::get_current_user_id();
			NDS_HR_Audit_Logger::log(
				'settings_updated',
				'settings',
				0,
				array( 'localization_default_language' => $old_lang ),
				array( 'localization_default_language' => $default_language ),
				$current_uid
			);
		}

		$redirect_url = NDS_HR_Router::url( 'settings', array( 'section' => 'localization', 'saved' => 1 ) );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Render the Settings main workspace.
	 *
	 * @param string $section Active sub-section ('general', 'employees', 'attendance', 'leave', 'payroll', 'notifications', 'localization', 'security').
	 */
	public function render_settings_page( $section = 'general' ) {
		// Server-side authorization check: view permission
		if ( ! NDS_HR_Permissions::can_view_settings() ) {
			echo '<div class="nds-hr-card" style="padding: 24px; text-align: center; color: #DC2626;">';
			echo '<span class="dashicons dashicons-lock" style="font-size: 32px; width: 32px; height: 32px; margin-bottom: 8px;"></span>';
			echo '<h3>' . esc_html__( 'Access Denied', 'nds-hr' ) . '</h3>';
			echo '<p>' . esc_html__( 'You do not have permission to view the system settings area.', 'nds-hr' ) . '</p>';
			echo '</div>';
			return;
		}

		$valid_sections = array(
			'general',
			'employees',
			'attendance',
			'leave',
			'payroll',
			'notifications',
			'localization',
			'security',
		);

		$section = sanitize_key( $section );
		if ( ! in_array( $section, $valid_sections, true ) ) {
			$section = 'general';
		}

		$can_manage = NDS_HR_Permissions::can_manage_settings();
		$is_saved   = isset( $_GET['saved'] ) && '1' === (string) $_GET['saved'];

		// Load settings data
		$general_settings = array(
			'company_name'    => NDS_HR_Settings::get( 'general_company_name', '' ),
			'company_email'   => NDS_HR_Settings::get( 'general_company_email', '' ),
			'company_phone'   => NDS_HR_Settings::get( 'general_company_phone', '' ),
			'company_address' => NDS_HR_Settings::get( 'general_company_address', '' ),
		);

		$localization_settings = array(
			'default_language' => NDS_HR_Settings::get( 'localization_default_language', 'en' ),
		);

		include NDS_HR_PATH . 'templates/admin/settings.php';
	}
}
