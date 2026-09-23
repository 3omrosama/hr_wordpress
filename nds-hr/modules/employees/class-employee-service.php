<?php
/**
 * Employee Business Logic Service.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Employee_Service
 */
class NDS_HR_Employee_Service {

	/**
	 * Repository instance.
	 *
	 * @var NDS_HR_Employee_Repository
	 */
	protected $repository;

	/**
	 * Constructor.
	 *
	 * @param NDS_HR_Employee_Repository $repository
	 */
	public function __construct( NDS_HR_Employee_Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Create a new employee with business rule validations and optional NDS HR account creation.
	 *
	 * @param array $data Sanitized employee data.
	 * @param array $account_options Options for NDS HR User association.
	 * @return int|array|WP_Error Employee DB ID or array with account details on success, or WP_Error.
	 */
	public function create_employee( array $data, array $account_options = array() ) {
		global $wpdb;

		// Validation of phone format errors if present
		if ( ! empty( $data['_phone_error'] ) && is_wp_error( $data['_phone_error'] ) ) {
			return $data['_phone_error'];
		}
		unset( $data['_phone_error'], $data['remove_profile_photo'], $data['remove_contract_document'], $data['display_name'] );

		// Validation of required fields
		if ( empty( $data['first_name'] ) || empty( $data['last_name'] ) ) {
			return new WP_Error( 'missing_name', __( 'First name and last name are required.', 'nds-hr' ) );
		}

		if ( empty( $data['email'] ) || ! is_email( $data['email'] ) ) {
			return new WP_Error( 'invalid_email', __( 'A valid email address is required.', 'nds-hr' ) );
		}

		// Check for duplicate employee email
		$table = NDS_HR_Database::employees_table();
		$existing = $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s LIMIT 1", $data['email'] )
		);
		if ( $existing ) {
			return new WP_Error( 'duplicate_email', __( 'An employee with this email address already exists.', 'nds-hr' ) );
		}

		// Handle Independent NDS HR User account integration
		$linked_user_id     = 0;
		$account_info_badge = null;

		$should_create_account = ! empty( $account_options['create_account'] ) || ( isset( $account_options['action'] ) && 'create' === $account_options['action'] );

		if ( $should_create_account ) {
			// Validate passwords match if manually provided
			$pw         = $account_options['password'] ?? '';
			$confirm_pw = $account_options['confirm_password'] ?? '';
			if ( ! empty( $pw ) && ! empty( $confirm_pw ) && $pw !== $confirm_pw ) {
				return new WP_Error( 'password_mismatch', __( 'The entered passwords do not match.', 'nds-hr' ) );
			}

			$role_slug = ! empty( $account_options['role'] ) && in_array( $account_options['role'], array( 'hr_employee', 'hr_admin' ), true )
				? $account_options['role']
				: 'hr_employee';

			// Create independent NDS HR user
			$user_result = NDS_HR_Auth::create_employee_user_account(
				array(
					'username'                => ! empty( $account_options['username'] ) ? $account_options['username'] : sanitize_user( explode( '@', $data['email'] )[0] ),
					'email'                   => $data['email'],
					'password'                => $pw,
					'role'                    => $role_slug,
					'require_password_change' => ! empty( $account_options['require_password_change'] ),
					'first_name'              => $data['first_name'],
					'last_name'               => $data['last_name'],
					'full_name'               => $data['full_name'],
					'send_notification'       => ! empty( $account_options['send_notification'] ),
				)
			);

			if ( is_wp_error( $user_result ) ) {
				return $user_result;
			}

			$linked_user_id     = $user_result['user_id'];
			$account_info_badge = $user_result;

			// Audit logging: NEVER record passwords
			NDS_HR_Audit_Logger::log(
				'user_account_created',
				'user',
				$linked_user_id,
				array(),
				array(
					'username'                => $user_result['username'],
					'email'                   => $data['email'],
					'role'                    => $role_slug,
					'require_password_change' => ! empty( $account_options['require_password_change'] ),
				)
			);
		}

		if ( $linked_user_id > 0 ) {
			$data['hr_user_id'] = $linked_user_id;
			$data['user_id']    = $linked_user_id;
		}

		// Insert into database
		$employee_id = $this->repository->insert( $data );

		if ( ! $employee_id ) {
			return new WP_Error( 'insert_failed', __( 'Failed to save employee record in database.', 'nds-hr' ) );
		}

		// Update link in employees table and user association
		if ( $linked_user_id > 0 ) {
			NDS_HR_Auth::link_user_to_employee( $linked_user_id, $employee_id );
		}

		// Audit Log
		NDS_HR_Audit_Logger::log(
			'employee_created',
			'employee',
			$employee_id,
			array(),
			array(
				'employee_id'   => $data['employee_id'] ?? '',
				'full_name'     => $data['full_name'],
				'email'         => $data['email'],
				'department'    => $data['department_id'],
				'status'        => $data['employment_status'],
				'contract_type' => $data['contract_type'] ?? 'permanent',
			)
		);

		if ( ! empty( $account_info_badge ) ) {
			return array(
				'employee_db_id'          => $employee_id,
				'employee_code'           => $data['employee_id'] ?? '',
				'username'                => $account_info_badge['username'],
				'temporary_password'      => $account_info_badge['temporary_password'],
				'require_password_change' => ! empty( $account_info_badge['require_password_change'] ),
			);
		}

		return $employee_id;
	}

	/**
	 * Update an existing employee.
	 *
	 * @param int   $id Employee DB primary ID.
	 * @param array $data Sanitized employee data.
	 * @param array $account_options Optional account updates.
	 * @return true|WP_Error
	 */
	public function update_employee( $id, array $data, array $account_options = array() ) {
		global $wpdb;

		// Validation of phone format errors if present
		if ( ! empty( $data['_phone_error'] ) && is_wp_error( $data['_phone_error'] ) ) {
			return $data['_phone_error'];
		}
		unset( $data['_phone_error'], $data['remove_profile_photo'], $data['remove_contract_document'], $data['display_name'] );

		$existing = $this->repository->find_by_id( $id );
		if ( ! $existing ) {
			return new WP_Error( 'not_found', __( 'Employee not found.', 'nds-hr' ) );
		}

		// Check duplicate email for other employees
		$table = NDS_HR_Database::employees_table();
		$duplicate = $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s AND id != %d LIMIT 1", $data['email'], $id )
		);
		if ( $duplicate ) {
			return new WP_Error( 'duplicate_email', __( 'Another employee already uses this email address.', 'nds-hr' ) );
		}

		// Handle Account updates if employee has linked HR user
		$target_hr_user_id = ! empty( $existing->hr_user_id ) ? (int) $existing->hr_user_id : ( ! empty( $existing->user_id ) ? (int) $existing->user_id : 0 );

		if ( $target_hr_user_id > 0 && ! empty( $account_options ) ) {
			$account_update_payload = array(
				'email'      => $data['email'],
				'first_name' => $data['first_name'],
				'last_name'  => $data['last_name'],
				'full_name'  => $data['full_name'],
			);

			if ( ! empty( $account_options['role'] ) ) {
				$account_update_payload['role'] = in_array( $account_options['role'], array( 'hr_employee', 'hr_admin' ), true ) ? $account_options['role'] : 'hr_employee';
			}

			if ( ! empty( $account_options['password'] ) ) {
				if ( ! empty( $account_options['confirm_password'] ) && $account_options['password'] !== $account_options['confirm_password'] ) {
					return new WP_Error( 'password_mismatch', __( 'The entered passwords do not match.', 'nds-hr' ) );
				}
				$account_update_payload['password'] = $account_options['password'];
			}

			if ( isset( $account_options['require_password_change'] ) ) {
				$account_update_payload['require_password_change'] = ! empty( $account_options['require_password_change'] ) ? 1 : 0;
			}

			$user_update_res = NDS_HR_Auth::update_employee_user_account( $target_hr_user_id, $account_update_payload );
			if ( is_wp_error( $user_update_res ) ) {
				return $user_update_res;
			}
		} elseif ( 0 === $target_hr_user_id && ! empty( $account_options['create_account'] ) ) {
			// Account creation for existing employee
			$role_slug = ! empty( $account_options['role'] ) && in_array( $account_options['role'], array( 'hr_employee', 'hr_admin' ), true ) ? $account_options['role'] : 'hr_employee';
			$pw        = $account_options['password'] ?? '';

			$user_result = NDS_HR_Auth::create_employee_user_account(
				array(
					'username'                => ! empty( $account_options['username'] ) ? $account_options['username'] : sanitize_user( explode( '@', $data['email'] )[0] ),
					'email'                   => $data['email'],
					'password'                => $pw,
					'role'                    => $role_slug,
					'require_password_change' => ! empty( $account_options['require_password_change'] ),
					'first_name'              => $data['first_name'],
					'last_name'               => $data['last_name'],
					'full_name'               => $data['full_name'],
				)
			);

			if ( is_wp_error( $user_result ) ) {
				return $user_result;
			}

			$data['hr_user_id'] = $user_result['user_id'];
			$data['user_id']    = $user_result['user_id'];
			NDS_HR_Auth::link_user_to_employee( $user_result['user_id'], $id );
		}

		$old_values = (array) $existing;
		$updated    = $this->repository->update( $id, $data );

		if ( false === $updated ) {
			return new WP_Error( 'update_failed', __( 'Could not update employee record.', 'nds-hr' ) );
		}

		// Audit Log
		NDS_HR_Audit_Logger::log(
			'employee_updated',
			'employee',
			$id,
			$old_values,
			$data
		);

		return true;
	}

	/**
	 * Change employee status (active / inactive / terminated).
	 *
	 * @param int    $id
	 * @param string $new_status
	 * @return bool
	 */
	public function change_status( $id, $new_status ) {
		$existing = $this->repository->find_by_id( $id );
		if ( ! $existing ) {
			return false;
		}

		$valid_statuses = array( 'active', 'inactive', 'terminated', 'suspended' );
		if ( ! in_array( $new_status, $valid_statuses, true ) ) {
			return false;
		}

		$result = $this->repository->update( $id, array( 'employment_status' => $new_status ) );

		if ( $result ) {
			NDS_HR_Audit_Logger::log(
				'employee_status_changed',
				'employee',
				$id,
				array( 'status' => $existing->employment_status ),
				array( 'status' => $new_status )
			);
		}

		return $result;
	}

	/**
	 * Generate an atomic, unique Employee ID in format NDS-00001.
	 *
	 * Business logic layer delegates to repository query/counter persistence.
	 *
	 * @return string
	 */
	public function generate_employee_id() {
		return $this->repository->generate_unique_employee_id();
	}

	/**
	 * Delete employee record.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete_employee( $id ) {
		$existing = $this->repository->find_by_id( $id );
		if ( ! $existing ) {
			return false;
		}

		$result = $this->repository->delete( $id );

		if ( $result ) {
			NDS_HR_Audit_Logger::log(
				'employee_deleted',
				'employee',
				$id,
				(array) $existing,
				array()
			);
		}

		return $result;
	}
}
