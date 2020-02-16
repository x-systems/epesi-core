<?php

namespace Epesi\Core\System\Modules;

abstract class ModuleCore
{
	use Concerns\HasModule;
	use Concerns\HasAssets;
	use Concerns\HasAssetsAccess;
	use Concerns\HasDependencies;
	use Concerns\HasMigrations;
	
	/**
	 * @var string Define the default view class for the module
	 * 
	 * By default it is <module-name> . 'View'
	 */
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
	 * Define module label
	 * 
	 * @var string
	 */
	protected static $label;

	/**
	 * Module installation method
	 * Include statements to be executed on module installation
	 *
	 * @return bool - true if installation success, false otherwise
	 */
	public function install() {}
	
	/**
	 * Module uninstallation method
	 * Include statements to be executed when module in uninstalled
	 *
	 * @return bool - true if installation success, false otherwise
	 */
	public function uninstall() {}
	
	/**
	 * Method called at system boot
	 * Can be used same as service controller boot method
	 */
	public static function boot() {}
	
	/**
	 * Label of the module
	 */
	public static function label() {
		return static::$label?: ucwords(str_ireplace('.', ' ', static::alias()));
	}
	
	/**
	 * Information about the module
	 */
	public static function info() {}
		
	public static function templates($skin = null)
	{
		$templatesDir = static::path() . DIRECTORY_SEPARATOR . 'Templates';
		
		if (! is_dir($templatesDir)) return;
		
		$skinDir = $templatesDir . DIRECTORY_SEPARATOR . $skin;
		
		return is_dir($skinDir)? $skinDir: $templatesDir;
	}
	
	public static function translations()
	{
		return implode(DIRECTORY_SEPARATOR, [static::path(), 'Translations']); 
	}		

	final public static function isInstalled()
	{
		return ModuleManager::isInstalled(static::class);
	}
	
	final public static function joints()
	{
		return static::$joints;
	}
	
	final public static function alias()
	{
		return static::$alias;
	}
}
