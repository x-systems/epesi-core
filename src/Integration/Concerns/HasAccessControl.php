<?php 

namespace Epesi\Core\Integration\Concerns;

trait HasAccessControl
{
	/**
	 * Define user access
	 */
	public static function access() {
		return true;
	}
	
	/**
	 * Define elements for access level selection
	 */
	public static function accessLevelElements() {}
}