<?php 

namespace Epesi\Core\System\Logo\Integration;

use Epesi\Core\System\Integration\Joints\SystemSettingsJoint;

class LogoSystemSettings extends SystemSettingsJoint
{
	public function section()
	{
		return __('System Configuration');
	}
	
	public function label()
	{
		return __('Title & Logo');
	}

	public function icon()
	{
		return 'image outline';
	}
	
	public function link() {
		return ['system.logo'];
	}
}