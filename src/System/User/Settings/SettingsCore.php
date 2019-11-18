<?php

namespace Epesi\Core\System\User\Settings;

use Epesi\Core\System\Integration\Modules\ModuleCore;
use Epesi\Core\System\User\Settings\Integration\UserMenu;

class SettingsCore extends ModuleCore
{
	protected static $alias = 'user.settings';
	
	protected static $joints = [
			UserMenu::class
	];
}
