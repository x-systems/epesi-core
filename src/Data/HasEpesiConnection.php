<?php

namespace Epesi\Core\Data;

trait HasEpesiConnection
{
	/**
	 * Create atk4 model and assign default persistence
	 * 
	 * @param array $defaults
	 * 
	 * @return \Epesi\Core\Data\Model
	 */
	public static function create($defaults = [])
	{
		$atkDb = app()->make(Persistence\SQL::class);
		
		return new static($atkDb, $defaults);
	}
	
	public static function migrate()
	{
		return \atk4\schema\Migration::getMigration(static::create())->migrate();
	}
	
	public function addCrits($crits = [])
	{
		foreach ($crits as $condition) {
			$this->addCondition(...$condition);
		}
		
		return $this;
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
	
	// to be removed for atk4/data 2.0
	public function addFields($fields = [], $defaults = [])
	{
	    foreach ($fields as $key => $field) {
	        if (!is_int($key)) {
	            // field name can be passed as array key
	            $name = $key;
	        } elseif (is_string($field)) {
	            // or it can be simple string = field name
	            $name = $field;
	            $field = [];
	        } elseif (is_array($field) && isset($field[0]) && is_string($field[0])) {
	            // or field name can be passed as first element of seed array (old behaviour)
	            $name = array_shift($field);
	        } else {
	            // some unsupported format, maybe throw exception here?
	            continue;
	        }
	        
	        $seed = array_merge($defaults, (array) $field);
	        
	        $this->addField($name, $seed);
	    }
	    
	    return $this;
	}
}