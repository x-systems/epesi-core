<?php

namespace Epesi\Core\System\User;

use Epesi\Core\System\Integration\Modules\ModuleCore;

class UserCore extends ModuleCore
{
	protected static $alias = 'user';

	protected static $requires = [
			Access\AccessCore::class,
			Online\OnlineCore::class,
			Settings\SettingsCore::class,
	];
}
