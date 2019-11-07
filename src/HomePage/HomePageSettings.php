<?php

namespace Epesi\Core\HomePage;

use Epesi\Core\System\Integration\Modules\ModuleView;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\HomePage\Database\Models\HomePage;
use Epesi\Core\Layout\Seeds\ActionBar;
use Epesi\Core\System\Seeds\Form;
use Spatie\Permission\Models\Role;
use atk4\data\Persistence;
use atk4\data\Persistence_Array;

class HomePageSettings extends ModuleView
{
	protected $label = 'Home Page Administration';
	
	protected $homepages;
	protected $grid;
	protected $form;
	
	public static function access()
	{
		return Auth::user()->can('modify system settings') && HomePageCommon::getAvailableHomePages();
	}
	
	public function body()
	{
		ActionBar::addButton('back')->link(url('view/system'));

		$this->grid = $this->add([
				'CRUD',
				'itemCreate' => ActionBar::addButton('add'),
				'formCreate' => $this->getHomepageForm(),
				'formUpdate' => $this->getHomepageForm(),
				'notifyDefault' => ['jsNotify', 'content' => __('Data is saved!'), 'color'   => 'green'],
				'canDelete' => false, //use custom delete routine
				'paginator' => false,
				'menu' => false
		]);
		
		$this->grid->setModel($this->getModel());
		
		$availablePages = HomePageCommon::getAvailableHomePages();

		$this->grid->addDecorator('path', ['Multiformat', function($row, $column) use ($availablePages) {
			return [['Template', $availablePages[$row[$column]]?? '[' . __('missing: :path', ['path' => $row[$column]]) . ']']];
		}]);
		
		$this->grid->addDragHandler()->onReorder(function ($order) {
			$result = true;
			foreach ($this->getHomepages() as $homepage) {
				$homepage->priority = array_search($homepage->id, $order);
				
				$result &= $homepage->save();
			}
			
			return $result? $this->notify(__('Homepages reordered!')): $this->notifyError(__('Error saving order!'));
		});
		
		$this->grid->addAction(['icon' => 'red trash'], function ($jschain, $id) {
			HomePage::find($id)->delete();
				
			return $jschain->closest('tr')->transition('fade left');
		}, __('Are you sure?'));
	}
	
	public function getModel()
	{
		$availablePages = HomePageCommon::getAvailableHomePages();
		$availableRoles = Role::get()->pluck('name')->all();

		$rows = [];
		foreach ($this->getHomepages() as $homepage) {
			$rows[] = [
					'id' => $homepage->id,
					'path' => $homepage->path,
					'role' => $homepage->role
			];
		}
		
		$rowsEmpty = [];
		
		$model = new \atk4\data\Model($rows? new \atk4\data\Persistence_Static($rows): new \atk4\data\Persistence_Array($rowsEmpty));
	
		$pathField = $rows? $model->hasField('path'): $model->addField('path');
			
		$roleField = $rows? $model->hasField('role'): $model->addField('role');
		
		$pathField->setDefaults(['caption' => __('Page'), 'values' => $availablePages]);

		$roleField->setDefaults(['caption' => __('Role'), 'enum' => $availableRoles]);

		return $model;
	}
	
	public function getHomepages()
	{
		return $this->homepages = $this->homepages?? HomePage::orderBy('priority')->get();
	}
	
	public function getHomepageForm()
	{
		if (! $this->form) {		
			$this->form = new Form(['buttonSave' => ['Button', __('Save'), 'primary']]);
	
			$this->form->addHook('submit', function($form) {
				$values = $form->getValues();
				
				if ($id = $values['id']?? null) {
					HomePage::find($id)->update($values);
					
					return $this->grid->jsSaveUpdate();
				}
	
				HomePage::create(array_merge($values, [
						'priority' => HomePage::max('priority') + 1
				]));
	
				return $this->grid->jsSaveCreate();
			});
		}

		return $this->form;
	}
}
