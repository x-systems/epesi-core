<?php

namespace Epesi\Core\Integration\Concerns;

use Epesi\Core\Integration\ModuleView;

trait HasLinks
{
	use HasModule;
	
	/**
	 * Create self module link
	 * 
	 * @param string $method
	 * @param array $args
	 * @return string
	 */
	final public static function selfLink($method = 'body', $args = [])
	{
		$defaultView = self::module()::view();
		
		$viewClass = is_a(static::class, ModuleView::class, true)? static::class: $defaultView;
		
		if ($viewClass == $defaultView) $viewClass = null;
		
		return url(implode('/', ['view', implode('_', array_filter([self::alias(), $viewClass])), $method, self::encodeArgs($args)]));
	}
	
	/**
	 * Create module link
	 * Associative $args are used to set module properties
	 * Numeric key values are used as method arguments
	 * 
	 * @param string $module
	 * @param string $method
	 * @param array $args
	 * @return string
	 */
	final public static function moduleLink($module, $method = 'body', $args = [])
	{
		$alias = class_exists($module)? $module::alias(): $module;
		
		return url(implode('/', ['view', $alias, $method, self::encodeArgs($args)]));
	}
	
	/**
	 * Decode the arguments hash and return stored arguments
	 * 
	 * @param string $hash
	 * @return array
	 * 
	 * @throws \Illuminate\Http\Exceptions\HttpResponseException
	 */
	final public static function decodeArgs($hash) {
		if (! $hash) return [];
		
		$args = session($hash);

		if (is_null($args)) abort(419);
		
		return is_array($args)? $args: [$args];
	}
	
	/**
	 * Encode arguments for the module method
	 * 
	 * @param array|mixed $args
	 * @return string
	 */
	final public static function encodeArgs($args) {
		$args = is_array($args)? $args: [$args];
		
		if (! $args) return;
		
		$hash = md5(serialize($args));
		
		session([$hash => $args]);
		
		return $hash;
	}
}
