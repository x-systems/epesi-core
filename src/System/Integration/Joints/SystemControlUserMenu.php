<?php 

namespace Epesi\Core\System\Integration\Joints;

use Epesi\Core\Integration\Joints\UserMenuJoint;
use Epesi\Core\System\SystemView;

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