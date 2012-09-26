<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class reports extends Admin_Controller {

  //--------------------------------------------------------------------
  private $ga_username;
  private $ga_password;
  private $ga_enabled;
  private $ga_profile;


  //--------------------------------------------------------------------

  public function __construct()
  {
    parent::__construct();

    $this->auth->restrict('Analytics.Reports.View');
    $this->lang->load('analytics');

    $this->load->model('settings/settings_model', 'settings_model');

    $settings = $this->settings_model->find_all_by('module', 'analytics');

    if ( is_array( $settings ) && $settings['ga.enabled'] == 1 )
    {
      $this->ga_username = $settings['ga.username'];
      $this->ga_password = $settings['ga.password'];
      $this->ga_profile  = $settings['ga.profile'];
      $this->ga_enabled  = $settings['ga.enabled'];
    } else {
      $this->ga_username = '';
      $this->ga_password = '';
      $this->ga_enabled  = 0;
      $this->ga_profile  = '';
    }

    //Assets::add_js($this->load->view('reports/js', null, true), 'inline');

  }

  //--------------------------------------------------------------------

  /*
   Method: index()

   Displays a list of form data.
  */
  public function index()
  {

    if ( ( $this->ga_enabled == 1 ) && $this->ga_username && $this->ga_password )
    {
			Assets::clear_cache();
      Assets::add_module_js('analytics','swfobject.js');
      Assets::add_module_css('analytics','analytics.css');

			Assets::add_js( $this->load->view('reports/reportsjs', null, true ), 'inline' );
			Assets::add_js( $this->load->view('reports/extreport', null, true ), 'inline' );
      Template::set('toolbar_title', "Google Analytics");
      Template::render();
    } else {
      Template::set('toolbar_title', "Google Analytics");
      Template::set_view('reports/not_defined');
      Template::render();
    }

  }

  //--------------------------------------------------------------------

	function analytics_profiles()
	{

		if ( ( $this->ga_enabled == 1 ) AND $this->ga_username AND $this->ga_password )
    {
      $this->load->library('analytics');
      $this->analytics->login($this->ga_username, $this->ga_password ); // change
      $aProfiles = $this->analytics->getProfileList();
      $counter = 1;
      $str = '';

			foreach($aProfiles as $value => $key)
			{
        $selected = 0;
        $comma = $counter == count($aProfiles) ? '' : '|';
        $str .= $value.','.$key.','.$selected.$comma;
        $counter++;
			}

      $data['profiles'] = $str.'';
			$this->load->view('reports/profiles',$data);
    }

  }


  //--------------------------------------------------------------------

  function statistics()
  {
    $this->cache('table-','table_data','no_table_data');
  }


  //--------------------------------------------------------------------

  function xml_data()
  {
    $this->cache('xml-','xml_data','empty_data');
  }


  //--------------------------------------------------------------------

  private function cache($filename, $view, $noresults = 'empty_data')
  {

    $this->load->library('analytics');
    $use_cache = TRUE;

    $year = (int)$this->input->post('year');
    $month = (int)$this->input->post('month');
    $profile = $this->input->post('profile');
    $cprofile = str_replace(':', '', $profile);

    $filepath = APPPATH . 'cache/'.$cprofile.'-'.$filename.$year.'-'.$month.'___'.date('Y-n-d').EXT;
    $created = substr($filepath , strlen(APPPATH. 'cache/'.$filename),strlen($filepath));
    $created = substr($filepath, strpos($filepath,'___')+3,-4);
    // als de huidige maand gelijk is aan de ingegeven maand
    if($use_cache)
    {

      if($month == date('n') && $year == date('Y'))
      {
        // als de created date gelijk is aan vandaag
        if($created == date('Y-n-d'))
        {
          if(file_exists($filepath))
          {
            echo file_get_contents($filepath);
            exit;
          }
        }
      } else {

        $days = cal_days_in_month(CAL_GREGORIAN,$month, $year);

    		// controle of de file wel de laatste dag bevat als we niet in deze maand zitten
        foreach (range(1, $days) as $number)
        {

          $filepath = APPPATH . "cache/{$cprofile}-{$filename}{$year}-{$month}___{$year}-{$month}-{$number}" . EXT;
          if ( file_exists($filepath) )
          {
            if($number == $days)
            {
              echo file_get_contents($filepath);
              exit;
            } else {
            	@unlink($filepath);
            }
          }
        }
      }
    }

    $this->analytics->login($this->ga_username, $this->ga_password ); // change

    $this->analytics->setProfileById($profile); // change
    $this->analytics->setMonth($month, $year);

    $data = array(
                  'visitors' => $this->analytics->getVisitors(),
                  'pageviews' => $this->analytics->getPageviews(),
                  'visitsperhour' => $this->analytics->getVisitsPerHour(),
                  'browsers' => $this->analytics->getBrowsers(),
                  'referrers' => $this->analytics->getReferrers(),
                  'searchwords' => $this->analytics->getSearchWords(),
                  'screenresolutions' => $this->analytics->getScreenResolution(),
                  'os' => $this->analytics->getOperatingSystem(),
                  'month' => $month,
                  'year' => $year
                 );

    $cache = count($data['referrers']) ?
    $this->load->view('reports/chart_data/'.$view,$data, TRUE) : $this->load->view('reports/chart_data/'. $noresults ,null, TRUE);


    // cleanup
    $max = cal_days_in_month(CAL_GREGORIAN,date('n'), date('Y'));
    foreach (range(1, $max) as $number)
		{
      @unlink( APPPATH . 'cache/'.$cprofile.'-'.$filename.$year.'-'.$month.'___'.date('Y-n-').$number.EXT);
		}

    // write
    $handle = fopen($filepath,"x+");
    fwrite($handle,$cache);
    fclose($handle);
    $data['cache']=$cache;
    $this->load->view('reports/chart',$data);
  }

}
