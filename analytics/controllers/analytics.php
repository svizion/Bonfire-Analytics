<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class analytics extends Front_Controller {

	//--------------------------------------------------------------------

	public function __construct()
	{
		parent::__construct();
	}

	//--------------------------------------------------------------------

	/*
		Method: index()

		Does nothing.
	*/
	public function index()
	{

 }

 /*
		Method: show_gcode()

		Outputs the google analytics script code for the footer of a website.
 */
 public function show_gcode()
 {
  $this->load->model('settings_model', null, true);
  $settings    = $this->settings_model->find_all_by('module', 'analytics');

  if ( $settings['ga.enabled'] == 1 )
  {
    $data['gcode'] = $settings['ga.code'];
    return $this->load->view('analytics/index', $data, true);
  }

 }

}
