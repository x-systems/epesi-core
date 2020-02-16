<?php

namespace Epesi\Core\System\Modules\Concerns;

use Epesi\Core\System\Modules\ModuleView;
use Illuminate\Support\Str;
use atk4\core\SessionTrait;

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
		
		$viewAlias = null;
		if ($viewClass !== $defaultView) {
			$names = array_slice(explode('\\', $viewClass), -1);
			
			$viewAlias = str_ireplace('_', '-', Str::snake(reset($names)));
		}
		
		return url(implode('/', ['view', implode(':', array_filter([self::alias(), $viewAlias])), $method, self::encodeArgs($args)]));
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
		
		if (is_array($method) && !$args) {
		    $args = $method;
		    $method = 'body';
		}
		
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
		
		return (array) $args;
	}
	
	/**
	 * Encode arguments for the module method
	 * 
	 * @param array|mixed $args
	 * @return string
	 */
	final public static function encodeArgs($args) {
		$args = (array) $args;
		
		if (! $args) return;
		
		$hash = md5(serialize($args));
		
		session([$hash => $args]);
		
		return $hash;
	}
}
