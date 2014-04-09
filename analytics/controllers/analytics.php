<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Public controller for the Analytics module
 */
class Analytics extends Front_Controller
{
    private $gDomain = 'auto';

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Default method, does nothing.
	 *
	 * @return void
	 */
	public function index()
	{

	}

	/**
	 * Outputs the google analytics script code for the site.
	 *
	 * @return	View If analytics is enabled, returns the script embedded in a
	 * view.
	 */
	public function show_gcode()
	{
		if (settings_item('ga.enabled') == 1) {
			$data['gcode'] = settings_item('ga.code');
            $data['gDomain'] = settings_item('ga.domain') ?: $this->gDomain;

			return $this->load->view(
                settings_item('ga.universal') ? 'analytics/universal' : 'analytics/index',
                $data,
                true
            );
		}
	}
}
/* /analytics/controllers/analytics.php */