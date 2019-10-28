<?php

namespace Epesi\Core\Integration\Module;

use Epesi\Core\Integration\Concerns\HasAssets;
use Epesi\Core\Integration\Concerns\HasStorage;
use Epesi\Core\Integration\Concerns\HasServiceProviders;
use Epesi\Core\Integration\Concerns\HasAssetsAccess;
use Epesi\Core\Integration\Concerns\HasModule;

abstract class ModuleCore
{
	use HasModule;
	use HasAssets;
	use HasAssetsAccess;
	use HasStorage;
	use HasServiceProviders;
	
	protected $version = '0.0.0';
	
	protected $requires = [];
	
	protected static $view;
	
	protected static $joints = [];
	
	protected static $alias;
	
	public static function info() {}
		
	/**
	 * Directory where module migrations are located
	 * 
	 * @return string
	 */
	public static function migrations()
	{
		return implode(DIRECTORY_SEPARATOR, [static::path(), 'Database', 'Migrations']); 
	}
			
	/**
	 * Module installation method
	 * Include statements to be executed on module installation
	 * 
	 * @return true if installation success, false otherwise
	 */
	abstract public function install();
	
	/**
	 * Module uninstallation method
	 * Include statements to be executed when module in uninstalled
	 * 
	 * @return true if installation success, false otherwise
	 */
	abstract public function uninstall();
	
	final public function version()
	{
		return $this->version;
	}
	
	final public static function joints()
	{
		return static::$joints;
	}
}
