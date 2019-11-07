<?php

namespace Epesi\Core\HomePage;

use Epesi\Core\System\Integration\Modules\ModuleCore;
use Epesi\Core\System\User\Access\AccessCore;
use Epesi\Core\HomePage\Database\Models\HomePage;

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
		// setup default home pages
		HomePage::create([
				'path' => 'view/dashboard',
				'role' => 'Super Admin'
		]);
		
		HomePage::create([
				'path' => 'view/dashboard',
				'role' => 'Employee'
		]);
	}
	
	public function uninstall()
	{
		
	}
}
