<?php

namespace Epesi\Core\Integration\Concerns;

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
	 * Url to the module public directory.
	 *
	 * @return string path to the module directory
	 */
	final public static function assetUrl($asset = '') {
		return url(implode('/', ['modules', self::alias(), $asset]));
	}
}
