<?php

namespace Epesi\Core\HomePage\Models;

use atk4\data\Model;
use Epesi\Core\Data\HasEpesiConnection;
use Spatie\Permission\Models\Role;
use Epesi\Core\HomePage\Integration\Joints\HomePageJoint;
use Illuminate\Support\Facades\Auth;

class HomePage extends Model
{
    use HasEpesiConnection;
    
	public $table = 'home_pages';
	
	public $caption = 'Home Page';
	
	public $title_field = 'path';
	
	/**
	 * Fallback path in case no home page set for the user
	 *
	 * @var string
	 */
	protected static $defaultPath = 'view/user.settings';
	
	function init() {
		parent::init();
		
		$this->addFields([
		        'path' => [
						'type' => 'enum',
						'caption' => __('Page'),
						'values' => self::list(),
						'ui' => [
								'table' => [
										'KeyValue',
								],
								'filter' => true
						],
				],
		        'role' => [
						'type' => 'enum', 
						'caption' => __('Role'), 
						'values' => Role::get()->pluck('name', 'name')->all(), 
						'ui' => [
								'filter' => true
						]
				],
		        'date' => [
						'type' => 'date', 
						'caption' => __('Date'),
		                'never_persist' => true,
						'ui' => [
								'filter' => true
						]
				],
				'priority' => [
				        'default' => 0
				],
		]);
		
		$this->setOrder('priority');

		$this->addHook('beforeInsert', function($model, & $data) {
			$data['priority'] = $data['priority']?: $this->action('fx', ['max', 'priority'])->getOne() + 1;
		});
	}
	
	/**
	 * Collect all home pages from module joints
	 *
	 * @return array
	 */
	public static function list()
	{
		static $cache;
		
		if (! isset($cache)) {
			$cache = [];
			foreach (HomePageJoint::collect() as $joint) {
				$cache[$joint->link()] = $joint->caption();
			}
		}
		
		return $cache;
	}
		
	/**
	 * Get the current user home page
	 *
	 * @return HomePage|null
	 */
	public static function ofUser()
	{
		if (! $user = Auth::user()) return;
		
		return self::create()->addCondition('role', $user->roles()->pluck('name')->toArray())->loadAny();
	}
	
	/**
	 * Get the current user home page path
	 *
	 * @return string
	 */
	public static function pathOfUser()
	{
		return HomePage::ofUser()['path']?: self::$defaultPath;
	}
}
