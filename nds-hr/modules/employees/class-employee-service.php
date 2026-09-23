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
	 * Create a new employee with business rule validations and optional account creation.
	 *
	 * @param array $data Sanitized employee data.
	 * @param array $account_options Options for WP User association.
	 * @return int|array|WP_Error Employee DB ID or array with account details on success, or WP_Error.
	 */
	public function create_employee( array $data, array $account_options = array() ) {
		global $wpdb;

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

		// Handle WordPress User account integration
		$linked_user_id     = 0;
		$account_info_badge = null;

		if ( ! empty( $account_options['action'] ) ) {
			if ( 'create' === $account_options['action'] ) {
				// Validate passwords match if manually provided
				$pw         = $account_options['password'] ?? '';
				$confirm_pw = $account_options['confirm_password'] ?? '';
				if ( ! empty( $pw ) && ! empty( $confirm_pw ) && $pw !== $confirm_pw ) {
					return new WP_Error( 'password_mismatch', __( 'The entered passwords do not match.', 'nds-hr' ) );
				}

				// Create new WP user (native password hashing, never logged or saved to HR DB)
				$user_result = NDS_HR_Auth::create_employee_user_account(
					array(
						'username'                => $account_options['username'] ?? '',
						'email'                   => $data['email'],
						'password'                => $pw,
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
				$account_info_badge = $user_result; // Contains temporary plain password for one-time HR display

				// Audit logging: ONLY record user ID, username, email, and require_pw flag. NEVER record the password.
				NDS_HR_Audit_Logger::log(
					'user_account_created',
					'user',
					$linked_user_id,
					array(),
					array(
						'username'                => $user_result['username'],
						'email'                   => $data['email'],
						'require_password_change' => ! empty( $account_options['require_password_change'] ),
					)
				);
			} elseif ( 'link' === $account_options['action'] && ! empty( $account_options['existing_user_id'] ) ) {
				$existing_user_id = absint( $account_options['existing_user_id'] );
				$user = get_user_by( 'id', $existing_user_id );

				if ( ! $user ) {
					return new WP_Error( 'invalid_user', __( 'The selected WordPress user does not exist.', 'nds-hr' ) );
				}

				// Check if user is already linked to another employee
				$already_linked = $this->repository->find_by_user_id( $existing_user_id );
				if ( $already_linked ) {
					return new WP_Error( 'user_already_linked', __( 'This WordPress user is already linked to employee ' . $already_linked->employee_id, 'nds-hr' ) );
				}

				$linked_user_id = $existing_user_id;

				NDS_HR_Audit_Logger::log(
					'user_account_linked',
					'user',
					$linked_user_id,
					array(),
					array( 'user_id' => $linked_user_id )
				);
			}
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
				'employee_id' => $data['employee_id'] ?? '',
				'full_name'   => $data['full_name'],
				'email'       => $data['email'],
				'department'  => $data['department_id'],
				'status'      => $data['employment_status'],
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
	 * @return true|WP_Error
	 */
	public function update_employee( $id, array $data ) {
		global $wpdb;

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
