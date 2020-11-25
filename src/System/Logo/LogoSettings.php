<?php

namespace Epesi\Core\System\Logo;

use Epesi\Core\System\Modules\ModuleView;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\System\View\Form;
use Epesi\Core\System\Model\Variable;
use Illuminate\Support\Facades\Storage;
use Epesi\Core\Layout\View\ActionBar;
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
		$layout = View::addTo($this)->addStyle('max-width:1200px;margin:auto;');
		\atk4\ui\Header::addTo($layout, [ __($this->label)]);
		$segment = View::addTo($layout, ['ui' => 'segment']);

		$form = Form::addTo($segment);

		$form->addControl('title', __('Base page title'));
		
		$form->addControl('custom_logo', [\atk4\ui\Form\Control\Checkbox::class, 'caption' => __('Use custom logo')]);
		
		$form->model->setMulti([
		        'title' => Variable::recall('system.title'),
		        'custom_logo' => (bool) Variable::recall('system.logo')
		]);
		
		$logo = $form->addControl('logo', [
				\atk4\ui\Form\Control\UploadImage::class, 
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
			if ($logo = $form->model->get('custom_logo') ? $form->model->get('logo') : null) {
				$storage = $this->storage();
				$from = self::alias() . '/tmp/' . $logo;
				$to = self::alias() . '/' . $logo;				
				
				if ($storage->exists($to)) {
					$storage->delete($to);
				}
				
				$storage->move($from, $to);
			}

	        Variable::memorize('system.logo', $logo);
			
			Variable::memorize('system.title', $form->model->get('title'));
			
			return $this->notifySuccess(__('Title and logo updated! Refresh page to see changes ...'));
		});
			
		ActionBar::addItemButton('back')->link(url('view/system'));
			
		ActionBar::addItemButton('save')->on('click', $form->submit());
	}
	
	public static function getLogoFile()
	{
		return self::storage()->path(self::alias() . '/' . Variable::recall('system.logo', self::$defaultLogo));
	}
	
	public static function getTitle()
	{
		return Variable::recall('system.title', config('epesi.ui.title'));
	}

	public static function storage()
	{
		return Storage::disk('public');
	}
	
}
