<?php

namespace Epesi\Core\Integration;

abstract class ModuleCore
{
	use Concerns\HasModule;
	use Concerns\HasAssets;
	use Concerns\HasAssetsAccess;
	use Concerns\HasStorage;
	use Concerns\HasServiceProviders;
	
	protected $version = '0.0.0';
	
	protected $requires = [];
	
	protected static $view;
	
	/**
	 * Define joints that this class manifests to the other modules
	 * 
	 * @var array
	 */
	protected static $joints = [];
	
	/**
	 * Define module alias
	 * 
	 * @var string
	 */
	protected static $alias;
	
	/**
	 * Information about the module
	 */
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
			
	public static function templates($skin = null)
	{
		$templatesDir = static::path() . DIRECTORY_SEPARATOR . 'Templates';
		
		if (! is_dir($templatesDir)) return;
		
		$skinDir = $templatesDir . DIRECTORY_SEPARATOR . $skin;
		
		return is_dir($skinDir)? $skinDir: $templatesDir;
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
