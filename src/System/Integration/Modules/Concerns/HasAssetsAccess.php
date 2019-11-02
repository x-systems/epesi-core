<?php

namespace Epesi\Core\System\Integration\Modules\Concerns;

trait HasAssetsAccess
{
	use HasModule;
	
	final public static function requireDefaultAssets() {
		self::requireCSS();
		self::requireJS();
	}
	
	final public static function requireJS($file = 'default.js', $isAsync = false, $isDefer = false) {
		return epesi()->requireJS(self::assetUrl('js/'. $file), $isAsync, $isDefer);
	}
	
	final public static function requireCSS($file = 'default.css') {
		return epesi()->requireCSS(self::assetUrl('css/'. $file));
	}
	
	/**
	 * Url to this module public assets directory.
	 *
	 * @return string path to the module directory
	 */
	final public static function assetUrl($asset = '') {
		return self::moduleAssetUrl(self::alias(), $asset);
	}
	
	/**
	 * Url to a module public assets directory.
	 *
	 * @return string path to the module directory
	 */
	final public static function moduleAssetUrl($moduleAlias, $asset = '') {
		return asset(implode('/', ['storage', $moduleAlias, $asset]));
	}
}
