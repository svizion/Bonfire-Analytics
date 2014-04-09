<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Migration file to add/remove the settings for the Analytics module.
 */
class Migration_Install_analytics_settings extends Migration
{
    /**
     * @var array The settings for the Analytics module
     */
    private $data = array(
        array(
            'name'   => 'ga.username',
            'module' => 'analytics',
            'value'  => '',
        ),
        array(
            'name'   => 'ga.password',
            'module' => 'analytics',
            'value'  => '',
        ),
        array(
            'name'   => 'ga.enabled',
            'module' => 'analytics',
            'value'  => '',
        ),
        array(
            'name'   => 'ga.profile',
            'module' => 'analytics',
            'value'  => '',
        ),
    );

    /**
     * @var string The name of the module field in the settings, used to remove
     * these settings.
     */
    private $moduleField = 'module';

    /**
     * @var string The name of the 'name' field in the settings, used to remove
     * these settings.
     */
    private $nameField = 'name';

    /**
     * @var string The name of the settings table.
     */
    private $tableSettings = 'settings';

    /**
     * Add the module's settings.
     *
     * @return void
     */
	public function up()
	{
        $this->db->insert_batch($this->tableSettings, $this->data);
	}

    /**
     * Remove the module's settings
     *
     * @return void
     */
	public function down()
	{
        foreach ($this->data as $settings) {
            $this->db->where(array(
                                $this->moduleField => $settings[$this->moduleField],
                                $this->nameField   => $settings[$this->nameField],
                            ))
                     ->delete($this->tableSettings);
        }
	}
}
/* /analytics/migrations/002_Install_analytics_settings.php */