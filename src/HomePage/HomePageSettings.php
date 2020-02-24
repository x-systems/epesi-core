<?php

namespace Epesi\Core\HomePage;

use Epesi\Core\System\Modules\ModuleView;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\HomePage\Models\HomePage;
use Epesi\Core\Layout\View\ActionBar;

class HomePageSettings extends ModuleView
{
	protected $label = 'Home Page Administration';
	
	public static function access()
	{
		return Auth::user()->can('modify system settings') && HomePage::list();
	}
	
	public function body()
	{
		ActionBar::addItemButton('back')->link(url('view/system'));

		$grid = $this->add([
				'CRUD',
				'displayFields' => ['path', 'role'],
				'editFields' => ['path', 'role'],
				'notifyDefault' => ['jsNotify', 'content' => __('Data is saved!'), 'color' => 'green'],
				'paginator' => false
		]);
		
		$grid->setModel(HomePage::create());

		$grid->addDragHandler()->onReorder(function ($order) {
			foreach (HomePage::create() as $homepage) {
				$homepage->save(['priority' => array_search($homepage['id'], $order)]);
			}
			
			return $this->notifySuccess(__('Homepages reordered!'));
		});
	}
}
