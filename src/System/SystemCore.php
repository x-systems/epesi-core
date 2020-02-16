<?php

namespace Epesi\Core\System;

class SystemCore extends Modules\ModuleCore
{
	protected static $alias = 'system';
	
	protected static $view = SystemSettings::class;
	
	protected static $joints = [
			Integration\SystemSettingsUserMenu::class,
			Integration\ModuleAdministrationSystemSettings::class
	];
	
	protected static $requires = [
			\Epesi\Core\Layout\LayoutCore::class,
			User\UserCore::class,
	];
	
	protected static $recommends = [
			'dashboard'
	];
	
	public function migrate()
	{
	    Models\Module::migrate();
	    
	    Models\Variable::migrate();
	    
	    return parent::migrate();
	}
	
}
