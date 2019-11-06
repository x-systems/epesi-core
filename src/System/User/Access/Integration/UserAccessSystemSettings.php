<?php 

namespace Epesi\Core\System\User\Access\Integration;

use Epesi\Core\System\Integration\Joints\SystemSettingsJoint;

class UserAccessSystemSettings extends SystemSettingsJoint
{
	public function section()
	{
		return __('User Management');
	}
	
	public function label()
	{
		return __('Access');
	}

	public function icon()
	{
		return 'users cog';
	}
	
	public function link() {
		return ['user.access'];
	}
}