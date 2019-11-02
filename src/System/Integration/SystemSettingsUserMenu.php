<?php 

namespace Epesi\Core\System\Integration;

use Epesi\Core\Layout\Integration\Joints\UserMenuJoint;
use Epesi\Core\System\SystemSettings;

class SystemSettingsUserMenu extends UserMenuJoint
{
	public static function access()
	{
		return SystemSettings::access();
	}
	
	public function entries() {
		return [
				'admin' => [
						'item' => [__('System Settings'), 'icon' => 'settings', 'class' => ['pjax']],
						'action' => url('view/system'),
						'group' => '10000:user'
				]
		];
	}
}