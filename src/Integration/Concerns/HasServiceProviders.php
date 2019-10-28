<?php

namespace Epesi\Core\Integration\Concerns;

trait HasServiceProviders
{
	protected static $providers = [];
	
	final public static function providers() {
		return static::$providers;
	}
}
