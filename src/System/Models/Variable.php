<?php

namespace Epesi\Core\System\Models;

use atk4\data\Model;
use Epesi\Core\Data\HasEpesiConnection;
use Illuminate\Database\Eloquent\Collection;

class Variable extends Model
{
    use HasEpesiConnection;
    
	/**
	 * @var Collection
	 */
	private static $variables;
	
	public $table = 'variables';
	
	public $strict_types = false;
	
	private static function cache() {
		if(isset(self::$variables)) return;
		
		self::$variables = self::pluck('value', 'name');
	}
	
	public function init(): void
	{
	    parent::init();
	    
	    $this->addFields([
	            'name',
	            'value' => ['type' => 'text', 'serialize' => 'json']
	    ]);
	}
	
	public static function recall($name, $default = null) {
		self::cache();
		
		if (! self::$variables->has($name)) return $default;
		
		return self::$variables->get($name, $default)?? $default;
	}
	
	public static function memorize($name, $value) {
		$maxLength = 128;
		
		if (strlen($name) > $maxLength) {
			throw new \Exception("Variable name too long. Max length is $maxLength.");
		}
		
		self::cache();
		
		self::$variables->put($name, $value);
		
		$variable = self::create()->addCondition('name', $name)->tryLoadAny();

		if ($variable->loaded()) {
		    $variable->save(compact('value'));
		}
		else {
		    $variable->insert(compact('name', 'value'));
		}
	}
	
	public static function forget($name, $throwError=true) {
		self::cache();
		
		if (! self::$variables->has($name) && $throwError) {
			throw new \Exception('No such variable in database: ' . $name);
		}
		
		self::$variables->forget($name);
			
		return self::create()->addCondition('name', $name)->tryLoadAny()->delete();
	}
}
