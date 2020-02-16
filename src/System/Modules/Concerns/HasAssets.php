<?php

namespace Epesi\Core\System\Modules\Concerns;

use Illuminate\Support\Facades\File;

trait HasAssets
{
	use HasModule;
	
	/**
	 * Files to publish in the system
	 * By default files in the Public subfolder are published as assets
	 * to the public path under modules/<module alias>
	 *
	 * @return string
	 */
	public static function assets()
	{
		return implode(DIRECTORY_SEPARATOR, [static::path(), 'Assets']);
	}
	
	/**
	 * Path to the module public directory.
	 *
	 * @return string path to the module directory
	 */
	public static function publicPath() {
		return storage_path(implode(DIRECTORY_SEPARATOR, ['app', 'public', self::alias()]));
	}
	
	final public static function publishAssets()
	{
		File::copyDirectory(self::assets(), self::publicPath());
	}
	
	final public static function unpublishAssets()
	{
		File::deleteDirectory(self::publicPath());
	}
}
