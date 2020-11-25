<?php

namespace Epesi\Core\HomePage;

use Epesi\Core\System\Modules\ModuleCore;
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
	    Model\HomePage::migrate();
	    
		// setup default home pages
		Model\HomePage::create()->import([
	    		[
			    		'path' => 'view/dashboard',
			    		'role' => 'Super Admin'
	    		],
				[
						'path' => 'view/dashboard',
						'role' => 'Employee'
				]
		]);
	}
}
