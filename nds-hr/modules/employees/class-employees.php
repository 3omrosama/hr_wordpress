<?php
/**
 * Employee Module Bootstrap.
 *
 * @package NDS_HR
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NDS_HR_Employees
 */
class NDS_HR_Employees {

	/**
	 * Main plugin instance.
	 *
	 * @var NDS_HR_Plugin
	 */
	protected $plugin;

	/**
	 * Repository instance.
	 *
	 * @var NDS_HR_Employee_Repository
	 */
	public $repository;

	/**
	 * Service instance.
	 *
	 * @var NDS_HR_Employee_Service
	 */
	public $service;

	/**
	 * Constructor.
	 *
	 * @param NDS_HR_Plugin $plugin
	 */
	public function __construct( NDS_HR_Plugin $plugin ) {
		$this->plugin     = $plugin;
		$this->repository = new NDS_HR_Employee_Repository();
		$this->service    = new NDS_HR_Employee_Service( $this->repository );
	}
}
