<?php
/**
 * Generic Custom Fields Core Engine for NDS HR.
 * Provides extensible database management, type casting, validation, and storage
 * for custom field definitions and entity-specific values.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Custom_Fields
 */
class NDS_HR_Custom_Fields {

	/**
	 * Supported entity types.
	 *
	 * @var array<string>
	 */
	const SUPPORTED_ENTITIES = array(
		'employee',
		'department',
		'leave',
		'attendance',
		'payroll',
	);

	/**
	 * Supported field data types.
	 *
	 * @var array<string>
	 */
	const SUPPORTED_FIELD_TYPES = array(
		'text',
		'textarea',
		'number',
		'email',
		'phone',
		'date',
		'select',
		'multiselect',
		'checkbox',
		'radio',
		'yes_no',
	);

	/**
	 * In-memory cache for field definitions: [ "{$entity}_{$field_key}" => field_object ].
	 *
	 * @var array<string, object>
	 */
	protected static $field_cache = array();

	/**
	 * Plugin instance.
	 *
	 * @var NDS_HR_Plugin|null
	 */
	protected $plugin;

	/**
	 * Constructor.
	 *
	 * @param NDS_HR_Plugin|null $plugin
	 */
	public function __construct( $plugin = null ) {
		$this->plugin = $plugin;
	}

	/* =========================================================================
	   VALIDATION & HELPER METHODS
	   ========================================================================= */

	/**
	 * Get list of currently allowed entities.
	 *
	 * @return array<string>
	 */
	public static function get_supported_entities() {
		return apply_filters( 'nds_hr_custom_fields_supported_entities', array( 'employee' ) );
	}

	/**
	 * Check if an entity name is valid.
	 *
	 * @param string $entity Target entity identifier.
	 * @return bool
	 */
	public static function is_valid_entity( $entity ) {
		$entity = sanitize_key( $entity );
		return in_array( $entity, self::get_supported_entities(), true );
	}

	/**
	 * Get list of supported field types.
	 *
	 * @return array<string>
	 */
	public static function get_supported_field_types() {
		return self::SUPPORTED_FIELD_TYPES;
	}

	/**
	 * Validate field type.
	 *
	 * @param string $type
	 * @return bool
	 */
	public static function is_valid_field_type( $type ) {
		$type = sanitize_key( $type );
		return in_array( $type, self::SUPPORTED_FIELD_TYPES, true );
	}

	/**
	 * Validate programmatic field key format (lowercase letters, numbers, underscores, 2-100 chars).
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function is_valid_field_key( $key ) {
		if ( ! is_string( $key ) || empty( $key ) ) {
			return false;
		}

		// Must match lowercase alphanumeric and underscore, start with letter or number
		return (bool) preg_match( '/^[a-z0-9][a-z0-9_]{1,99}$/', $key );
	}

	/**
	 * Safely encode settings JSON.
	 *
	 * @param mixed $settings
	 * @return string|null
	 */
	public static function encode_settings( $settings ) {
		if ( empty( $settings ) ) {
			return null;
		}

		if ( is_string( $settings ) ) {
			$decoded = json_decode( $settings, true );
			if ( json_last_error() === JSON_ERROR_NONE && ( is_array( $decoded ) || is_object( $decoded ) ) ) {
				return wp_json_encode( $decoded );
			}
			return null;
		}

		if ( is_array( $settings ) || is_object( $settings ) ) {
			return wp_json_encode( $settings );
		}

		return null;
	}

	/**
	 * Safely decode settings JSON into an associative array.
	 *
	 * @param string|null $settings_json
	 * @return array
	 */
	public static function decode_settings( $settings_json ) {
		if ( empty( $settings_json ) || ! is_string( $settings_json ) ) {
			return array();
		}

		$decoded = json_decode( $settings_json, true );
		if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
			return $decoded;
		}

		return array();
	}

	/**
	 * Check whether a field is required.
	 *
	 * @param object|array|int $field Field object, array or ID.
	 * @return bool
	 */
	public static function is_field_required( $field ) {
		if ( is_numeric( $field ) ) {
			$field = self::get_field( (int) $field );
		}

		if ( is_object( $field ) && isset( $field->is_required ) ) {
			return (bool) $field->is_required;
		}

		if ( is_array( $field ) && isset( $field['is_required'] ) ) {
			return (bool) $field['is_required'];
		}

		return false;
	}

	/**
	 * Check whether a field is active.
	 *
	 * @param object|array|int $field Field object, array or ID.
	 * @return bool
	 */
	public static function is_field_active( $field ) {
		if ( is_numeric( $field ) ) {
			$field = self::get_field( (int) $field );
		}

		if ( is_object( $field ) && isset( $field->is_active ) ) {
			return (bool) $field->is_active;
		}

		if ( is_array( $field ) && isset( $field['is_active'] ) ) {
			return (bool) $field['is_active'];
		}

		return false;
	}

	/**
	 * Validate and sanitize submitted custom field values for an entity.
	 * Only processes currently ACTIVE fields for that entity.
	 *
	 * @param array  $raw_input Submitted key-value pairs (e.g. $_POST['custom_fields']).
	 * @param string $entity    Target entity (default: 'employee').
	 * @return array|WP_Error  Sanitized [ field_key => sanitized_value ] on success, WP_Error on validation failure.
	 */
	public static function validate_submission( array $raw_input, $entity = 'employee' ) {
		$active_fields = self::get_active_fields( $entity );
		$sanitized     = array();

		foreach ( $active_fields as $field ) {
			$key       = $field->field_key;
			$label     = $field->field_label;
			$type      = $field->field_type;
			$required  = (bool) $field->is_required;
			$settings  = is_array( $field->settings ) ? $field->settings : array();
			$raw_value = isset( $raw_input[ $key ] ) ? $raw_input[ $key ] : null;

			// Check if value is logically empty
			$is_empty = false;
			if ( null === $raw_value ) {
				$is_empty = true;
			} elseif ( is_string( $raw_value ) && '' === trim( $raw_value ) ) {
				$is_empty = true;
			} elseif ( is_array( $raw_value ) ) {
				$filtered = array_filter( $raw_value, function( $v ) {
					return is_scalar( $v ) && '' !== trim( (string) $v );
				} );
				if ( empty( $filtered ) ) {
					$is_empty = true;
				}
			}

			// Required check
			if ( $required && $is_empty ) {
				return new WP_Error(
					'required_custom_field',
					sprintf(
						/* translators: %s: field label */
						__( '%s is required.', 'nds-hr' ),
						$label
					)
				);
			}

			// If empty and not required, set to null and continue
			if ( $is_empty ) {
				$sanitized[ $key ] = null;
				continue;
			}

			// Validate and sanitize based on field type
			switch ( $type ) {
				case 'text':
					$sanitized[ $key ] = sanitize_text_field( wp_unslash( (string) $raw_value ) );
					break;

				case 'textarea':
					$sanitized[ $key ] = sanitize_textarea_field( wp_unslash( (string) $raw_value ) );
					break;

				case 'number':
					$val_str = trim( (string) wp_unslash( $raw_value ) );
					if ( ! is_numeric( $val_str ) ) {
						return new WP_Error(
							'invalid_number',
							sprintf(
								/* translators: %s: field label */
								__( '%s must be a valid number.', 'nds-hr' ),
								$label
							)
						);
					}
					$sanitized[ $key ] = $val_str + 0;
					break;

				case 'email':
					$val_str = sanitize_email( wp_unslash( (string) $raw_value ) );
					if ( ! is_email( $val_str ) ) {
						return new WP_Error(
							'invalid_email',
							sprintf(
								/* translators: %s: field label */
								__( '%s must be a valid email address.', 'nds-hr' ),
								$label
							)
						);
					}
					$sanitized[ $key ] = $val_str;
					break;

				case 'phone':
					$sanitized[ $key ] = sanitize_text_field( wp_unslash( (string) $raw_value ) );
					break;

				case 'date':
					$val_str  = trim( sanitize_text_field( wp_unslash( (string) $raw_value ) ) );
					$sql_date = NDS_HR_Security::parse_date_to_sql( $val_str );
					if ( ! $sql_date ) {
						return new WP_Error(
							'invalid_date',
							sprintf(
								/* translators: %s: field label */
								__( '%s must be a valid date.', 'nds-hr' ),
								$label
							)
						);
					}
					$sanitized[ $key ] = $sql_date;
					break;

				case 'select':
				case 'radio':
					$val_str = sanitize_text_field( wp_unslash( (string) $raw_value ) );
					$allowed_options = isset( $settings['options'] ) && is_array( $settings['options'] ) ? $settings['options'] : array();
					$allowed_values  = array();
					foreach ( $allowed_options as $opt ) {
						if ( is_array( $opt ) ) {
							if ( isset( $opt['value'] ) && '' !== (string) $opt['value'] ) {
								$allowed_values[] = (string) $opt['value'];
							}
							if ( isset( $opt['label'] ) && '' !== (string) $opt['label'] ) {
								$allowed_values[] = (string) $opt['label'];
							}
						} elseif ( is_object( $opt ) ) {
							if ( isset( $opt->value ) && '' !== (string) $opt->value ) {
								$allowed_values[] = (string) $opt->value;
							}
							if ( isset( $opt->label ) && '' !== (string) $opt->label ) {
								$allowed_values[] = (string) $opt->label;
							}
						} elseif ( is_string( $opt ) && '' !== $opt ) {
							$allowed_values[] = $opt;
						}
					}

					if ( ! empty( $allowed_values ) && ! in_array( $val_str, $allowed_values, true ) ) {
						return new WP_Error(
							'invalid_option',
							sprintf(
								/* translators: %s: field label */
								__( 'Invalid option selected for %s.', 'nds-hr' ),
								$label
							)
						);
					}
					$sanitized[ $key ] = $val_str;
					break;

				case 'multiselect':
				case 'checkbox':
					$raw_arr = is_array( $raw_value ) ? $raw_value : array( $raw_value );
					$allowed_options = isset( $settings['options'] ) && is_array( $settings['options'] ) ? $settings['options'] : array();
					$allowed_values  = array();
					foreach ( $allowed_options as $opt ) {
						if ( is_array( $opt ) ) {
							if ( isset( $opt['value'] ) && '' !== (string) $opt['value'] ) {
								$allowed_values[] = (string) $opt['value'];
							}
							if ( isset( $opt['label'] ) && '' !== (string) $opt['label'] ) {
								$allowed_values[] = (string) $opt['label'];
							}
						} elseif ( is_object( $opt ) ) {
							if ( isset( $opt->value ) && '' !== (string) $opt->value ) {
								$allowed_values[] = (string) $opt->value;
							}
							if ( isset( $opt->label ) && '' !== (string) $opt->label ) {
								$allowed_values[] = (string) $opt->label;
							}
						} elseif ( is_string( $opt ) && '' !== $opt ) {
							$allowed_values[] = $opt;
						}
					}

					$clean_arr = array();
					foreach ( $raw_arr as $item ) {
						$clean_item = sanitize_text_field( wp_unslash( (string) $item ) );
						if ( '' === $clean_item ) {
							continue;
						}
						if ( ! empty( $allowed_values ) && ! in_array( $clean_item, $allowed_values, true ) ) {
							return new WP_Error(
								'invalid_option',
								sprintf(
									/* translators: %s: field label */
									__( 'Invalid option selected for %s.', 'nds-hr' ),
									$label
								)
							);
						}
						$clean_arr[] = $clean_item;
					}
					$sanitized[ $key ] = $clean_arr;
					break;

				case 'yes_no':
					$sanitized[ $key ] = ( '1' === (string) $raw_value || 'yes' === strtolower( (string) $raw_value ) || true === $raw_value || 1 === $raw_value ) ? 1 : 0;
					break;

				default:
					$sanitized[ $key ] = sanitize_text_field( wp_unslash( (string) $raw_value ) );
					break;
			}
		}

		return $sanitized;
	}

	/* =========================================================================
	   FIELD DEFINITION CRUD OPERATIONS
	   ========================================================================= */

	/**
	 * Create a new custom field definition.
	 *
	 * @param array $data Field definition data.
	 * @return int|WP_Error Inserted field ID on success, WP_Error on validation/db failure.
	 */
	public static function create_field( array $data ) {
		global $wpdb;

		// 1. Validate Entity
		$entity = isset( $data['entity'] ) ? sanitize_key( $data['entity'] ) : 'employee';
		if ( ! self::is_valid_entity( $entity ) ) {
			return new WP_Error(
				'invalid_entity',
				sprintf(
					/* translators: %s: entity name */
					__( 'Unsupported or invalid entity: "%s".', 'nds-hr' ),
					esc_html( $entity )
				)
			);
		}

		// 2. Validate Field Key
		$field_key = isset( $data['field_key'] ) ? strtolower( trim( $data['field_key'] ) ) : '';
		if ( ! self::is_valid_field_key( $field_key ) ) {
			return new WP_Error(
				'invalid_field_key',
				__( 'Field key must contain only lowercase letters, numbers, and underscores (e.g. fingerprint_code).', 'nds-hr' )
			);
		}

		// Check uniqueness within the same entity
		$table = NDS_HR_Database::custom_fields_table();
		$existing_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE entity = %s AND field_key = %s LIMIT 1",
				$entity,
				$field_key
			)
		);
		if ( $existing_id ) {
			return new WP_Error(
				'duplicate_field_key',
				sprintf(
					/* translators: 1: field key, 2: entity */
					__( 'Field key "%1$s" already exists for entity "%2$s".', 'nds-hr' ),
					esc_html( $field_key ),
					esc_html( $entity )
				)
			);
		}

		// 3. Validate Field Type
		$field_type = isset( $data['field_type'] ) ? sanitize_key( $data['field_type'] ) : '';
		if ( ! self::is_valid_field_type( $field_type ) ) {
			return new WP_Error(
				'invalid_field_type',
				sprintf(
					/* translators: %s: field type */
					__( 'Invalid field type "%s".', 'nds-hr' ),
					esc_html( $field_type )
				)
			);
		}

		// 4. Validate & Sanitize Field Label
		$field_label = isset( $data['field_label'] ) ? sanitize_text_field( $data['field_label'] ) : '';
		if ( empty( $field_label ) ) {
			return new WP_Error( 'missing_field_label', __( 'Field label is required.', 'nds-hr' ) );
		}

		$description = isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : null;
		$is_required = ! empty( $data['is_required'] ) ? 1 : 0;
		$is_active   = isset( $data['is_active'] ) ? ( ! empty( $data['is_active'] ) ? 1 : 0 ) : 1;
		$sort_order  = isset( $data['sort_order'] ) ? (int) $data['sort_order'] : 0;
		$settings    = isset( $data['settings'] ) ? self::encode_settings( $data['settings'] ) : null;

		$insert_data = array(
			'entity'      => $entity,
			'field_key'   => $field_key,
			'field_label' => $field_label,
			'field_type'  => $field_type,
			'description' => $description,
			'is_required' => $is_required,
			'is_active'   => $is_active,
			'sort_order'  => $sort_order,
			'settings'    => $settings,
			'created_at'  => current_time( 'mysql' ),
			'updated_at'  => current_time( 'mysql' ),
		);

		$format = array( '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s' );

		$result = $wpdb->insert( $table, $insert_data, $format );

		if ( false === $result ) {
			return new WP_Error( 'db_insert_error', __( 'Could not create custom field record.', 'nds-hr' ) );
		}

		$field_id = (int) $wpdb->insert_id;
		self::flush_cache();

		return $field_id;
	}

	/**
	 * Update an existing custom field definition.
	 *
	 * @param int   $id   Field ID.
	 * @param array $data Updated field properties.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public static function update_field( $id, array $data ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return new WP_Error( 'invalid_id', __( 'Invalid custom field ID.', 'nds-hr' ) );
		}

		$existing = self::get_field( $id );
		if ( ! $existing ) {
			return new WP_Error( 'field_not_found', __( 'Custom field not found.', 'nds-hr' ) );
		}

		$table = NDS_HR_Database::custom_fields_table();
		$update_data   = array();
		$update_format = array();

		// Entity update
		if ( isset( $data['entity'] ) ) {
			$entity = sanitize_key( $data['entity'] );
			if ( ! self::is_valid_entity( $entity ) ) {
				return new WP_Error( 'invalid_entity', __( 'Unsupported entity identifier.', 'nds-hr' ) );
			}
			$update_data['entity'] = $entity;
			$update_format[]       = '%s';
		} else {
			$entity = $existing->entity;
		}

		// Field key update
		if ( isset( $data['field_key'] ) ) {
			$field_key = strtolower( trim( $data['field_key'] ) );
			if ( ! self::is_valid_field_key( $field_key ) ) {
				return new WP_Error( 'invalid_field_key', __( 'Invalid field key format.', 'nds-hr' ) );
			}

			// Check unique constraint if changed
			if ( $field_key !== $existing->field_key ) {
				$duplicate = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT id FROM {$table} WHERE entity = %s AND field_key = %s AND id != %d LIMIT 1",
						$entity,
						$field_key,
						$id
					)
				);
				if ( $duplicate ) {
					return new WP_Error( 'duplicate_field_key', __( 'Field key is already in use for this entity.', 'nds-hr' ) );
				}
			}

			$update_data['field_key'] = $field_key;
			$update_format[]          = '%s';
		}

		// Field label update
		if ( isset( $data['field_label'] ) ) {
			$field_label = sanitize_text_field( $data['field_label'] );
			if ( empty( $field_label ) ) {
				return new WP_Error( 'missing_field_label', __( 'Field label cannot be empty.', 'nds-hr' ) );
			}
			$update_data['field_label'] = $field_label;
			$update_format[]            = '%s';
		}

		// Field type update
		if ( isset( $data['field_type'] ) ) {
			$field_type = sanitize_key( $data['field_type'] );
			if ( ! self::is_valid_field_type( $field_type ) ) {
				return new WP_Error( 'invalid_field_type', __( 'Invalid field type.', 'nds-hr' ) );
			}
			$update_data['field_type'] = $field_type;
			$update_format[]           = '%s';
		}

		// Description update
		if ( array_key_exists( 'description', $data ) ) {
			$update_data['description'] = ! empty( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : null;
			$update_format[]            = '%s';
		}

		// is_required
		if ( isset( $data['is_required'] ) ) {
			$update_data['is_required'] = ! empty( $data['is_required'] ) ? 1 : 0;
			$update_format[]            = '%d';
		}

		// is_active
		if ( isset( $data['is_active'] ) ) {
			$update_data['is_active'] = ! empty( $data['is_active'] ) ? 1 : 0;
			$update_format[]          = '%d';
		}

		// sort_order
		if ( isset( $data['sort_order'] ) ) {
			$update_data['sort_order'] = (int) $data['sort_order'];
			$update_format[]           = '%d';
		}

		// settings JSON
		if ( array_key_exists( 'settings', $data ) ) {
			$update_data['settings'] = self::encode_settings( $data['settings'] );
			$update_format[]         = '%s';
		}

		if ( empty( $update_data ) ) {
			return true;
		}

		$update_data['updated_at'] = current_time( 'mysql' );
		$update_format[]           = '%s';

		$result = $wpdb->update(
			$table,
			$update_data,
			array( 'id' => $id ),
			$update_format,
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_update_error', __( 'Could not update custom field.', 'nds-hr' ) );
		}

		self::flush_cache();
		return true;
	}

	/**
	 * Delete a custom field definition and its associated values.
	 *
	 * @param int $id Field ID.
	 * @return bool True on success, false on failure.
	 */
	public static function delete_field( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		$fields_table = NDS_HR_Database::custom_fields_table();
		$values_table = NDS_HR_Database::custom_field_values_table();

		// 1. Delete associated values
		$wpdb->delete( $values_table, array( 'field_id' => $id ), array( '%d' ) );

		// 2. Delete field definition
		$result = $wpdb->delete( $fields_table, array( 'id' => $id ), array( '%d' ) );

		self::flush_cache();

		return false !== $result && $result > 0;
	}

	/**
	 * Retrieve a single custom field definition by ID.
	 *
	 * @param int $id Field ID.
	 * @return object|null Field object with decoded settings or null.
	 */
	public static function get_field( $id ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return null;
		}

		$table = NDS_HR_Database::custom_fields_table();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id ) );

		if ( ! $row ) {
			return null;
		}

		return self::hydrate_field_object( $row );
	}

	/**
	 * Retrieve a custom field definition by programmatic key and entity.
	 *
	 * @param string $field_key Field key.
	 * @param string $entity    Target entity (default: 'employee').
	 * @return object|null Field object or null.
	 */
	public static function get_field_by_key( $field_key, $entity = 'employee' ) {
		global $wpdb;

		$entity    = sanitize_key( $entity );
		$field_key = sanitize_key( $field_key );

		$cache_key = "{$entity}_{$field_key}";
		if ( isset( self::$field_cache[ $cache_key ] ) ) {
			return self::$field_cache[ $cache_key ];
		}

		$table = NDS_HR_Database::custom_fields_table();
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE entity = %s AND field_key = %s LIMIT 1",
				$entity,
				$field_key
			)
		);

		if ( ! $row ) {
			return null;
		}

		$field = self::hydrate_field_object( $row );
		self::$field_cache[ $cache_key ] = $field;

		return $field;
	}

	/**
	 * Retrieve fields with flexible filtering and sorting.
	 *
	 * @param array $args Query parameters.
	 * @return array<object> List of field objects.
	 */
	public static function get_fields( array $args = array() ) {
		global $wpdb;

		$defaults = array(
			'entity'    => '',
			'is_active' => null,
			'orderby'   => 'sort_order',
			'order'     => 'ASC',
			'limit'     => 0,
			'offset'    => 0,
		);

		$params = wp_parse_args( $args, $defaults );
		$table  = NDS_HR_Database::custom_fields_table();

		$where_clauses = array( '1=1' );
		$query_args    = array();

		if ( ! empty( $params['entity'] ) ) {
			$where_clauses[] = 'entity = %s';
			$query_args[]    = sanitize_key( $params['entity'] );
		}

		if ( null !== $params['is_active'] ) {
			$where_clauses[] = 'is_active = %d';
			$query_args[]    = ! empty( $params['is_active'] ) ? 1 : 0;
		}

		$allowed_order_by = array( 'sort_order', 'id', 'field_key', 'field_label', 'created_at' );
		$orderby = in_array( $params['orderby'], $allowed_order_by, true ) ? $params['orderby'] : 'sort_order';
		$order   = 'DESC' === strtoupper( $params['order'] ) ? 'DESC' : 'ASC';

		$where_sql = implode( ' AND ', $where_clauses );
		$sql       = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order}, id ASC";

		if ( ! empty( $params['limit'] ) && (int) $params['limit'] > 0 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', absint( $params['limit'] ), absint( $params['offset'] ) );
		}

		if ( ! empty( $query_args ) ) {
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $query_args ) );
		} else {
			$results = $wpdb->get_results( $sql );
		}

		if ( empty( $results ) || ! is_array( $results ) ) {
			return array();
		}

		return array_map( array( __CLASS__, 'hydrate_field_object' ), $results );
	}

	/**
	 * Retrieve active custom fields for a specific entity.
	 *
	 * @param string $entity Target entity (default: 'employee').
	 * @return array<object>
	 */
	public static function get_active_fields( $entity = 'employee' ) {
		return self::get_fields(
			array(
				'entity'    => $entity,
				'is_active' => 1,
				'orderby'   => 'sort_order',
				'order'     => 'ASC',
			)
		);
	}

	/**
	 * Retrieve all custom fields for an entity (optionally active only).
	 *
	 * @param string $entity      Target entity.
	 * @param bool   $active_only Whether to retrieve only active fields.
	 * @return array<object>
	 */
	public static function get_entity_fields( $entity = 'employee', $active_only = false ) {
		$args = array(
			'entity'  => $entity,
			'orderby' => 'sort_order',
			'order'   => 'ASC',
		);

		if ( $active_only ) {
			$args['is_active'] = 1;
		}

		return self::get_fields( $args );
	}

	/* =========================================================================
	   VALUE CRUD OPERATIONS
	   ========================================================================= */

	/**
	 * Set / Save custom field value for an entity instance.
	 *
	 * @param int         $field_id  Field ID.
	 * @param int         $entity_id Entity record ID (e.g. employee ID).
	 * @param mixed       $value     Value to store.
	 * @param string|null $entity    Optional entity scope to verify against.
	 * @return bool True on success, false on failure.
	 */
	public static function set_value( $field_id, $entity_id, $value, $entity = null ) {
		global $wpdb;

		$field_id  = absint( $field_id );
		$entity_id = absint( $entity_id );

		if ( ! $field_id || ! $entity_id ) {
			return false;
		}

		$field = self::get_field( $field_id );
		if ( ! $field ) {
			return false;
		}

		// Entity isolation guard
		if ( ! empty( $entity ) && $field->entity !== sanitize_key( $entity ) ) {
			return false;
		}

		$table    = NDS_HR_Database::custom_field_values_table();
		$db_value = self::format_value_for_db( $value, $field->field_type );

		$existing_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE field_id = %d AND entity_id = %d LIMIT 1",
				$field_id,
				$entity_id
			)
		);

		if ( $existing_id ) {
			$result = $wpdb->update(
				$table,
				array(
					'value'      => $db_value,
					'updated_at' => current_time( 'mysql' ),
				),
				array( 'id' => $existing_id ),
				array( '%s', '%s' ),
				array( '%d' )
			);
			return false !== $result;
		}

		$result = $wpdb->insert(
			$table,
			array(
				'field_id'   => $field_id,
				'entity_id'  => $entity_id,
				'value'      => $db_value,
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%s' )
		);

		return false !== $result;
	}

	/**
	 * Set / Save custom field value by programmatic field key and entity.
	 *
	 * @param string $field_key Field key (e.g. 'fingerprint_code').
	 * @param int    $entity_id Entity record ID.
	 * @param mixed  $value     Value.
	 * @param string $entity    Target entity (default: 'employee').
	 * @return bool
	 */
	public static function set_value_by_key( $field_key, $entity_id, $value, $entity = 'employee' ) {
		$field = self::get_field_by_key( $field_key, $entity );
		if ( ! $field ) {
			return false;
		}

		return self::set_value( $field->id, $entity_id, $value, $entity );
	}

	/**
	 * Retrieve a custom field value for an entity instance.
	 *
	 * @param int         $field_id  Field ID.
	 * @param int         $entity_id Entity record ID.
	 * @param mixed       $default   Default value if not set.
	 * @param string|null $entity    Optional entity scope to verify against.
	 * @return mixed Typed value.
	 */
	public static function get_value( $field_id, $entity_id, $default = null, $entity = null ) {
		global $wpdb;

		$field_id  = absint( $field_id );
		$entity_id = absint( $entity_id );

		if ( ! $field_id || ! $entity_id ) {
			return $default;
		}

		$field = self::get_field( $field_id );
		if ( ! $field ) {
			return $default;
		}

		// Entity isolation guard
		if ( ! empty( $entity ) && $field->entity !== sanitize_key( $entity ) ) {
			return $default;
		}

		$table = NDS_HR_Database::custom_field_values_table();
		$raw   = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT value FROM {$table} WHERE field_id = %d AND entity_id = %d LIMIT 1",
				$field_id,
				$entity_id
			)
		);

		if ( null === $raw ) {
			return $default;
		}

		return self::cast_value_from_db( $raw, $field->field_type );
	}

	/**
	 * Retrieve a custom field value by field key.
	 *
	 * @param string $field_key Field key.
	 * @param int    $entity_id Entity record ID.
	 * @param string $entity    Target entity.
	 * @param mixed  $default   Default fallback.
	 * @return mixed
	 */
	public static function get_value_by_key( $field_key, $entity_id, $entity = 'employee', $default = null ) {
		$field = self::get_field_by_key( $field_key, $entity );
		if ( ! $field ) {
			return $default;
		}

		return self::get_value( $field->id, $entity_id, $default, $entity );
	}

	/**
	 * Retrieve all custom field values for an entity record as an associative map [ field_key => typed_value ].
	 *
	 * @param int    $entity_id   Entity record ID.
	 * @param string $entity      Target entity.
	 * @param bool   $active_only Whether to retrieve only active fields.
	 * @return array<string, mixed>
	 */
	public static function get_values( $entity_id, $entity = 'employee', $active_only = false ) {
		global $wpdb;

		$entity_id = absint( $entity_id );
		if ( ! $entity_id ) {
			return array();
		}

		$fields_table = NDS_HR_Database::custom_fields_table();
		$values_table = NDS_HR_Database::custom_field_values_table();

		$active_sql = $active_only ? ' AND f.is_active = 1' : '';

		$query = $wpdb->prepare(
			"SELECT f.field_key, f.field_type, v.value 
			 FROM {$fields_table} f
			 LEFT JOIN {$values_table} v ON f.id = v.field_id AND v.entity_id = %d
			 WHERE f.entity = %s{$active_sql}
			 ORDER BY f.sort_order ASC, f.id ASC",
			$entity_id,
			sanitize_key( $entity )
		);

		$results = $wpdb->get_results( $query );
		$values  = array();

		if ( ! empty( $results ) && is_array( $results ) ) {
			foreach ( $results as $row ) {
				$values[ $row->field_key ] = self::cast_value_from_db( $row->value, $row->field_type );
			}
		}

		return $values;
	}

	/**
	 * Batch set/update multiple custom field values for an entity record.
	 *
	 * @param int                  $entity_id Entity record ID.
	 * @param array<string, mixed> $values    Associative array of [ field_key => value ].
	 * @param string               $entity    Target entity.
	 * @return bool True if all succeeded.
	 */
	public static function set_values( $entity_id, array $values, $entity = 'employee' ) {
		$entity_id = absint( $entity_id );
		if ( ! $entity_id || empty( $values ) ) {
			return false;
		}

		$all_success = true;
		foreach ( $values as $field_key => $val ) {
			$saved = self::set_value_by_key( $field_key, $entity_id, $val, $entity );
			if ( ! $saved ) {
				$all_success = false;
			}
		}

		return $all_success;
	}

	/**
	 * Delete a single custom field value for an entity record.
	 *
	 * @param int         $field_id  Field ID.
	 * @param int         $entity_id Entity record ID.
	 * @param string|null $entity    Optional entity scope.
	 * @return bool True on success, false on failure.
	 */
	public static function delete_value( $field_id, $entity_id, $entity = null ) {
		global $wpdb;

		$field_id  = absint( $field_id );
		$entity_id = absint( $entity_id );

		if ( ! $field_id || ! $entity_id ) {
			return false;
		}

		if ( ! empty( $entity ) ) {
			$field = self::get_field( $field_id );
			if ( ! $field || $field->entity !== sanitize_key( $entity ) ) {
				return false;
			}
		}

		$table = NDS_HR_Database::custom_field_values_table();
		$res   = $wpdb->delete(
			$table,
			array(
				'field_id'  => $field_id,
				'entity_id' => $entity_id,
			),
			array( '%d', '%d' )
		);

		return false !== $res;
	}

	/**
	 * Delete a custom field value by field key and entity ID.
	 *
	 * @param string $field_key Field key.
	 * @param int    $entity_id Entity record ID.
	 * @param string $entity    Target entity (default: 'employee').
	 * @return bool
	 */
	public static function delete_value_by_key( $field_key, $entity_id, $entity = 'employee' ) {
		$field = self::get_field_by_key( $field_key, $entity );
		if ( ! $field ) {
			return false;
		}

		return self::delete_value( $field->id, $entity_id, $entity );
	}

	/**
	 * Delete all custom field values for a specific entity record (e.g. on employee deletion).
	 *
	 * @param int    $entity_id Entity record ID.
	 * @param string $entity    Target entity.
	 * @return bool
	 */
	public static function delete_all_entity_values( $entity_id, $entity = 'employee' ) {
		global $wpdb;

		$entity_id = absint( $entity_id );
		if ( ! $entity_id ) {
			return false;
		}

		$fields_table = NDS_HR_Database::custom_fields_table();
		$values_table = NDS_HR_Database::custom_field_values_table();

		// Delete values matching fields belonging to entity
		$query = $wpdb->prepare(
			"DELETE v FROM {$values_table} v
			 INNER JOIN {$fields_table} f ON v.field_id = f.id
			 WHERE v.entity_id = %d AND f.entity = %s",
			$entity_id,
			sanitize_key( $entity )
		);

		$result = $wpdb->query( $query );

		return false !== $result;
	}

	/* =========================================================================
	   DATA CASTING & SANITIZATION HELPERS
	   ========================================================================= */

	/**
	 * Format PHP value for safe database text storage based on field type.
	 *
	 * @param mixed  $value      Incoming PHP value.
	 * @param string $field_type Data type identifier.
	 * @return string|null
	 */
	public static function format_value_for_db( $value, $field_type ) {
		if ( null === $value ) {
			return null;
		}

		switch ( $field_type ) {
			case 'number':
				if ( '' === $value ) {
					return null;
				}
				return is_numeric( $value ) ? (string) ( $value + 0 ) : null;

			case 'yes_no':
				return ! empty( $value ) ? '1' : '0';

			case 'multiselect':
			case 'checkbox':
				if ( is_array( $value ) ) {
					$sanitized = array_map( 'sanitize_text_field', $value );
					return wp_json_encode( array_values( array_filter( $sanitized ) ) );
				}
				if ( is_string( $value ) && ! empty( $value ) ) {
					return wp_json_encode( array( sanitize_text_field( $value ) ) );
				}
				return wp_json_encode( array() );

			case 'email':
				return sanitize_email( $value );

			case 'phone':
				return sanitize_text_field( $value );

			case 'date':
				$cleaned = sanitize_text_field( $value );
				return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $cleaned ) ? $cleaned : null;

			case 'textarea':
				return sanitize_textarea_field( $value );

			case 'text':
			case 'select':
			case 'radio':
			default:
				return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
		}
	}

	/**
	 * Cast raw database string to typed PHP structure.
	 *
	 * @param string|null $raw_value  Raw DB string.
	 * @param string      $field_type Data type identifier.
	 * @return mixed Typed value.
	 */
	public static function cast_value_from_db( $raw_value, $field_type ) {
		if ( null === $raw_value ) {
			return null;
		}

		switch ( $field_type ) {
			case 'number':
				if ( '' === $raw_value ) {
					return null;
				}
				return is_numeric( $raw_value ) ? ( $raw_value + 0 ) : null;

			case 'yes_no':
				return '1' === (string) $raw_value || 1 === (int) $raw_value;

			case 'multiselect':
			case 'checkbox':
				$decoded = json_decode( $raw_value, true );
				return is_array( $decoded ) ? $decoded : array();

			case 'date':
			case 'email':
			case 'phone':
			case 'textarea':
			case 'text':
			case 'select':
			case 'radio':
			default:
				return (string) $raw_value;
		}
	}

	/**
	 * Hydrate a raw DB row into a clean field object.
	 *
	 * @param object $row
	 * @return object
	 */
	protected static function hydrate_field_object( $row ) {
		$field = clone $row;
		$field->id          = (int) $field->id;
		$field->is_required = (bool) (int) $field->is_required;
		$field->is_active   = (bool) (int) $field->is_active;
		$field->sort_order  = (int) $field->sort_order;
		$field->settings    = self::decode_settings( $field->settings );
		return $field;
	}

	/**
	 * Flush in-memory field definition cache.
	 */
	public static function flush_cache() {
		self::$field_cache = array();
	}
}
