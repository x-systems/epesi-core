<?php

namespace Epesi\Core\System;

use Epesi\Core\Integration\Module\ModuleCore;
use Epesi\Core\System\Integration\Joints\SystemControlUserMenu;

class SystemCore extends ModuleCore
{
	protected static $alias = 'system';
	
	protected static $joints = [
			SystemControlUserMenu::class
	];
	
	public function install()
	{
		
	}
	
	public function uninstall()
	{
		
	}
}
