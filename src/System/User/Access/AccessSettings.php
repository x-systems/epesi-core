<?php

namespace Epesi\Core\System\User\Access;

use Epesi\Core\System\User\Database\Models\User;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use atk4\data\Model;
use Epesi\Core\System\View\Form;
use Epesi\Core\Layout\View\ActionBar;
use Epesi\Core\System\Modules\ModuleView;
use atk4\data\Persistence\Static_;
use atk4\ui\Table;
use atk4\ui\Button;
use atk4\ui\Columns;

class AccessSettings extends ModuleView
{
	protected $label = ['System Settings', 'User Access'];
	
	protected $columns;
	protected $reload;
	
	public function body()
	{
		ActionBar::addItemButton('back')->link(url('view/system'));
		
		$this->showEditPermissions();
		
		$this->addGrantRoleAccessButton();
		
		$this->addGrantUserAccessButton();
	}
	
	protected function showEditPermissions()
	{
		$permissionsData = Permission::all(['id', 'name'])->map(function($permission){
			$permission['name'] = trans(ucwords($permission['name']));
			
			return $permission;
		})->toArray();

		$table = $this->columns()->addColumn()->add([Table::class, 'selectable'])->addStyle('cursor', 'pointer');
		$table->setModel($this->getModel($permissionsData), false);
		
		$table->addColumn('name', Table\Column\Text::class, ['caption' => __('Permissions')]);
		
		$table->addColumn(null, new Table\Column\Template([['i', 'class' => 'indicator arrow circle right icon']]), ['caption' => 'Test'])->setAttr('class', ['right aligned']);
		
		$this->getApp()->addStyle('
			table.selectable tr:not(.active) .indicator {
				display: none;
			}'
		);
		
		$table->on('click', 'tr', $this->reload(new \atk4\ui\JsExpression('$(this).data("id")')));
		
		if ($permissionId = $this->permissionId()) {
			$permission = Permission::findById($permissionId);
			
			$this->putModuleVariable('permission', $permissionId);
			
			$table->js(true)->find("tr[data-id=$permission->id]")->addClass('active');
			
			$rolesData = $permission->roles()->get()->map(function($role) {
				$role['name'] = trans($role['name']);
				
				return $role;
			})->toArray();

			$column = $this->columns()->addColumn();
			$rolesTable = Table::addTo($column);
			$rolesTable->setModel($this->getModel($rolesData), false);
			
			$rolesTable->addColumn('name', [Table\Column\Text::class], ['caption' => __('Roles allowed to :permission', ['permission' => $permission->name])]);
			
			$roleActions = $rolesTable->addColumn(null, [Table\Column\ActionButtons::class]);
			$roleActions->addButton($this->deleteButton(), function($jQuery, $roleId) use ($permission) {
				Role::findById($roleId)->revokePermissionTo($permission);
				
				return $this->reload();
			}, __('Revoke permission to role?'));
				
			$usersData = $permission->users()->get()->toArray();

			$usersTable = Table::addTo($column);
			$usersTable->setModel($this->getModel($usersData), false);
				
			$usersTable->addColumn('name', [Table\Column\Text::class], ['caption' => __('Users allowed to :permission', ['permission' => $permission->name])]);
				
			$userActions = $usersTable->addColumn(null, [Table\Column\ActionButtons::class]);
			$userActions->addButton($this->deleteButton(), function($jQuery, $userId) use ($permission) {
				User::find($userId)->revokePermissionTo($permission);
					
				return $this->reload();
			}, __('Revoke permission to user?'));
		}
	}
	
	protected function columns()
	{
		return $this->columns = $this->columns?: Columns::addTo($this);
	}
	
	protected function permissionId()
	{
		return $this->getApp()->stickyGet($this->columns()->name)?: $this->getModuleVariable('permission');
	}
	
	protected function reload($permissionExpression = null)
	{
		$columns = $this->columns();
		
		return $this->reload = $this->reload?: new \atk4\ui\JsReload($columns, [$columns->name => $permissionExpression?: '']);
	}
	
	protected function getModel($array)
	{
		return new Model(new Static_($array));
	}
	
	protected function addGrantRoleAccessButton()
	{
		$modal = \atk4\ui\Modal::addTo($this, ['title' => __('Grant Role Access')])->set(function($view) {
			$form = Form::addTo($view, ['buttonSave' => ['Button', __('Save'), 'primary']]);
			
			$form->addControl('role', [\atk4\ui\Form\Control\Dropdown::class, 'caption' => __('Grant'), 'values' => Role::all()->pluck('name', 'id')])->set($this->getModuleVariable('permission'));
			
			$form->addControl('permission', [\atk4\ui\Form\Control\Dropdown::class, 'caption' => __('Access To'), 'values' => Permission::all()->pluck('name', 'id')])->set($this->getModuleVariable('permission'));

			$form->layout->addButton([Button::class, __('Cancel')])->on('click', $view->getOwner()->hide());

			$form->onSubmit(function($form) use ($view) {
				$values = $form->getValues();
				
				Role::findById($values['role'])->givePermissionTo($values['permission']);
				
				return [
						$view->getOwner()->hide(),
						$this->reload()
				];
			});
		});
		
		ActionBar::addItemButton([__('Grant Role Access'), 'icon' => 'group'])->on('click', $modal->show());
	}
	
	protected function addGrantUserAccessButton()
	{
		$modal = \atk4\ui\Modal::addTo($this, ['title' => __('Grant User Access')])->set(function($view) {
			$form = Form::addTo($view, ['buttonSave' => [Button::class, __('Save'), 'primary']]);
			
			$form->addControl('user', [\atk4\ui\Form\Control\Dropdown::class, 'caption' => __('Grant'), 'values' => User::all()->pluck('name', 'id')]);
			
			$form->addControl('permission', [\atk4\ui\Form\Control\Dropdown::class, 'caption' => __('Access To'), 'values' => Permission::all()->pluck('name', 'id')])->set($this->getModuleVariable('permission'));
			
			$form->layout->addButton([Button::class, __('Cancel')])->on('click', $view->getOwner()->hide());
			
			$form->onSubmit(function($form) use ($view) {
				$values = $form->getValues();
				
				User::find($values['user'])->givePermissionTo($values['permission']);
				
				return [
						$view->getOwner()->hide(),
						$this->reload()
				];
			});
		});
		
		ActionBar::addItemButton([__('Grant User Access'), 'icon' => 'user', ])->on('click', $modal->show());
	}
	protected function deleteButton()
	{
		return ['icon' => 'trash', 'class' => ['red'], 'attr' => ['title' => __('Revoke Permission')]];
	}
}
