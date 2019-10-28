<?php 

namespace Epesi\Core\Integration\Module;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Epesi\Core\Integration\Concerns\HasLinks;
use Epesi\Core\Database\Models\Module;
use Epesi\Core\Integration\Concerns\HasAccessControl;

abstract class ModuleJoint
{
	use HasLinks;
	use HasAccessControl;
	
	protected static $packageManifest;
	
	/**
	 * 	Make all joints which are applicable to static class and user has access to
	 *
	 * @return 	Collection
	 */
	final public static function collect()
	{
		$ret = collect();
		foreach (self::list() as $class) {
			/**
			 * @var ModuleJoint $joint
			 */
			$joint = new $class();

			if (! $joint->access()) continue;
			
			$ret->add($joint);
		}
		
		return $ret;
	}
		
	/**
	 * List all registered joints which are subclasses of static
	 */
	final public static function list()
	{
		$joints = collect(config('epesi.joints'))
			->merge(self::packageManifest()->joints())
			->merge(self::moduleJoints())
			->unique();
			
		return $joints->filter(function($class) {
			return is_a($class, static::class, true);
		});
	}
	
	/**
	 * Collect all joints declared in the modules and return array
	 *
	 * @return array
	 */
	final public static function moduleJoints()
	{
		$ret = collect();
		foreach (Module::getInstalled() as $module) {
			$ret = $ret->merge($module::joints());
		}
		
		return $ret->all();
	}
	
	final public static function packageManifest()
	{
		return self::$packageManifest = self::$packageManifest?: new PackageManifest(new Filesystem(), app()->basePath(), self::getCachedManifestPath());
	}
	
	final public static function getCachedManifestPath()
	{
		return app()->bootstrapPath() . '/cache/epesi.php';
	}
}