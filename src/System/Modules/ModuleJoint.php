<?php 

namespace Epesi\Core\System\Modules;

use Illuminate\Support\Collection;

abstract class ModuleJoint
{
	use Concerns\HasLinks;
	use Concerns\HasAccessControl;
	use Concerns\HasPackageManifest;

	/**
	 * List of runtime registered joints
	 * Can be used for tesing purposes
	 * 
	 * @var array
	 */
	protected static $registry = [];
	
	/**
	 * 	Make all joints which are applicable to static class and user has access to
	 *
	 * @return 	Collection
	 */
	final public static function collect()
	{
		$ret = collect();
		foreach (self::list() as $class) {
			/** @var ModuleJoint $joint */
			$joint = new $class();

			if ($joint->access()) {
				$ret->add($joint);
			}
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
			->merge(self::$registry)
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
		foreach (ModuleManager::getInstalled() as $module) {
			$ret = $ret->merge($module::joints());
		}

		return $ret->all();
	}
	
	final public static function register($class)
	{
		self::$registry[] = $class;
	}
}