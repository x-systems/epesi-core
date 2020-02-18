<?php

namespace Epesi\Core\System\User\Settings;

use Epesi\Core\System\Modules\ModuleView;
use Epesi\Core\System\User\Settings\Integration\Joints\UserSettingsJoint;
use Epesi\Core\System\Seeds\Form;
use Epesi\Core\Layout\Seeds\ActionBar;
use Epesi\Core\System\User\Settings\Database\Models\UserSetting;

class SettingsView extends ModuleView
{
	protected $label = 'User Settings';
	
	public function body()
	{
		$layout = $this->add(['View'])->addStyle('max-width:800px;margin:auto;');
		$layout->add(['Header', __('User Settings')]);
		$segment = $layout->add(['View', ['ui' => 'segment']]);
		
		foreach (UserSettingsJoint::collect() as $joint) {
			$segment->add($joint->button());
		}
	}
	
	public function edit($jointClass)
	{
		$joint = new $jointClass();
		
		$this->location($joint->label());
		
		$form = $this->add(new Form());
		$form->addElements($joint->elements());
		$form->confirmLeave();
		$form->model->set(UserSetting::getGroup($joint->group()));

		$form->validate(function(Form $form) use ($joint) {
			UserSetting::putGroup($joint->group(), $form->model->get());
			
			return $form->notify(__('Settings saved!'));
		});
		
		ActionBar::addItemButton('back');
			
		ActionBar::addItemButton('save')->on('click', $form->submit());
	}
}
