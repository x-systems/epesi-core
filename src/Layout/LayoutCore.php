<?php

namespace Epesi\Core\Layout;

use Epesi\Core\System\Integration\Modules\ModuleCore;
use Epesi\Core\HomePage\HomePageCore;

class LayoutCore extends ModuleCore
{
	protected static $alias = 'layout';
	
	protected static $requires = [
			HomePageCore::class
	];
	
	public function install()
	{
		
	}
	
	public function uninstall()
	{
		
	}
}
