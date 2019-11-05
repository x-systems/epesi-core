<?php

namespace Epesi\Core\HomePage;

use Epesi\Core\HomePage\Integration\Joints\HomePageJoint;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\HomePage\Database\Models\HomePage;

class HomePageCommon
{
	/**
	 * Fallback path in case no home page set for the user
	 * 
	 * @var string
	 */
	protected static $defaultPath = 'view/user.settings';
	
	/**
	 * Collect all home pages from module joints
	 * 
	 * @return array
	 */
	public static function getAvailableHomePages()
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
	 * @return HomePage
	 */
	public static function getUserHomePage()
	{
		if (! $user = Auth::user()) return;

		return HomePage::whereIn('role', $user->roles()->pluck('name'))->orderBy('priority')->first();
	}

	/**
	 * Get the current user home page path
	 * 
	 * @return HomePage
	 */
	public static function getUserHomePagePath()
	{
		$homepage = self::getUserHomePage();
		
		return $homepage? $homepage->path: self::$defaultPath;
	}
}
