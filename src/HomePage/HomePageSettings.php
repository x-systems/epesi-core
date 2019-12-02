<?php

namespace Epesi\Core\HomePage;

use Epesi\Core\System\Integration\Modules\ModuleView;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\HomePage\Database\Models\HomePage;
use Epesi\Core\Layout\Seeds\ActionBar;
use Epesi\Core\HomePage\Integration\Joints\HomePageJoint;

class HomePageSettings extends ModuleView
{
	protected $label = 'Home Page Administration';
	
	protected $homepages;
	protected $grid;
	protected $form;
	
	/**
	 * Fallback path in case no home page set for the user
	 *
	 * @var string
	 */
	protected static $defaultPath = 'view/user.settings';
	
	public static function access()
	{
		return Auth::user()->can('modify system settings') && self::getAvailableHomePages();
	}
	
	public function body()
	{
		ActionBar::addButton('back')->link(url('view/system'));

		$this->grid = $this->add([
				'CRUD',
				'displayFields' => ['path', 'role'],
				'editFields' => ['path', 'role'],
				'notifyDefault' => ['jsNotify', 'content' => __('Data is saved!'), 'color' => 'green'],
				'paginator' => false
		]);
		
		$this->grid->setModel(HomePage::create());

		$availablePages = self::getAvailableHomePages();

		$this->grid->addDecorator('path', ['Multiformat', function($row, $column) use ($availablePages) {
			return [['Template', $availablePages[$row[$column]]?? '[' . __('missing: :path', ['path' => $row[$column]]) . ']']];
		}]);
		
		$this->grid->addDragHandler()->onReorder(function ($order) {
			$result = true;
			foreach (HomePage::create() as $homepage) {
				$homepage['priority'] = array_search($homepage['id'], $order);
				
				$result &= $homepage->save();
			}
			
			return $result? $this->notify(__('Homepages reordered!')): $this->notifyError(__('Error saving order!'));
		});
	}

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

		return HomePage::create()->addCondition('role', $user->roles()->pluck('name')->toArray())->loadAny();
	}
	
	/**
	 * Get the current user home page path
	 *
	 * @return HomePage
	 */
	public static function getUserHomePagePath()
	{
		$homepage = self::getUserHomePage();
		
		return $homepage['path']?? self::$defaultPath;
	}
}
