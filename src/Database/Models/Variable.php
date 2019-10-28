<?php

namespace Epesi\Core\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Variable extends Model
{
	public $timestamps = false;
	protected $primaryKey = 'name';
	protected $fillable = ['name', 'value'];
	
	/**
	 * @var Collection
	 */
	private static $variables;
	
	private static function cache() {
		if(isset(self::$variables)) return;
		
		self::$variables = self::pluck('value', 'name');
	}
	
	public static function get($name, $throwError = true) {
		self::cache();
		
		if (! self::$variables->has($name) && $throwError) {
			throw new \Exception('No such variable in database: ' . $name);
		}
		
		return self::$variables->get($name, '');
	}
	
	public static function put($name, $value) {
		$maxLength = 128;
		
		if (strlen($name) > $maxLength) {
			throw new \Exception("Variable name too long. Max length is $maxLength.");
		}
		
		self::cache();
		
		self::$variables->put($name, $value);
		
		return self::updateOrCreate(compact('name'), compact('value'));
	}
	
	public static function forget($name, $throwError=true) {
		self::cache();
		
		if (! self::$variables->has($name) && $throwError) {
			throw new \Exception('No such variable in database: ' . $name);
		}
		
		self::$variables->forget($name);
			
		return self::destroy($name);
	}
}
