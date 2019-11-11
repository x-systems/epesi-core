<?php 

namespace Epesi\Core\System\Integration;

class ModuleAdministrationSystemSettings extends Joints\SystemSettingsJoint
{
	public function section()
	{
		return __('System Configuration');
	}
	
	public function label()
	{
		return __('Modules Administration');
	}

	public function icon()
	{
		return 'cubes';
	}
	
	public function link() {
		return ['system:module-administration'];
	}
}