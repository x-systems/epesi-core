<?php

namespace Epesi\Core\System\User\Online;

use Epesi\Core\System\Modules\ModuleCore;
use Epesi\Core\System\User\Online\Integration\UsersOnlineApplet;
use Epesi\Base\Dashboard\Integration\UsersOnlineUserSettings;

class OnlineCore extends ModuleCore
{
	protected static $alias = 'users.online';
	
	protected static $joints = [
			UsersOnlineApplet::class,
			UsersOnlineUserSettings::class,
	];
	
	public function install()
	{
		
	}

	public function uninstall()
	{
		
	}
}
