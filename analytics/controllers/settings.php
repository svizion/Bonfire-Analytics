<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * The Settings controller for the Analytics module
 */
class Settings extends Admin_Controller
{
    protected $permissionView = 'Analytics.Settings.View';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->auth->restrict($this->permissionView);
		$this->lang->load('analytics');

		Assets::add_js($this->load->view('settings/js', null, true), 'inline');

		Template::set('settings', array (
			'ga_username'	=> settings_item('ga.username'),
			'ga_password'	=> settings_item('ga.password'),
			'ga_enabled'	=> (int) settings_item('ga.enabled'),
			'ga_profile'	=> settings_item('ga.profile'),
			'ga_code'		=> settings_item('ga.code'),
            'ga_universal'  => settings_item('ga.universal'),
            'ga_domain'     => settings_item('ga.domain'),
		));
	}

	/**
	 * Display the settings form
	 *
	 * @return	void
	 */
	public function index()
	{
		if ($this->input->post('submit')) {
			if ($this->save_settings()) {
				Template::set_message(lang('analytics_settings_edit_success'), 'success');
				Template::redirect('admin/settings/analytics');
			} else {
				Template::set_message(sprintf(lang('analytics_form_error'), $error), 'error');
			}
		}

		Template::set('toolbar_title', lang('analytics_toolbar_title_settings_index'));

		Template::render();
	}

	/**
	 * Displays form data and writes settings to database
	 *
	 * @return	void
	 */
	public function edit()
	{
		if ($this->input->post('submit')) {
			if ($this->save_settings()) {
				Template::set_message(lang('analytics_settings_edit_success'), 'success');
				Template::redirect('admin/settings/analytics');
			} else {
				Template::set_message(sprintf(lang('analytics_form_error'), $error), 'error');
			}
		}

		Template::set_view('settings/index');
		Template::set('toolbar_title', lang('analytics_toolbar_title_settings_edit'));

		Template::render();
	}

	/*
	 *********************************************************************
	 * Private Methods
	 *********************************************************************
	 */

	/**
	 * Runs form validation and writes settings to the database
	 *
	 * @return	Boolean		false if there was an error, else true
	 */
	private function save_settings()
	{
		$this->form_validation->set_rules('ga_username', 'lang:analytics_ga_username', 'valid_email|required|trim|xss_clean|max_length[100]');
		$this->form_validation->set_rules('ga_enabled', 'lang:analytics_ga_enabled', 'required|trim|xss_clean|max_length[1]');
		$this->form_validation->set_rules('ga_profile', 'lang:analytics_ga_profile', 'trim|xss_clean|max_length[100]');

		$rule = 'trim|xss_clean|max_length[100]';
		if ($this->input->post('ga_new_password')) {
			$rule = 'required|' . $rule;
		}
		$this->form_validation->set_rules('ga_new_password', 'lang:analytics_ga_new_password', $rule);

		$rule = 'trim|xss_clean|max_length[15]';
		if ($this->input->post('ga_enabled') != 0) {
			$rule = 'required|' . $rule;
		}
		$this->form_validation->set_rules('ga_code', 'lang:analytics_ga_code', $rule);
        $this->form_validation->set_rules('ga_universal', 'lang:analytics_ga_universal', 'trim|xss_clean|max_length[1]');
        $this->form_validation->set_rules('ga_domain', 'lang:analytics_ga_domain', 'trim|xss_clean|max_length[255]');

		if ($this->form_validation->run() === false) {
			return false;
		}

		$data = array(
			array(
				'name'	 => 'ga.username',
				'value'	 => $this->input->post('ga_username'),
				'module' => 'analytics',
			),
			array(
				'name'	 => 'ga.enabled',
				'value'	 => $this->input->post('ga_enabled'),
				'module' => 'analytics',
			),
			array(
				'name'	 => 'ga.profile',
				'value'	 => $this->input->post('ga_profile'),
				'module' => 'analytics',
			),
			array(
				'name'	 => 'ga.code',
				'value'	 => $this->input->post('ga_code'),
				'module' => 'analytics',
			),
            array(
                'name'   => 'ga.universal',
                'value'  => $this->input->post('ga_universal'),
                'module' => 'analytics',
            ),
            array(
                'name'   => 'ga.domain',
                'value'  => $this->input->post('ga_domain'),
                'module' => 'analytics',
            ),
		);

		if ($this->input->post('ga_new_password')) {
			$data[] = array(
				'name'	 => 'ga.password',
				'value'	 => $this->input->post('ga_new_password'),
				'module' => 'analytics',
			);
		}

		log_activity($this->current_user->id, lang('bf_act_settings_saved') . $this->input->ip_address(), 'analytics');

		return $this->settings_model->update_batch($data, 'name');
	}
}
/* /analytics/controllers/settings.php */