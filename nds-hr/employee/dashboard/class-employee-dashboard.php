<?php
/**
 * Employee Self-Service Dashboard Controller.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Employee_Dashboard
 */
class NDS_HR_Employee_Dashboard {

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
	 * Render the employee's personal dashboard tab.
	 *
	 * @param object $employee
	 */
	public function render( $employee ) {
		// Calculate tenure / days since hire
		$hire_datetime = new DateTime( $employee->hire_date );
		$now_datetime  = new DateTime();
		$tenure_interval = $hire_datetime->diff( $now_datetime );

		// Future modules placeholder metadata for Phase 2-5
		$future_features = array(
			'attendance' => array(
				'title'       => __( 'Attendance & Time Tracking', 'nds-hr' ),
				'description' => __( 'Check-in, check-out, shifts, and monthly attendance tracking (Scheduled Phase 2).', 'nds-hr' ),
				'status'      => __( 'Coming in Phase 2', 'nds-hr' ),
				'icon'        => 'dashicons-clock',
			),
			'leaves' => array(
				'title'       => __( 'Leave Management', 'nds-hr' ),
				'description' => __( 'Request leaves, view remaining leave balances, and track manager approvals (Scheduled Phase 3).', 'nds-hr' ),
				'status'      => __( 'Coming in Phase 3', 'nds-hr' ),
				'icon'        => 'dashicons-calendar-alt',
			),
			'payroll' => array(
				'title'       => __( 'Payslips & Compensation', 'nds-hr' ),
				'description' => __( 'View salary breakdown, allowances, deductions, and download official payslips (Scheduled Phase 4 & 5).', 'nds-hr' ),
				'status'      => __( 'Coming in Phase 4 & 5', 'nds-hr' ),
				'icon'        => 'dashicons-media-document',
			),
		);

		include NDS_HR_PATH . 'templates/employee/dashboard.php';
	}
}
