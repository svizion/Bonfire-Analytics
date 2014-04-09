<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Add/remove the Universal Analytics settings for the Analytics module.
 */
class Migration_Install_universal_analytics extends Migration
{
    /**
     * @var array The settings for the Analytics module
     */
    private $data = array(
        array(
            'name'   => 'ga.universal',
            'module' => 'analytics',
            'value'  => '',
        ),
        array(
            'name'   => 'ga.domain',
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
     * Add the setting(s).
     *
     * @return void
     */
	public function up()
	{
        $this->db->insert_batch($this->tableSettings, $this->data);
	}

    /**
     * Remove the setting(s).
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
/* /analytics/migrations/003_Install_analytics_code.php */