<?php

namespace Epesi\Core\Integration\Concerns;

trait HasVariables
{
	use HasModule;
	
	final public function putModuleVariable($name, $value = null) {
		session()->put($this->id . '__' . $name, $value);
		session()->save();
	}
	
	final public function getModuleVariable($name, $default = null) {
		return session()->get($this->id . '__' . $name, $default);
	}

	final public static function putStaticModuleVariable($name, $value = null) {
		session()->put(static::module() . '__' . $name, $value);
		session()->save();
	}
	
	final public static function getStaticModuleVariable($name, $default = null) {
		return session()->get(static::module() . '__' . $name, $default);
	}
}
