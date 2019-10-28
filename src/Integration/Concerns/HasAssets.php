<?php

namespace Epesi\Core\Integration\Concerns;

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
	public static function public()
	{
		$source = implode(DIRECTORY_SEPARATOR, [static::path(), 'Public']);

		return is_dir($source)? [
				$source => self::publicPath()
		]: [];
	}
		
	/**
	 * Path to the module public directory.
	 *
	 * @return string path to the module directory
	 */
	final public static function publicPath() {
		return public_path('modules' . DIRECTORY_SEPARATOR . self::alias());
	}
}
