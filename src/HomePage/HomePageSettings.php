<?php

namespace Epesi\Core\HomePage;

use Epesi\Core\System\Integration\Modules\ModuleView;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\HomePage\Database\Models\HomePage;
use Epesi\Core\Layout\Seeds\ActionBar;

class HomePageSettings extends ModuleView
{
	protected $label = 'Home Page Administration';
	
	public static function access()
	{
		return Auth::user()->can('modify system settings') && HomePage::list();
	}
	
	public function body()
	{
		ActionBar::addButton('back')->link(url('view/system'));

		$grid = $this->add([
				'CRUD',
				'displayFields' => ['path', 'role'],
				'editFields' => ['path', 'role'],
				'notifyDefault' => ['jsNotify', 'content' => __('Data is saved!'), 'color' => 'green'],
				'paginator' => false
		]);
		
		$grid->setModel(HomePage::create());

		$grid->addDragHandler()->onReorder(function ($order) {
			$result = true;
			foreach (HomePage::create() as $homepage) {
				$homepage['priority'] = array_search($homepage['id'], $order);
				
				$result &= $homepage->save();
			}
			
			return $result? $this->notify(__('Homepages reordered!')): $this->notifyError(__('Error saving order!'));
		});
	}
}
