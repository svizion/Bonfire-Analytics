<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Migration file to add/remove permissions for the Analytics module.
 */
class Migration_Install_analytics_permissions extends Migration
{
    /**
     * @var array The key values of the roles to which the permissions will be
     * assigned.
     */
    private $defaultRoles = array('1');

    /**
     * @var string The name of the permission key (in both tables)
     */
    private $keyPermission = 'permission_id';

    /**
     * @var string The name of the role key (in the role/permissions table)
     */
    private $keyRole = 'role_id';

    /**
     * @var string The name of the permission name column. Used when removing
     * the permissions.
     */
    private $permissionName = 'name';

    /**
     * @var string The name of the permissions table
     */
    private $tablePermissions = 'permissions';

    /**
     * @var string The name of the role/permissions table
     */
    private $tableRolePermissions = 'role_permissions';

    /**
     * @var array The permissions.
     */
    private $permissions = array(
        array(
            'name'        => 'Analytics.Reports.View',
            'description' => 'View Analytics Reports',
            'status'      => 'active',
        ),
        array(
            'name'        => 'Analytics.Settings.View',
            'description' => 'View Analytics Settings',
            'status'      => 'active',
        ),
    );

    /**
     * Add each of the permissions and assign them to the default roles.
     *
     * @return void
     */
	public function up()
	{
        // Insert each of the permissions and retrieve the inserted ID
		$rolePermissionsData = array();
        foreach ($this->permissions as $permission) {
            $this->db->insert($this->tablePermissions, $permission);
            $insertedId = $this->db->insert_id();

            // Add the inserted ID (and each role's ID) to the data for the
            // role/permissions table
            foreach ($this->defaultRoles as $defaultRole) {
                $rolePermissionsData[] = array(
                    $this->keyRole       => $defaultRole,
                    $this->keyPermission => $insertedId,
                );
            }
        }

        // Insert the role/permissions data
        $this->db->insert_batch($this->tableRolePermissions, $rolePermissionsData);
	}

    /**
     * Remove the permissions.
     *
     * @return void
     */
	public function down()
	{
        // Get the names of the permissions to remove
        $permissionNames = array();
        foreach ($this->permissions as $permission) {
            $permissionNames = $permission[$this->permissionName];
        }

        // Retrieve the IDs of the permissions to remove
        $query = $this->db->select($this->keyPermission)
                          ->where_in($this->permissionName, $permissionNames)
                          ->get($this->tablePermissions);

        $permissionIds = array();
        foreach ($query->result() as $row) {
            $permissionIds[] = $row->{$this->keyPermission};
        }

        // Remove the permissions from the role/permission table
        $this->db->where_in($this->keyPermission, $permissionIds)
                 ->delete($this->tableRolePermissions);

        // Remove the permissions from the permissions table
        $this->db->where_in($this->keyPermission, $permissionIds)
                 ->delete($this->tablePermissions);
	}
}
/* /analytics/migrations/001_Install_analytics_permissions.php */