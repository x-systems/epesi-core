<?php 

namespace Epesi\Core\System\User\Settings\Integration\Joints;

use Epesi\Core\System\Integration\Modules\ModuleJoint;
use Epesi\Core\System\Integration\Modules\Concerns\HasLaunchButton;
use Epesi\Core\System\Integration\Modules\Concerns\HasOptions;

abstract class UserSettingsJoint extends ModuleJoint
{
	use HasOptions;
	use HasLaunchButton;
	
	/**
	 * Define group under which the settings are saved
	 * 
	 * @var string
	 */
	protected $group;

	/**
	 * Get the group under which settings are saved
	 *
	 * @return string
	 */
	public function group()
	{
		return $this->group?: static::class;
	}
	
	/**
	 * Define the settings view
	 */
	public function link() {
		return ['user.settings', 'edit', static::class];
	}
}