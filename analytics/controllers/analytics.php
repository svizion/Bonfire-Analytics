<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class analytics extends Front_Controller
{

	//--------------------------------------------------------------------

	/**
	 * Constructor, calls parent (Front_Controller) constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	//--------------------------------------------------------------------

	/**
	 * Currently does nothing
	 */
	public function index()
	{

	}

	//--------------------------------------------------------------------

	/**
	 * Outputs the Google Analytics script for the footer of the website.
	 */
	public function show_gcode()
	{
		if ( ! class_exists('Settings_model'))
		{
			$this->load->model('settings_model', null, true);
		}
		$settings = $this->settings_model->find_all_by('module', 'analytics');

		if ( is_array($settings) && $settings['ga.enabled'] == 1 )
		{
			$data['gcode'] = $settings['ga.code'];
			return $this->load->view('analytics/index', $data, true);
		}
	}
}
