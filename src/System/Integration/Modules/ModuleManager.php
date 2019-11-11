<?php

namespace Epesi\Core\System\Integration\Modules;

use Epesi\Core\System\Database\Models\Module;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use atk4\core\Exception;
use Illuminate\Support\Facades\File;

class ModuleManager
{
	use Concerns\HasPackageManifest;
	
	/**
	 * @var \Illuminate\Support\Collection
	 */
	private static $installed;
	private static $processing;

	public static function isInstalled($classOrAlias)
	{
		return self::getClass($classOrAlias, true)? 1: 0;
	}
	
	public static function isAvailable($classOrAlias)
	{
		return class_exists(self::getClass($classOrAlias));
	}

	/**
	 * Get the module core class from class or alias
	 * 
	 * @param string $classOrAlias
	 * @return string;
	 */
	public static function getClass($classOrAlias, $installedOnly = false) {
		$modules = $installedOnly? self::getInstalled(): self::getAll();
		
		if (collect($modules)->contains($classOrAlias)) return $classOrAlias;
		
		return $modules[$classOrAlias]?? null;
	}
	
	/**
	 * Get a collection of installed modules in alias -> class pairs
	 * 
	 * @return \Illuminate\Support\Collection;
	 */
	public static function getInstalled()
	{
		return self::$installed = self::$installed?? self::getCached('epesi-modules-installed', function() {
			try {
				$installedModules = Module::pluck('class', 'alias');
			} catch (QueryException $e) {
				$installedModules = collect();
			}
			
			return $installedModules;
		});
	}
	
	/**
	 * Get a collection of all manifested modules in alias -> class pairs
	 * 
	 * @return \Illuminate\Support\Collection;
	 */
	public static function getAll()
	{
		return self::getCached('epesi-modules-available', function () {
			$modules = collect();
			foreach (array_merge(config('epesi.modules', []), self::packageManifest()->modules()?: []) as $namespace => $path) {
				foreach (self::discoverModuleClasses($namespace, $path) as $moduleClass) {
					$modules->add(['alias' => $moduleClass::alias(), 'class' => $moduleClass]);
				}
			}
			
			return $modules->pluck('class', 'alias');
		});
	}
	
	protected static function discoverModuleClasses($namespace, $basePath)
	{
		$ret = collect();
		foreach (glob($basePath . '/*', GLOB_ONLYDIR|GLOB_NOSORT) as $path) {
			$moduleNamespace = trim($namespace, '\\') . '\\' . basename($path);
			
			$ret = $ret->merge(self::discoverModuleClasses($moduleNamespace, $path));
			
			$moduleClass = $moduleNamespace . '\\' . basename($path) . 'Core';
			
			if (! is_a($moduleClass, ModuleCore::class, true)) continue;
			
			$ret->add($moduleClass);
		}
		
		return $ret;
	}
	
	/**
	 * Common method to use for caching of data within module manager
	 * 
	 * @param string $key
	 * @param \Closure $default
	 * @return mixed
	 */
	protected static function getCached($key, \Closure $default)
	{
		if (! Cache::has($key)) {
			Cache::forever($key, $default());
		}

		return Cache::get($key);
	}
	
	/**
	 * Clear module manager cache
	 */
	public static function clearCache()
	{
		self::$installed = null;
		File::delete(base_path('bootstrap/cache'));
		
		Cache::forget('epesi-modules-installed');
		Cache::forget('epesi-modules-available');
	}
	
	/**
	 * Alias for collect when no return values expected
	 *
	 * @param string $method
	 * @return array
	 */
	public static function call($method, $args = [])
	{
		return self::collect($method, $args);
	}
	
	/**
	 * Collect array of results from $method in all installed module core classes
	 *
	 * @param string $method
	 * @return array
	 */
	public static function collect($method, $args = [])
	{
		$args = is_array($args)? $args: [$args];
		
		$installedModules = self::getInstalled();
		
		// if epesi is not installed fake having the system module to enable its functionality
		if (! $installedModules->contains(\Epesi\Core\System\SystemCore::class)) {
			$installedModules = collect([
				'system' => \Epesi\Core\System\SystemCore::class
			]);
		}
		
		$ret = [];
		foreach ($installedModules as $module) {
			if (! $list = $module::$method(...$args)) continue;
			
			$ret = array_merge($ret, is_array($list)? $list: [$list]);
		}
		
		return $ret;
	}

	/**
	 * Install the module class provided as argument
	 * 
	 * @param string $classOrAlias
	 */
	public static function install($classOrAlias, $installRecommended = true)
	{
		if (self::isInstalled($classOrAlias)) {
			print ('Module "' . $classOrAlias . '" already installed!');
			
			return true;
		}
		
		if (! $moduleClass = self::getClass($classOrAlias)) {			
			throw new \Exception('Module "' . $classOrAlias . '" could not be identified');
		}
		
		/**
		 * @var ModuleCore $module
		 */
		$module = new $moduleClass();
		
		$module->migrate();
		
		self::satisfyDependencies($moduleClass);
		
		try {
			$module->install();
		} catch (\Exception $exception) {
			$module->rollback();
			
			throw $exception;
		}
		
		$module->publishAssets();
		
		// update database
		Module::create([
				'class' => $moduleClass,
				'alias' => $module->alias()
		]);
		
		if ($installRecommended) {
			$installRecommended = is_array($installRecommended)? $installRecommended: $module->recommended();
			
			foreach ($installRecommended as $recommendedModule) {
				try {
					self::install($recommendedModule);
				} catch (Exception $e) {
					// just continue, nothing to do if module cannot be installed
				}
			}
		}
				
		self::clearCache();
		
		print ('Module ' . $classOrAlias . ' successfully installed!');
		
		return true;
	}
	
	/**
	 * Install modules that $moduleClass requires
	 * Performs operation recursively for all required modules
	 * 
	 * @param string $moduleClass
	 * @throws \Exception
	 * @return boolean
	 */
	protected static function satisfyDependencies($moduleClass) {
		self::$processing[$moduleClass] = true;
		
		while ($unsatisfiedDependencies = self::unsatisfiedDependencies($moduleClass)) {
			$parentModule = array_shift($unsatisfiedDependencies);
				
			if (self::$processing[$parentModule]?? false) {
				throw new Exception('Cross dependency: '. $parentModule);
			}
				
			if (! self::isAvailable($parentModule)) {
				throw new Exception('Module not found: "' . $parentModule . '"');
			}
	
			print("\n\r");
			print('Installing required module: "' . $parentModule . '" by "' . $moduleClass . '"');

			self::install($parentModule);
		}

		unset(self::$processing[$moduleClass]);
		
		return true;
	}
	
	protected static function unsatisfiedDependencies($moduleClass) {
		return collect($moduleClass::requires())->diff(self::getInstalled())->filter()->all();
	}	
	
	public static function listDependencies($moduleClass) {
		$ret = collect();
		foreach (collect($moduleClass::requires()) as $parentClass) {
			$ret->add($parentClass = self::getClass($parentClass));
			
			$ret = $ret->merge(self::listDependencies($parentClass));
		}
		
		return $ret->filter()->unique()->all();
	}
	
	public static function listRecommended($moduleClass) {
		$ret = collect();
		foreach (collect($moduleClass::recommended()) as $childClass) {
			$ret->add($childClass = self::getClass($childClass));
			
			$ret = $ret->merge(self::listRecommended($childClass));
		}
		
		return $ret->filter()->unique()->all();
	}
	
	public static function listDependents() {
		$ret = [];
		foreach (self::getInstalled() as $moduleClass) {
			foreach ($moduleClass::requires() as $parentClass) {
				$ret[$parentClass][] = $moduleClass;
			}
		}
		return $ret;
	}
	
	public static function uninstall($classOrAlias)
	{
		if (! self::isInstalled($classOrAlias)) {
			print ('Module "' . $classOrAlias . '" is not installed!');
			
			return true;
		}
		
		if (! $moduleClass = self::getClass($classOrAlias)) {
			throw new \Exception('Module "' . $classOrAlias . '" could not be identified');
		}
		
		foreach (self::listDependents()[$moduleClass]?? [] as $childModule) {
			self::uninstall($childModule);
		}
		
		/**
		 * @var ModuleCore $module
		 */
		$module = new $moduleClass();
		
		$module->rollback();
		
		try {
			$module->uninstall();
		} catch (\Exception $exception) {
			$module->migrate();
			
			throw $exception;
		}
		
		$module->unpublishAssets();
		
		// update database
		Module::where('class', $moduleClass)->delete();
		
		self::clearCache();
		
		print ('Module ' . $classOrAlias . ' successfully uninstalled!');
		
		return true;
	}
}
