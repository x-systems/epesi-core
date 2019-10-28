<?php

namespace Epesi\Core\Integration\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasStorage
{
	use HasModule;

	/**
	 * Creates default data directory for module.
	 *
	 * Do not use directly.
	 *
	 * @param $module string
	 * @return bool true if directory was created or already exists, false otherwise
	 */
	public static final function createStorage($module) {
		return Storage::makeDirectory(self::getStorage($module));
	}
	
	/**
	 * Removes default data directory of a module.
	 *
	 * Do not use directly.
	 *
	 * @param $module string 
	 * @return bool true if directory was removed or did not exist, false otherwise
	 */
	final public static function removeStorage($module) {
		return Storage::deleteDirectory(self::getStorage($module));
	}
	
	final public static function getStorage($module) {
		return self::$storage . '/' . str_ireplace('\\', '_', $module) . '/';
	}
}
