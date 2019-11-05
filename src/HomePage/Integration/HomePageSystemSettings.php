<?php 

namespace Epesi\Core\HomePage\Integration;

use Epesi\Core\System\Integration\Joints\SystemSettingsJoint;

class HomePageSystemSettings extends SystemSettingsJoint
{
	public function label()
	{
		return __('Home Page');
	}

	public function icon()
	{
		return 'home';
	}
	
	public function link() {
		return 'homepage';
	}
}