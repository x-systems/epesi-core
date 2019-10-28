<?php 

namespace Epesi\Core\System\Integration\Joints;

use Epesi\Core\Integration\Module\ModuleJoint;
use Epesi\Core\Integration\Concerns\HasLaunchButton;
use Epesi\Core\Integration\Concerns\HasOptions;

abstract class SystemSettingsJoint extends ModuleJoint
{
	use HasOptions;
	use HasLaunchButton;
	
	/**
	 * Define the section under which admin button is displayed
	 *
	 * @return string
	 */
	public function section()
	{
		return __('General');
	}
	
	public static function access()
	{
		return __('General');
	}
}