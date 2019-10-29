<?php 

namespace Epesi\Core\System\Integration;

use Epesi\Core\System\SystemView;
use Epesi\Base\Layout\Integration\Joints\UserMenuJoint;

class SystemControlUserMenu extends UserMenuJoint
{
	public static function access()
	{
		return SystemView::access();
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