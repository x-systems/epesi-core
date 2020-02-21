<?php

namespace Epesi\Core\System\Logo;

use Epesi\Core\System\Modules\ModuleCore;

class LogoCore extends ModuleCore
{
	protected static $alias = 'system.logo';
	
	protected static $view = LogoSettings::class;
	
	protected static $joints = [
			Integration\LogoSystemSettings::class
	];
	
	public static function boot()
	{
		// dynamically set the page title based on GUI setting
		config(['epesi.ui.title' => LogoSettings::getTitle()]);
	}
}
