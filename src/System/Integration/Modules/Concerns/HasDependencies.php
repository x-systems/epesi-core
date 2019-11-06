<?php

namespace Epesi\Core\System\Integration\Modules\Concerns;

trait HasDependencies
{
	/**
	 * List of module classes / aliases to be installed BEFORE the module itself
	 * Module will not be installed if required modules cannot be installed
	 * 
	 * @var array
	 */
	protected static $requires = [];
	
	/**
	 * List of module classes / aliases to be installed AFTER the module itself
	 * Modules will be installed only if available and do not cause failure when missing
	 *
	 * @var array
	 */
	protected static $recommends = [];

	final public static function requires()
	{
		return static::$requires;
	}
	
	final public static function recommended()
	{
		return static::$recommends;
	}
}
