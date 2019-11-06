<?php

namespace Epesi\Core\HomePage;

use Epesi\Core\System\Integration\Modules\ModuleCore;
use Epesi\Core\System\User\Access\AccessCore;

class HomePageCore extends ModuleCore
{
	protected static $alias = 'homepage';
	
	protected static $view = HomePageSettings::class;
	
	protected static $joints = [
			Integration\HomePageSystemSettings::class
	];
	
	protected static $requires = [
			AccessCore::class
	];
	
	public function install()
	{
		
	}
	
	public function uninstall()
	{
		
	}
}
