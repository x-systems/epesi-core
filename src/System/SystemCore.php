<?php

namespace Epesi\Core\System;

use Epesi\Core\Integration\ModuleCore;
use Epesi\Core\System\Integration\SystemControlUserMenu;

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
