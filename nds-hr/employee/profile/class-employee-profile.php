<?php
/**
 * Employee Profile Controller.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Employee_Profile
 */
class NDS_HR_Employee_Profile {

	/**
	 * Plugin instance.
	 *
	 * @var NDS_HR_Plugin
	 */
	protected $plugin;

	/**
	 * Constructor.
	 *
	 * @param NDS_HR_Plugin $plugin
	 */
	public function __construct( NDS_HR_Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Render the employee's personal profile view.
	 *
	 * @param object $employee
	 */
	public function render( $employee ) {
		$wp_user = $employee->user_id ? get_user_by( 'id', $employee->user_id ) : null;
		include NDS_HR_PATH . 'templates/employee/profile.php';
	}
}
