<?php

namespace Epesi\Core\Database\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
	public $timestamps = false;
	
	/**
	 * @var \Illuminate\Support\Collection
	 */
	private static $cache;
	
	private static function cache() {
		if(isset(self::$cache)) return;
		
		self::$cache = self::pluck('class', 'alias');
	}
	
	public static function isInstalled($classOrAlias) {
		return self::getClass($classOrAlias)? 1: 0;
	}
	
	public static function getClass($classOrAlias) {
		self::cache();
		
		if (self::$cache->contains($classOrAlias)) return $classOrAlias;
		
		return self::$cache[$classOrAlias]?? null;
	}
	
	public static function getInstalled() {
		self::cache();
		
		return self::$cache;
	}
	
	/**
	 * Collect array of results from $method in all installed module core classes
	 *
	 * @param string $method
	 * @return array
	 */
	public static function collect($method)
	{
		$ret = [];
		foreach (self::getInstalled() as $module) {
			if (! $list = $module::$method()) continue;
			
			$ret = array_merge($ret, is_array($list)? $list: [$list]);
		}
		
		return $ret;
	}
}
