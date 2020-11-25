<?php

namespace Epesi\Core\Data;

trait HasEpesiConnection
{
	/**
	 * Create atk4 model and assign default persistence
	 * 
	 * @param array $defaults
	 * 
	 * @return \atk4\data\Model
	 */
	public static function create($defaults = [])
	{
		$atkDb = app()->make(Persistence\SQL::class);
		
		return new static($atkDb, $defaults);
	}
	
	public static function migrate()
	{
		return (new \atk4\schema\Migration(static::create()))->create();
	}
	
	/**
	 * Get the values of a given key.
	 *
	 * @param  string|array  $value
	 * @param  string|null  $key
	 * @return static
	 */
	public static function pluck($value, $key = null)
	{
	    return collect(self::create()->export())->pluck($value, $key);
	}
}