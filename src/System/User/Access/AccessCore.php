<?php

namespace Epesi\Core\System\User\Access;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Epesi\Core\System\Integration\Modules\ModuleCore;
use Epesi\Core\System\User\Access\Integration\UserAccessSystemSettings;
use Illuminate\Support\Facades\Gate;

class AccessCore extends ModuleCore
{
	protected static $alias = 'user.access';
	
	protected static $view = AccessSettings::class;
	
	protected static $joints = [
			UserAccessSystemSettings::class
	];
	
	public function defaultRoles()
	{
		return [
				'Super Admin',
				'Admin',
				'Employee',
				'Guest'
		];
	}
	
	public function install()
	{
		foreach ($this->defaultRoles() as $roleName) {
			Role::create(['name' => $roleName]);
		}
		
		Permission::create(['name' => 'modify system']);
		
		$modifySystemSettings = Permission::create(['name' => 'modify system settings']);
		
		Role::findByName('Admin')->givePermissionTo($modifySystemSettings);
	}

	public function uninstall()
	{
		Role::whereIn('name', $this->defaultRoles())->delete();
		
		Permission::findByName('modify system')->delete();
		Permission::findByName('modify system settings')->delete();
	}
	
	public static function boot()
	{
		// allow Super Admin full access
		Gate::after(function ($user, $ability) {
			return $user->hasRole('Super Admin');
		});
	}
}
