<?php

namespace Epesi\Core\System\User\Access;

use Epesi\Core\System\User\Database\Models\User;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use atk4\data\Model;
use atk4\ui\jsReload;
use atk4\ui\jsExpression;
use atk4\ui\TableColumn\Template;
use atk4\data\Persistence_Static;
use Epesi\Core\System\Seeds\Form;
use Epesi\Core\Layout\Seeds\ActionBar;
use Epesi\Core\System\Integration\Modules\ModuleView;

class AccessSettings extends ModuleView
{
	protected $label = ['System Settings', 'User Access'];
	
	protected $columns;
	protected $reload;
	
	public function body()
	{
		ActionBar::addButton('back')->link(url('view/system'));
		
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

		$table = $this->columns()->addColumn()->add(['Table', 'selectable'])->addStyle('cursor', 'pointer');
		$table->setModel($this->getModel($permissionsData), false);
		
		$table->addColumn('name', 'Text', ['caption' => __('Permissions')]);
		
		$table->addColumn(null, new Template([['i', 'class' => 'indicator arrow circle right icon']]), ['caption' => 'Test'])->setAttr('class', ['right aligned']);
		
		eval_css('
		table.selectable tr:not(.active) .indicator {
			display: none;
		}');
		
		$table->on('click', 'tr', $this->reload(new jsExpression('$(this).data("id")')));
		
		if ($permissionId = $this->permissionId()) {
			$permission = Permission::findById($permissionId);
			
			$this->putModuleVariable('permission', $permissionId);
			
			$table->js(true)->find("tr[data-id=$permission->id]")->addClass('active');
			
			$rolesData = $permission->roles()->get()->map(function($role) {
				$role['name'] = trans($role['name']);
				
				return $role;
			})->toArray();

			$column = $this->columns()->addColumn();
			$rolesTable = $column->add('Table');
			$rolesTable->setModel($this->getModel($rolesData), false);
			
			$rolesTable->addColumn('name', 'Text', ['caption' => __('Roles allowed to :permission', ['permission' => $permission->name])]);
			
			$roleActions = $rolesTable->addColumn(null, 'Actions');
			$roleActions->addAction($this->deleteButton(), function($jQuery, $roleId) use ($permission) {
				Role::findById($roleId)->revokePermissionTo($permission);
				
				return $this->reload();
			}, __('Revoke permission to role?'));
				
			$usersData = $permission->users()->get()->toArray();

			$usersTable = $column->add('Table');
			$usersTable->setModel($this->getModel($usersData), false);
				
			$usersTable->addColumn('name', 'Text', ['caption' => __('Users allowed to :permission', ['permission' => $permission->name])]);
				
			$userActions = $usersTable->addColumn(null, 'Actions');
			$userActions->addAction($this->deleteButton(), function($jQuery, $userId) use ($permission) {
				User::find($userId)->revokePermissionTo($permission);
					
				return $this->reload();
			}, __('Revoke permission to user?'));
		}
	}
	
	protected function columns()
	{
		return $this->columns = $this->columns?: $this->add('Columns');
	}
	
	protected function permissionId()
	{
		return $this->app->stickyGet($this->columns()->name)?: $this->getModuleVariable('permission');
	}
	
	protected function reload($permissionExpression = null)
	{
		$columns = $this->columns();
		
		return $this->reload = $this->reload?: new jsReload($columns, [$columns->name => $permissionExpression?: '']);
	}
	
	protected function getModel($array)
	{
		return new Model(new Persistence_Static($array));
	}
	
	protected function addGrantRoleAccessButton()
	{
		$modal = $this->add(['Modal', 'title' => __('Grant Role Access')])->set(function($view) {
			$form = $view->add(new Form(['buttonSave' => ['Button', __('Save'), 'primary']]));
			
			$form->addField('role', ['DropDown', 'caption' => __('Grant'), 'values' => Role::all()->pluck('name', 'id')])->set($this->getModuleVariable('permission'));
			
			$form->addField('permission', ['DropDown', 'caption' => __('Access To'), 'values' => Permission::all()->pluck('name', 'id')])->set($this->getModuleVariable('permission'));

			$form->layout->addButton(['Button', __('Cancel')])->on('click', $view->owner->hide());

			$form->onSubmit(function($form) use ($view) {
				$values = $form->getValues();
				
				Role::findById($values['role'])->givePermissionTo($values['permission']);
				
				return [
						$view->owner->hide(),
						$this->reload()
				];
			});
		});
		
		ActionBar::addButton(['icon' => 'group', 'label' => __('Grant Role Access')])->on('click', $modal->show());
	}
	
	protected function addGrantUserAccessButton()
	{
		$modal = $this->add(['Modal', 'title' => __('Grant User Access')])->set(function($view) {
			$form = $view->add(new Form(['buttonSave' => ['Button', __('Save'), 'primary']]));
			
			$form->addField('user', ['DropDown', 'caption' => __('Grant'), 'values' => User::all()->pluck('name', 'id')]);
			
			$form->addField('permission', ['DropDown', 'caption' => __('Access To'), 'values' => Permission::all()->pluck('name', 'id')])->set($this->getModuleVariable('permission'));
			
			$form->layout->addButton(['Button', __('Cancel')])->on('click', $view->owner->hide());
			
			$form->onSubmit(function($form) use ($view) {
				$values = $form->getValues();
				
				User::find($values['user'])->givePermissionTo($values['permission']);
				
				return [
						$view->owner->hide(),
						$this->reload()
				];
			});
		});
		
		ActionBar::addButton(['icon' => 'user', 'label' => __('Grant User Access')])->on('click', $modal->show());
	}
	protected function deleteButton()
	{
		return ['icon' => 'trash', 'class' => ['red'], 'attr' => ['title' => __('Revoke Permission')]];
	}
}
