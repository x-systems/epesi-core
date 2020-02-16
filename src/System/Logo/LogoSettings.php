<?php

namespace Epesi\Core\System\Logo;

use Epesi\Core\System\Modules\ModuleView;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\System\Seeds\Form;
use Epesi\Core\System\Models\Variable;
use Illuminate\Support\Facades\Storage;
use Epesi\Core\Layout\Seeds\ActionBar;
use atk4\ui\View;

class LogoSettings extends ModuleView
{
	protected $label = 'Title & Logo';
	
	protected static $defaultLogo = 'epesi-logo.png';
	
	public static function access()
	{
		return Auth::user()->can('modify system settings');
	}
	
	public function body()
	{
		$layout = $this->add(['View'])->addStyle('max-width:1200px;margin:auto;');
		$layout->add(['Header', __($this->label)]);
		$segment = $layout->add(['View', ['ui' => 'segment']]);

		$form = $segment->add(new Form());

		$form->addField('title', __('Base page title'))->set(Variable::recall('system.title'));
		
		$form->addField('custom_logo', ['CheckBox', 'caption' => __('Use custom logo')])->set((bool) Variable::recall('system.logo'));
		
		$logo = $form->addField('logo', [
				'UploadImg', 
				'defaultSrc' => url('logo'), 
				'thumbnail' => (new View(['element'=>'img', 'class' => ['right', 'floated', 'image'], 'ui' => true]))->setStyle('max-width', '150px'),
				'placeholder' => __('Upload file to replace system logo')
		]);
		
		$form->addFieldsDisplayRules(['logo' => ['custom_logo' => 'checked']]);
		
		$logo->onDelete(function($fileName) use ($logo) {
			$this->storage()->delete(self::alias() . '/tmp/' . $fileName);
			
			$logo->setThumbnailSrc(asset('storage/' . self::alias() . '/' . self::$defaultLogo));
		});

		$logo->onUpload(function ($files) use ($form, $logo) {
			if ($files === 'error')	return $form->error('logo', __('Error uploading image'));
			
			$tmpPath = self::alias() . '/tmp/' . $files['name'];
			
			$logo->setThumbnailSrc(asset('storage/' . $tmpPath));
	
			$this->storage()->put($tmpPath, file_get_contents($files['tmp_name']));
		});
					
		$form->onSubmit(function($form) {
			if ($name = $form->model['custom_logo']? $form->model['logo']: null) {
				$storage = $this->storage();
				$from = self::alias() . '/tmp/' . $name;
				$to = self::alias() . '/' . $name;				
				
				if ($storage->exists($to)) {
					$storage->delete($to);
				}
				
				$storage->move($from, $to);
			}
	
			Variable::put('system.logo', $name);
			
			Variable::put('system.title', $form->model['title']);
			
			return $form->notify(__('Title and logo updated! Refresh page to see changes ...'));
		});
			
		ActionBar::addButton('back')->link(url('view/system'));
			
		ActionBar::addButton('save')->on('click', $form->submit());
	}
	
	public static function getLogoFile()
	{
		return self::storage()->path(self::alias() . '/' . Variable::recall('system.logo', self::$defaultLogo));
	}
	
	public static function getTitle()
	{
		return Variable::recall('system.title', config('epesi.app.title'));
	}

	public static function storage()
	{
		return Storage::disk('public');
	}
	
}
