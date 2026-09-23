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
		if ( ! isset( $_POST['_nds_hr_settings_nonce'] ) || ! NDS_HR_Security::verify_nonce( self::NONCE_ACTION, '_nds_hr_settings_nonce' ) ) {
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

			case 'create_custom_field':
				$this->process_create_custom_field();
				break;

			case 'update_custom_field':
				$this->process_update_custom_field();
				break;

			case 'toggle_custom_field_status':
				$this->process_toggle_custom_field_status();
				break;

			case 'delete_custom_field':
				$this->process_delete_custom_field();
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
	 * Process Custom Field Creation for Employee entity.
	 */
	protected function process_create_custom_field() {
		$entity      = 'employee';
		$field_label = isset( $_POST['field_label'] ) ? sanitize_text_field( wp_unslash( $_POST['field_label'] ) ) : '';
		$field_key   = isset( $_POST['field_key'] ) ? strtolower( sanitize_key( wp_unslash( $_POST['field_key'] ) ) ) : '';
		$field_type  = isset( $_POST['field_type'] ) ? sanitize_key( wp_unslash( $_POST['field_type'] ) ) : 'text';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$is_required = ! empty( $_POST['is_required'] ) ? 1 : 0;
		$is_active   = isset( $_POST['is_active'] ) ? ( ! empty( $_POST['is_active'] ) ? 1 : 0 ) : 1;
		$sort_order  = isset( $_POST['sort_order'] ) ? (int) $_POST['sort_order'] : 10;

		// Parse dynamic options if select/multiselect/checkbox/radio
		$settings = array();
		if ( in_array( $field_type, array( 'select', 'multiselect', 'checkbox', 'radio' ), true ) ) {
			$settings['options'] = $this->parse_dynamic_options_from_post();
		}

		$data = array(
			'entity'      => $entity,
			'field_key'   => $field_key,
			'field_label' => $field_label,
			'field_type'  => $field_type,
			'description' => $description,
			'is_required' => $is_required,
			'is_active'   => $is_active,
			'sort_order'  => $sort_order,
			'settings'    => $settings,
		);

		$result = NDS_HR_Custom_Fields::create_field( $data );

		if ( is_wp_error( $result ) ) {
			$redirect_url = NDS_HR_Router::url(
				'settings',
				array(
					'section'   => 'employees',
					'cf_error'  => rawurlencode( $result->get_error_message() ),
				)
			);
			wp_safe_redirect( $redirect_url );
			exit;
		}

		$field_id    = (int) $result;
		$current_uid = NDS_HR_Session::get_current_user_id();

		NDS_HR_Audit_Logger::log(
			'custom_field_created',
			'custom_fields',
			$field_id,
			array(),
			array(
				'entity'      => $entity,
				'field_key'   => $field_key,
				'field_label' => $field_label,
				'field_type'  => $field_type,
				'is_required' => $is_required,
				'is_active'   => $is_active,
				'sort_order'  => $sort_order,
			),
			$current_uid
		);

		$redirect_url = NDS_HR_Router::url(
			'settings',
			array(
				'section'   => 'employees',
				'cf_notice' => 'created',
			)
		);
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Process Custom Field Update.
	 */
	protected function process_update_custom_field() {
		$field_id = isset( $_POST['field_id'] ) ? absint( $_POST['field_id'] ) : 0;
		if ( ! $field_id ) {
			wp_die( esc_html__( 'Invalid field ID.', 'nds-hr' ), '', array( 'response' => 400 ) );
		}

		$existing = NDS_HR_Custom_Fields::get_field( $field_id );
		if ( ! $existing ) {
			wp_die( esc_html__( 'Custom field not found.', 'nds-hr' ), '', array( 'response' => 404 ) );
		}

		$field_label = isset( $_POST['field_label'] ) ? sanitize_text_field( wp_unslash( $_POST['field_label'] ) ) : $existing->field_label;
		$field_key   = isset( $_POST['field_key'] ) ? strtolower( sanitize_key( wp_unslash( $_POST['field_key'] ) ) ) : $existing->field_key;
		$field_type  = isset( $_POST['field_type'] ) ? sanitize_key( wp_unslash( $_POST['field_type'] ) ) : $existing->field_type;
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$is_required = ! empty( $_POST['is_required'] ) ? 1 : 0;
		$is_active   = isset( $_POST['is_active'] ) ? ( ! empty( $_POST['is_active'] ) ? 1 : 0 ) : 0;
		$sort_order  = isset( $_POST['sort_order'] ) ? (int) $_POST['sort_order'] : $existing->sort_order;

		$settings = is_array( $existing->settings ) ? $existing->settings : array();
		if ( in_array( $field_type, array( 'select', 'multiselect', 'checkbox', 'radio' ), true ) ) {
			$settings['options'] = $this->parse_dynamic_options_from_post();
		}

		$update_data = array(
			'field_label' => $field_label,
			'field_key'   => $field_key,
			'field_type'  => $field_type,
			'description' => $description,
			'is_required' => $is_required,
			'is_active'   => $is_active,
			'sort_order'  => $sort_order,
			'settings'    => $settings,
		);

		$result = NDS_HR_Custom_Fields::update_field( $field_id, $update_data );

		if ( is_wp_error( $result ) ) {
			$redirect_url = NDS_HR_Router::url(
				'settings',
				array(
					'section'  => 'employees',
					'cf_error' => rawurlencode( $result->get_error_message() ),
				)
			);
			wp_safe_redirect( $redirect_url );
			exit;
		}

		$current_uid = NDS_HR_Session::get_current_user_id();
		NDS_HR_Audit_Logger::log(
			'custom_field_updated',
			'custom_fields',
			$field_id,
			array(
				'field_label' => $existing->field_label,
				'field_key'   => $existing->field_key,
				'field_type'  => $existing->field_type,
				'is_required' => $existing->is_required,
				'is_active'   => $existing->is_active,
				'sort_order'  => $existing->sort_order,
			),
			array(
				'field_label' => $field_label,
				'field_key'   => $field_key,
				'field_type'  => $field_type,
				'is_required' => $is_required,
				'is_active'   => $is_active,
				'sort_order'  => $sort_order,
			),
			$current_uid
		);

		$redirect_url = NDS_HR_Router::url(
			'settings',
			array(
				'section'   => 'employees',
				'cf_notice' => 'updated',
			)
		);
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Process Custom Field Activate / Deactivate Toggle.
	 */
	protected function process_toggle_custom_field_status() {
		$field_id = isset( $_POST['field_id'] ) ? absint( $_POST['field_id'] ) : 0;
		if ( ! $field_id ) {
			wp_die( esc_html__( 'Invalid field ID.', 'nds-hr' ), '', array( 'response' => 400 ) );
		}

		$existing = NDS_HR_Custom_Fields::get_field( $field_id );
		if ( ! $existing ) {
			wp_die( esc_html__( 'Custom field not found.', 'nds-hr' ), '', array( 'response' => 404 ) );
		}

		$new_status = $existing->is_active ? 0 : 1;
		$result     = NDS_HR_Custom_Fields::update_field( $field_id, array( 'is_active' => $new_status ) );

		if ( ! is_wp_error( $result ) ) {
			$current_uid = NDS_HR_Session::get_current_user_id();
			$event_type  = $new_status ? 'custom_field_activated' : 'custom_field_deactivated';

			NDS_HR_Audit_Logger::log(
				$event_type,
				'custom_fields',
				$field_id,
				array( 'is_active' => $existing->is_active ),
				array( 'is_active' => $new_status, 'field_key' => $existing->field_key ),
				$current_uid
			);
		}

		$notice = $new_status ? 'activated' : 'deactivated';
		$redirect_url = NDS_HR_Router::url(
			'settings',
			array(
				'section'   => 'employees',
				'cf_notice' => $notice,
			)
		);
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Process Custom Field Deletion.
	 */
	protected function process_delete_custom_field() {
		$field_id = isset( $_POST['field_id'] ) ? absint( $_POST['field_id'] ) : 0;
		if ( ! $field_id ) {
			wp_die( esc_html__( 'Invalid field ID.', 'nds-hr' ), '', array( 'response' => 400 ) );
		}

		$existing = NDS_HR_Custom_Fields::get_field( $field_id );
		if ( ! $existing ) {
			wp_die( esc_html__( 'Custom field not found.', 'nds-hr' ), '', array( 'response' => 404 ) );
		}

		// Snapshot metadata before deletion
		$old_meta = array(
			'entity'      => $existing->entity,
			'field_key'   => $existing->field_key,
			'field_label' => $existing->field_label,
			'field_type'  => $existing->field_type,
		);

		$deleted = NDS_HR_Custom_Fields::delete_field( $field_id );

		if ( $deleted ) {
			$current_uid = NDS_HR_Session::get_current_user_id();
			NDS_HR_Audit_Logger::log(
				'custom_field_deleted',
				'custom_fields',
				$field_id,
				$old_meta,
				array( 'deleted' => 1 ),
				$current_uid
			);
		}

		$redirect_url = NDS_HR_Router::url(
			'settings',
			array(
				'section'   => 'employees',
				'cf_notice' => 'deleted',
			)
		);
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Parse dynamic options array from POST.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	protected function parse_dynamic_options_from_post() {
		$options = array();

		if ( empty( $_POST['option_labels'] ) || ! is_array( $_POST['option_labels'] ) ) {
			return $options;
		}

		$labels = (array) $_POST['option_labels'];
		$values = isset( $_POST['option_values'] ) && is_array( $_POST['option_values'] ) ? (array) $_POST['option_values'] : array();

		foreach ( $labels as $idx => $raw_label ) {
			$label = sanitize_text_field( wp_unslash( $raw_label ) );
			if ( '' === $label ) {
				continue;
			}

			$val = isset( $values[ $idx ] ) ? sanitize_key( wp_unslash( $values[ $idx ] ) ) : '';
			if ( empty( $val ) ) {
				// Generate safe key from label
				$val = sanitize_key( str_replace( ' ', '_', strtolower( $label ) ) );
			}

			$options[] = array(
				'value' => $val,
				'label' => $label,
			);
		}

		return $options;
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
		$cf_notice  = isset( $_GET['cf_notice'] ) ? sanitize_key( wp_unslash( $_GET['cf_notice'] ) ) : '';
		$cf_error   = isset( $_GET['cf_error'] ) ? sanitize_text_field( rawurldecode( wp_unslash( $_GET['cf_error'] ) ) ) : '';

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

		// Load custom fields for employee entity
		$employee_custom_fields = NDS_HR_Custom_Fields::get_entity_fields( 'employee', false );
		$supported_field_types  = NDS_HR_Custom_Fields::get_supported_field_types();

		include NDS_HR_PATH . 'templates/admin/settings.php';
	}
}
