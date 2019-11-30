<?php

namespace Epesi\Core\Data;

use atk4\data\Model as AtkModel;

class Model extends AtkModel
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
	
	public function addCrits($crits = [])
	{
		foreach ($crits as $condition) {
			$this->addCondition(...$condition);
		}
		
		return $this;
	}
}