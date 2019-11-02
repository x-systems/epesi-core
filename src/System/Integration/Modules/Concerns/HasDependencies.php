<?php

namespace Epesi\Core\System\Integration\Modules\Concerns;

trait HasDependencies
{
	protected static $requires = [];

	final public static function requires()
	{
		return static::$requires;
	}
	
}
