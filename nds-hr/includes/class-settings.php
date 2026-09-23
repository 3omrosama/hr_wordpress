<?php
/**
 * Centralized Settings Repository and Service for NDS HR.
 * Provides typed, cached, and autoloaded configuration storage in wp_nds_hr_settings.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Settings
 */
class NDS_HR_Settings {

	/**
	 * In-memory settings cache [ setting_key => setting_value ].
	 *
	 * @var array<string, mixed>
	 */
	protected static $cache = array();

	/**
	 * Flag indicating if autoloaded settings have been populated.
	 *
	 * @var bool
	 */
	protected static $autoloaded = false;

	/**
	 * Load all autoloaded settings into memory cache.
	 */
	public static function init() {
		if ( self::$autoloaded ) {
			return;
		}

		global $wpdb;
		$table = NDS_HR_Database::settings_table();

		// Check if table exists before querying
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( ! $table_exists ) {
			self::$autoloaded = true;
			return;
		}

		$results = $wpdb->get_results( "SELECT setting_key, setting_value, setting_type FROM {$table} WHERE autoload = 1" );

		if ( ! empty( $results ) && is_array( $results ) ) {
			foreach ( $results as $row ) {
				self::$cache[ $row->setting_key ] = self::cast_value_from_db( $row->setting_value, $row->setting_type );
			}
		}

		self::$autoloaded = true;
	}

	/**
	 * Get a setting value by key.
	 *
	 * @param string $key     Setting key (e.g. 'company_name', 'general.company_name', 'localization.default_language').
	 * @param mixed  $default Fallback value if setting is not found.
	 * @return mixed Typed setting value.
	 */
	public static function get( $key, $default = null ) {
		self::init();

		$key = sanitize_key( $key );

		if ( array_key_exists( $key, self::$cache ) ) {
			return self::$cache[ $key ];
		}

		// If not in cache (e.g. not autoloaded), query the database directly
		global $wpdb;
		$table = NDS_HR_Database::settings_table();

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT setting_value, setting_type FROM {$table} WHERE setting_key = %s LIMIT 1",
				$key
			)
		);

		if ( $row ) {
			$value = self::cast_value_from_db( $row->setting_value, $row->setting_type );
			self::$cache[ $key ] = $value;
			return $value;
		}

		return $default;
	}

	/**
	 * Check if a setting exists in the database.
	 *
	 * @param string $key Setting key.
	 * @return bool
	 */
	public static function has( $key ) {
		self::init();

		$key = sanitize_key( $key );

		if ( array_key_exists( $key, self::$cache ) ) {
			return true;
		}

		global $wpdb;
		$table = NDS_HR_Database::settings_table();

		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT 1 FROM {$table} WHERE setting_key = %s LIMIT 1",
				$key
			)
		);

		return (bool) $exists;
	}

	/**
	 * Set or update a setting value.
	 *
	 * @param string $key      Setting key.
	 * @param mixed  $value    Setting value.
	 * @param string $type     Value type ('string', 'int', 'float', 'bool', 'json', 'array').
	 * @param bool   $autoload Whether to preload on bootstrap (default: true).
	 * @return bool True on success, false on failure.
	 */
	public static function set( $key, $value, $type = 'string', $autoload = true ) {
		self::init();

		$key      = sanitize_key( $key );
		$type     = in_array( $type, array( 'string', 'int', 'float', 'bool', 'json', 'array' ), true ) ? $type : 'string';
		$db_value = self::format_value_for_db( $value, $type );

		global $wpdb;
		$table = NDS_HR_Database::settings_table();

		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE setting_key = %s LIMIT 1",
				$key
			)
		);

		$data = array(
			'setting_key'   => $key,
			'setting_value' => $db_value,
			'setting_type'  => $type,
			'autoload'      => $autoload ? 1 : 0,
			'updated_at'    => current_time( 'mysql' ),
		);

		if ( $existing ) {
			$result = $wpdb->update(
				$table,
				$data,
				array( 'setting_key' => $key ),
				array( '%s', '%s', '%s', '%d', '%s' ),
				array( '%s' )
			);
			$success = false !== $result;
		} else {
			$data['created_at'] = current_time( 'mysql' );
			$result             = $wpdb->insert(
				$table,
				$data,
				array( '%s', '%s', '%s', '%d', '%s', '%s' )
			);
			$success = false !== $result;
		}

		if ( $success ) {
			self::$cache[ $key ] = self::cast_value_from_db( $db_value, $type );
		}

		return $success;
	}

	/**
	 * Delete a setting.
	 *
	 * @param string $key Setting key.
	 * @return bool True on success, false on failure.
	 */
	public static function delete( $key ) {
		self::init();

		$key = sanitize_key( $key );

		global $wpdb;
		$table = NDS_HR_Database::settings_table();

		$result = $wpdb->delete(
			$table,
			array( 'setting_key' => $key ),
			array( '%s' )
		);

		unset( self::$cache[ $key ] );

		return false !== $result;
	}

	/**
	 * Retrieve all settings starting with a given group prefix (e.g. 'general_' or 'localization_').
	 *
	 * @param string $prefix Group prefix.
	 * @return array<string, mixed> Key-value pairs with prefix stripped.
	 */
	public static function get_group( $prefix ) {
		self::init();

		$prefix = sanitize_key( $prefix );
		$len    = strlen( $prefix );
		$group  = array();

		// From memory cache
		foreach ( self::$cache as $key => $val ) {
			if ( 0 === strpos( $key, $prefix ) ) {
				$short_key           = substr( $key, $len );
				$group[ $short_key ] = $val;
			}
		}

		return $group;
	}

	/**
	 * Cast raw database string to typed PHP value.
	 *
	 * @param string|null $raw_value
	 * @param string      $type
	 * @return mixed
	 */
	protected static function cast_value_from_db( $raw_value, $type ) {
		if ( null === $raw_value ) {
			return null;
		}

		switch ( $type ) {
			case 'int':
			case 'integer':
				return (int) $raw_value;

			case 'float':
			case 'double':
				return (float) $raw_value;

			case 'bool':
			case 'boolean':
				return (bool) (int) $raw_value;

			case 'json':
			case 'array':
				$decoded = json_decode( $raw_value, true );
				return is_array( $decoded ) ? $decoded : array();

			case 'string':
			default:
				return (string) $raw_value;
		}
	}

	/**
	 * Format PHP value for database storage.
	 *
	 * @param mixed  $value
	 * @param string $type
	 * @return string|null
	 */
	protected static function format_value_for_db( $value, $type ) {
		if ( null === $value ) {
			return null;
		}

		switch ( $type ) {
			case 'int':
			case 'integer':
				return (string) (int) $value;

			case 'float':
			case 'double':
				return (string) (float) $value;

			case 'bool':
			case 'boolean':
				return $value ? '1' : '0';

			case 'json':
			case 'array':
				return is_array( $value ) || is_object( $value ) ? wp_json_encode( $value ) : (string) $value;

			case 'string':
			default:
				return is_scalar( $value ) ? (string) $value : '';
		}
	}

	/**
	 * Clear in-memory cache.
	 */
	public static function flush_cache() {
		self::$cache      = array();
		self::$autoloaded = false;
	}
}
