<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class settings extends Admin_Controller {

  //--------------------------------------------------------------------

  public function __construct()
  {
    parent::__construct();

    $this->auth->restrict('Analytics.Settings.View');
				$this->load->model('activities/Activity_model', 'activity_model');

				$this->load->model('settings/settings_model', 'settings_model');

    $this->load->library('form_validation');
    $this->lang->load('analytics');
    Assets::add_js($this->load->view('settings/js', null, true), 'inline');

    $settings = $this->settings_model->find_all_by('module', 'analytics');

				$settings = array (
																							'ga_username' => $settings['ga.username'],
																							'ga_password' => $settings['ga.password'],
																							'ga_enabled'  => (int) $settings['ga.enabled'],
																							'ga_profile'  => $settings['ga.profile'],
																							'ga_code'     => $settings['ga.code']
																						);

    Template::set('settings',$settings);
  }

  //--------------------------------------------------------------------

  /*
    Method: index()

    Displays a list of form data.
  */
  public function index()
  {
    if ($this->input->post('submit'))
    {
      if ($this->save_settings())
      {
        Template::set_message(lang('settings_edit_success'), 'success');
        Template::redirect('admin/settings/analytics');
      } else {
        Template::set_message('Error', 'error');
      }
    }

    Template::set('toolbar_title','Google Analytics');
    Template::render();
  }

  //--------------------------------------------------------------------

  /*
    Method: edit()

    Displays form data and writes settings to database.
  */
  public function edit()
  {
    if ($this->input->post('submit'))
    {
      if ($this->save_settings())
      {
        Template::set_message(lang('settings_edit_success'), 'success');
        Template::redirect('admin/settings/analytics');
      } else {
        Template::set_message('Error', 'error');
      }
    }

    Template::set('toolbar_title', "Google Analytics");
    Template::set_view('settings/index');
    Template::render();
  }

  //--------------------------------------------------------------------

  //--------------------------------------------------------------------
  // !PRIVATE METHODS
  //--------------------------------------------------------------------

  /*
    Method: save_settings()

    Runs form validation on data and writes settings to database.
  */
  private function save_settings()
  {

    $this->form_validation->set_rules('ga_username','Username','valid_email|required|trim|xss_clean|max_length[100]');
				if ( $this->input->post('ga_new_password') )
						$this->form_validation->set_rules('ga_new_password','Password','required|trim|xss_clean|max_length[100]');

    $this->form_validation->set_rules('ga_enabled','Enabled','required|trim|xss_clean|max_length[1]');

    if ( $this->input->post('ga_enabled') != 0 )
    {
      $this->form_validation->set_rules('ga_profile','Profile id','trim|xss_clean|max_length[100]');
      $this->form_validation->set_rules('ga_code','Code','required|trim|xss_clean|max_length[15]');
    }

    if ($this->form_validation->run() === false)
    {
      return false;
    }


				$password = ( $this->input->post('ga_new_password') != '' ) ? $this->input->post('ga_new_password') : $this->input->post('ga_password');

				$data = array(
                  array('name' => 'ga.username', 'value' => $this->input->post('ga_username') ),
                  array('name' => 'ga.password', 'value' => $password ),
                  array('name' => 'ga.enabled', 'value' => $this->input->post('ga_enabled') ),
                  array('name' => 'ga.profile', 'value' => $this->input->post('ga_profile') ),
                  array('name' => 'ga.code',  'value' => $this->input->post('ga_code') ),
                 );

    //destroy the saved update message in case they changed update preferences.
    if ($this->cache->get('update_message'))
    {
      $this->cache->delete('update_message');
    }

    // Log the activity
				$this->activity_model->log_activity($this->current_user->id, lang('bf_act_settings_saved'). $this->input->ip_address(), 'analytics');

    // save the settings to the DB
    $updated = $this->settings_model->update_batch($data, 'name');

    return $updated;
  }

  //--------------------------------------------------------------------

}
