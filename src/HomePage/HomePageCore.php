<?php

namespace Epesi\Core\HomePage;

use Epesi\Core\System\Integration\Modules\ModuleCore;

class HomePageCore extends ModuleCore
{
	protected static $alias = 'homepage';
	
	protected static $view = HomePageSettings::class;
	
	protected static $joints = [
			Integration\HomePageSystemSettings::class
	];
	
	protected static $requires = [
			'Epesi\\Base\\User\\Access\\AccessCore'
	];
	
	public function install()
	{
		
	}
	
	public function uninstall()
	{
		
	}
}
