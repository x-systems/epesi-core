<?php

namespace Epesi\Core\System\Modules;

use Epesi\Core\System\Model\Module;
use Illuminate\Support\Facades\Cache;
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

	/**
	 * Check if a module is installed providing an alias or class name
	 */
	public static function isInstalled(string $classOrAlias): bool
	{
		return (bool) self::getClass($classOrAlias, true);
	}
	
	/**
	 * Check if a module is available providing an alias or class name
	 */
	public static function isAvailable(string $classOrAlias): bool
	{
		return class_exists(self::getClass($classOrAlias));
	}

	/**
	 * Get the module core class from class or alias
	 */
	public static function getClass(string $classOrAlias, bool $installedOnly = false): ?string
	{
		$modules = $installedOnly? self::getInstalled(): self::getAll();

		if (collect($modules)->contains($classOrAlias)) {
			return $classOrAlias;
		}
		
		return $modules[$classOrAlias] ?? null;
	}
	
	/**
	 * Get a collection of installed modules in alias -> class pairs
	 */
	public static function getInstalled(): array
	{
		return self::$installed = self::$installed ?? self::getCached('epesi-modules-installed', function() {
			try {
			    $installedModules = Module::pluck('class', 'alias');
			} catch (\Exception $e) {
				$installedModules = collect();
			}
			
			return $installedModules->all();
		});
	}
	
	/**
	 * Get a collection of all manifested modules in alias -> class pairs
	 */
	public static function getAll(): array
	{
		return self::getCached('epesi-modules-available', function () {
			$modules = collect();
			foreach (array_merge(config('epesi.modules', []), self::packageManifest()->modules()?: []) as $namespace => $path) {
				foreach (self::discoverModuleClasses($namespace, $path) as $moduleClass) {
					$modules->add(['alias' => $moduleClass::alias(), 'class' => $moduleClass]);
				}
			}

			return $modules->pluck('class', 'alias')->all();
		});
	}
	
	/**
	 * Scans the profided $basePath directrory recursively to locate modules
	 * A directory is considered having a module when it has a file descendant of ModuleCore
	 * having the directory name with 'Core' suffix, e.g Test -> TestCore extends ModuleCore
	 */
	protected static function discoverModuleClasses(string $namespace, string $basePath): array
	{
	    $ret = collect();
	    
	    $moduleNamespace = trim($namespace, '\\');
	    
	    $names = array_slice(explode('\\', $moduleNamespace), - 1);
	    
	    if ($name = $names? reset($names): '') {
	        $moduleClass = $moduleNamespace . '\\' . $name . 'Core';
	        
	        if (is_subclass_of($moduleClass, ModuleCore::class)) {
	            $ret->add($moduleClass);
	        }
	    }
	    
	    foreach (glob($basePath . '/*', GLOB_ONLYDIR|GLOB_NOSORT) as $path) {
	        $subModuleNamespace = $moduleNamespace . '\\' . basename($path);
	        
	        $ret = $ret->merge(self::discoverModuleClasses($subModuleNamespace, $path));
	    }
	    
	    return $ret->all();
	}
	
	/**
	 * Common method to use for caching of data within module manager

	 * @return mixed
	 */
	protected static function getCached(string $key, \Closure $default)
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
		File::cleanDirectory(base_path('bootstrap/cache'));
		
		Cache::forget('epesi-modules-installed');
		Cache::forget('epesi-modules-available');
	}
	
	/**
	 * Alias for collect when no return values expected
	 */
	public static function call(string $method, array $args = []): void
	{
		self::collect($method, $args);
	}
	
	/**
	 * Collect array of results from $method in all installed module core classes
	 */
	public static function collect(string $method, array $args = []): array
	{
		$installedModules = collect(self::getInstalled());
		
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
	 */
	public static function install(string $classOrAlias, bool $installRecommended = true)
	{
		if (self::isInstalled($classOrAlias)) {
			print sprintf('Module "%s" already installed!', $classOrAlias);
			
			return true;
		}
		
		if (! $moduleClass = self::getClass($classOrAlias)) {			
			throw new \Exception(sprintf('Module "%s" could not be identified', $classOrAlias));
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
		Module::create()->insert([
				'class' => $moduleClass,
				'alias' => $module->alias()
		]);
		
		if ($installRecommended) {
			$installRecommended = is_array($installRecommended)? $installRecommended: $module->recommended();
			
			foreach ($installRecommended as $recommendedModule) {
				try {
					self::install($recommendedModule);
				} catch (\Exception $e) {
					// just continue, nothing to do if module cannot be installed
				}
			}
		}
				
		self::clearCache();
		
		print sprintf('Module "%s" successfully installed!', $module->label());
		
		return true;
	}
	
	/**
	 * Install modules that $moduleClass requires
	 * Performs operation recursively for all required modules
	 */
	protected static function satisfyDependencies(string $moduleClass): bool
	{
		self::$processing[$moduleClass] = true;
		
		while ($unsatisfiedDependencies = self::unsatisfiedDependencies($moduleClass)) {
			$parentModule = array_shift($unsatisfiedDependencies);
				
			if (self::$processing[$parentModule]?? false) {
				throw new \Exception(sprintf('Cross dependency: %s', $parentModule));
			}
				
			if (! self::isAvailable($parentModule)) {
				throw new \Exception(sprintf('Module "%s" not found!', $parentModule));
			}
	
			print("\n\r");
			print sprintf('Installing required module: "%s" by "%s"', $parentModule, $moduleClass);

			self::install($parentModule);
		}

		unset(self::$processing[$moduleClass]);
		
		return true;
	}
	
	/**
	 * Finds modules which are required by $moduleClass but not yet installed
	 */
	protected static function unsatisfiedDependencies(string $moduleClass): array
	{
		return collect($moduleClass::requires())->diff(self::getInstalled())->filter()->all();
	}	
	
	/**
	 * Finds $moduleClass dependencies recursively (including dependencies of dependencies)
	 */
	public static function listDependencies(string $moduleClass): array
	{
		$ret = collect();
		foreach (collect($moduleClass::requires()) as $parentClass) {
			$ret->add($parentClass = self::getClass($parentClass));
			
			$ret = $ret->merge(self::listDependencies($parentClass));
		}
		
		return $ret->filter()->unique()->all();
	}
	
	/**
	 * Finds $moduleClass recommended modules recursively (including recommended of recommended)
	 */
	public static function listRecommended(string $moduleClass): array
	{
		$ret = collect();
		foreach (collect($moduleClass::recommended()) as $childClass) {
			$ret->add($childClass = self::getClass($childClass));
			
			$ret = $ret->merge(self::listRecommended($childClass));
		}
		
		return $ret->filter()->unique()->all();
	}
	
	/**
	 * Creates array of dependencies of installed modules
	 */
	public static function listDependents(): array
	{
		$ret = [];
		foreach (self::getInstalled() as $moduleClass) {
			foreach ($moduleClass::requires() as $parentClass) {
				$ret[$parentClass][] = $moduleClass;
			}
		}
		return $ret;
	}
	
	/**
	 * Runs uninstallation routine on $classOrAlias
	 */
	public static function uninstall(string $classOrAlias): bool
	{
		if (! self::isInstalled($classOrAlias)) {
			print sprintf('Module "%s" is not installed!', $classOrAlias);
			
			return true;
		}
		
		if (! $moduleClass = self::getClass($classOrAlias)) {
			throw new \Exception(sprintf('Module "%s" could not be identified', $classOrAlias));
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
		Module::create()->addCondition('class', $moduleClass)->tryLoadAny()->delete();
		
		self::clearCache();
		
		print sprintf('Module "%s" successfully uninstalled!', $module->label());
		
		return true;
	}
}
