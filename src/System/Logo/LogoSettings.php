<?php

namespace Epesi\Core\System\Logo;

use Epesi\Core\System\Integration\Modules\ModuleView;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\System\Seeds\Form;
use Epesi\Core\System\Database\Models\Variable;
use Illuminate\Support\Facades\Storage;
use Epesi\Core\Layout\Seeds\ActionBar;
use atk4\ui\View;

class LogoSettings extends ModuleView
{
	protected $label = 'Title & Logo';
	
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

		$form->addField('title', __('Base page title'))->set(Variable::get('system.title'));		
		$logo = $form->addField('logo', [
				'UploadImg', 
				'defaultSrc' => url('logo'), 
				'thumbnail' => (new View(['element'=>'img', 'class' => ['right', 'floated', 'image'], 'ui' => true]))->setStyle('max-width', '150px'),
				'placeholder' => __('Upload new logo')
		]);
		
		$logo->onDelete(function($fileName) {
			$token = md5($fileName);
			
			Storage::delete(self::alias() . '/tmp/' . $token);
		});

		$logo->onUpload(function ($files) use ($form, $logo) {
			if ($files === 'error')	return $form->error('logo', __('Error uploading image'));
			
			$tmpPath = self::alias() . '/tmp/' . $files['name'];
			
			$logo->setThumbnailSrc(asset('storage/' . $tmpPath));
	
			Storage::disk('public')->put($tmpPath, file_get_contents($files['tmp_name']));
		});
					
		$form->onSubmit(function($form) {
			$name = $form->model['logo'];
						
			Storage::disk('public')->move(self::alias() . '/tmp/' . $name, self::alias() . '/' . $name);
						
			Variable::put('system.title', $form->model['title']);
			
			Variable::put('system.logo', $name);
						
			return $form->notify(__('Title and logo updated!'));
		});
			
		ActionBar::addButton('back')->link(url('view/system'));
			
		ActionBar::addButton('save')->on('click', $form->submit());
	}
	
	public static function getLogoPath()
	{
		return self::alias() . '/' . Variable::get('system.logo', 'epesi-logo.png');
	}
	
	public static function getLogoMeta()
	{
		$logoPath = self::getLogoPath();
		
		return array_merge(Storage::getMetadata($logoPath), [
				'mime' => Storage::mimeType($logoPath),
				'contents' => Storage::get($logoPath)
		]);
	}
	
	public static function getTitle()
	{
		return Variable::get('system.title', config('epesi.app.title'));
	}
}
