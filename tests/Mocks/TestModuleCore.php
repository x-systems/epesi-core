<?php

namespace Epesi\Core\Tests\Mocks;

use Epesi\Core\System\Modules\ModuleCore;

class TestModuleCore extends ModuleCore
{
	protected static $alias = 'test.module';
	
	protected static $joints = [
			TestModuleJoint::class
	];
	
	protected static $requires = [];
	
	public function install()
	{

	}
	
	public function uninstall()
	{
		
	}
}