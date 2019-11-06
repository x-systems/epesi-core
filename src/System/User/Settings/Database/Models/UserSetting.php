<?php

namespace Epesi\Core\System\User\Settings\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class UserSetting extends Model
{
	public $timestamps = false;
	protected $fillable = ['user_id', 'group', 'name', 'value'];
	
	/**
	 * @var Collection
	 */
	private static $userVariables;
	private static $adminVariables;
	
	private static function cache() {
		self::cacheUserVariables();
		self::cacheAdminVariables();
	}
	
	private static function cacheUserVariables() {
		if(isset(self::$userVariables)) return;

		$userId = Auth::id();
		
		foreach (self::where('user_id', $userId)->get() as $row) {
			self::$userVariables[$userId][$row['group']][$row['name']] = $row['value'];
		}
	}
	
	private static function cacheAdminVariables() {
		if(isset(self::$adminVariables)) return;

		foreach (self::where('user_id', 0) as $row) {
			self::$adminVariables[$row['group']][$row['name']] = $row['value'];
		}
	}
	
	public static function get($group, $name, $user = null) {
		$user = $user?: Auth::id();
		
		if (!$user || !is_numeric($user)) return;
		
		self::cache();
		
		return self::$userVariables[$user][$group][$name]?? self::getAdmin($group, $name);
	}
	
	public static function getGroup($group, $user = null) {
		$user = $user?: Auth::id();
		
		if (!$user || !is_numeric($user)) return;
		
		self::cache();

		return self::$userVariables[$user][$group]?? [];
	}
	
	public static function getAdmin($group, $name) {
		self::cache();
		
		return self::$adminVariables[$group][$name]?? null;
	}
	
	public static function getAdminGroup($group) {
		self::cache();
		
		return self::$adminVariables[$group]?? [];
	}
	
	public static function put($group, $name, $value, $user = null) {
		$user_id = $user?: Auth::id();
		
		if (!$user_id || !is_numeric($user_id)) return;
		
		self::cache();

		self::$userVariables[$user_id][$group][$name] = $value;
		
		return self::updateOrCreate(compact('user_id', 'group', 'name'), compact('value'));
	}
	
	public static function putGroup($group, $values, $user = null) {
		foreach ($values as $name => $value) {
			self::put($group, $name, $value, $user);
		}
	}
	
	public static function putAdmin($group, $name, $value, $user = null) {
		self::cache();

		self::$adminVariables[$group][$name] = $value;
		
		$user_id = 0;
		
		return self::updateOrCreate(compact('user_id', 'group', 'name'), compact('value'));
	}
	
	public static function forget($group, $name, $user = null) {
		$user_id = $user?: Auth::id();
		
		if (!$user_id || !is_numeric($user_id)) return;
		
		self::cache();
		
		unset(self::$userVariables[$user_id][$group][$name]);
			
		return self::where(compact('user_id', 'group', 'name'))->delete();
	}
	
	public static function forgetAdmin($group, $name) {
		self::cache();
		
		unset(self::$adminVariables[$group][$name]);
			
		$user_id = 0;
		
		return self::where(compact('user_id', 'group', 'name'))->delete();
	}
}
