<?php 

namespace Epesi\Core\System\Integration\Modules\Concerns;

use Illuminate\Filesystem\Filesystem;
use Epesi\Core\System\Integration\Modules\PackageManifest;

trait HasPackageManifest
{
	protected static $packageManifest;
	
	final public static function packageManifest()
	{
		return self::$packageManifest = self::$packageManifest?: new PackageManifest(new Filesystem(), app()->basePath(), self::getCachedManifestPath());
	}
	
	final public static function getCachedManifestPath()
	{
		return app()->bootstrapPath() . '/cache/epesi.php';
	}
}