<?php 

namespace Epesi\Core\System\Integration\Joints;

use Epesi\Core\System\Integration\Modules\ModuleJoint;
use Epesi\Core\System\Integration\Modules\Concerns\HasLaunchButton;
use Epesi\Core\System\Integration\Modules\Concerns\HasOptions;

abstract class SystemSettingsJoint extends ModuleJoint
{
	use HasOptions;
	use HasLaunchButton;
	
	/**
	 * Define the section under which admin button is displayed
	 *
	 * @return string|array|null
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