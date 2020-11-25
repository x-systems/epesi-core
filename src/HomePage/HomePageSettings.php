<?php

namespace Epesi\Core\HomePage;

use Epesi\Core\System\Modules\ModuleView;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\Layout\View\ActionBar;
use atk4\ui\GridPlugin\Filter;
use atk4\ui\Crud;

class HomePageSettings extends ModuleView
{
	protected $label = 'Home Page Administration';
	
	public static function access()
	{
		return Auth::user()->can('modify system settings') && Model\HomePage::list();
	}
	
	public function body()
	{
		ActionBar::addItemButton('back')->link(url('view/system'));

		$grid = $this->add([
				Crud::class,
		          'menu' => ActionBar::instance(),
// 		        'model' => HomePage::create(),
				'displayFields' => ['path', 'role'],
				'editFields' => ['path', 'role'],
				'notifyDefault' => ['jsNotify', 'content' => __('Data is saved!'), 'color' => 'green'],
				'paginator' => false,
// 		        'plugins' => [
// 		                'quickSearch' => true,//['path', 'role'],
// 		                'paginator' => false
// 		        ]
		]);
		
		$grid->setModel(Model\HomePage::create());
		
// 		$grid->model->getField('path')->ui['filter'] = [
// 				'values' => ['aa', 'bb'],
// 				'callback' => function($plugin, $value) {
// 					$plugin->owner->model->addCondition('path', 'view/dashboard');
// 				}
// 		];

// 		$grid->add(Filter::class);

// 		$grid->getElement('filter')->form->addField('test', ['CheckBox', 'ui' => ['filter' => ['callback' => function($filter, $key, $value){$filter->owner->model->addCondition('path', null);}]]]);

// 		$plugin->extendList('path');

		$grid->addDragHandler()->onReorder(function ($order) {
			foreach (Model\HomePage::create() as $homepage) {
				$homepage->save(['priority' => array_search($homepage['id'], $order)]);
			}
			
			return $this->notifySuccess(__('Homepages reordered!'));
		});
	}
}
