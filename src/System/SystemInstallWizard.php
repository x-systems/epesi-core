<?php

namespace Epesi\Core\System;

use Epesi\Core\System\Seeds\Form;
use atk4\ui\jsExpression;
use atk4\ui\Wizard;
use Illuminate\Support\Facades\Artisan;
use Epesi\Core\System\Integration\Modules\Concerns\HasAdminMode;
use Illuminate\Support\Facades\App;
use Epesi\Core\System\Integration\Modules\ModuleManager;

class SystemInstallWizard extends Wizard
{
	use HasAdminMode;
	
	public function init()
	{
		parent::init();

		$this->setAdminMode()->performInstallationSteps();
	}
	
	public function performInstallationSteps()
	{
		$this->addStep(__('Welcome'), [__CLASS__, 'stepWelcome']);
		
		$this->addStep([__('License'), 'icon'=>'id card', 'description'=>__('Accept license conditions')], [__CLASS__, 'stepLicense']);
			
		$this->addStep([__('Database'), 'icon'=>'database', 'description'=>__('Database connection settings')], [__CLASS__, 'stepDatabase']);
		
		$this->addStep([__('Environment'), 'icon'=>'configure', 'description'=>__('Check environment')], [__CLASS__, 'stepEnvironment']);
		
		$this->addFinish([__CLASS__, 'stepInstallationCompleted']);
		
	}
	
	public function addRequiredNote()
	{
		$this->add(['View', __('denotes required field'), 'class' => ['required-note']])->setStyle(['float' => 'right']);
		
		eval_css('
			.required-note::before {
				margin: 0 .2em 0 0;
				content: \'*\';
				color: #db2828;
			}
		');
	}
	
	public function setForm($form)
	{
		$this->buttonNext->on('click', $form->js()->submit());
	}
	
	public static function stepWelcome($wizard)
	{
		$columns = $wizard->add('Columns');
		
		$column = $columns->addColumn();
		$column->add(['Message', __('Thank you for downloading EPESI!')])->text
		->addParagraph(__('This wizard will guide you though the process of setting up your new CRM / ERP installation'))
		->addParagraph(__('Select the installation language and click NEXT button to proceed to next step'));
		
		$column = $columns->addColumn();

		if (! function_exists('locale_get_display_language')) {
			$column->addClass('middle aligned');
			$column->add(['Label', __('Install php-intl extension to enable language selection!'), 'class' => ['red']]);
			
			return;
		}

		$languages = app()->make(\JoeDixon\Translation\Drivers\Translation::class)->allLanguages()->toArray();

		$values = array_map(function($language) {
			return [
					locale_get_display_language($language),
					'icon' => "$language flag"
			];
		}, $languages);
				
		$form = $column->add(new Form());
		
		$form->addField('language', ['DropDown', 'values' => $values, 'caption' => __('Select Language')], ['required'=>true])->set($wizard->recall('language', 'en'));
		
		$form->onSubmit(function ($form) use ($wizard) {
			$wizard->memorize('language', $form->model['language']);
			
			App::setLocale($form->model['language']);
			
			return $wizard->jsNext();
		});

		$wizard->setForm($form);
	}
	
	public static function stepLicense($wizard)
	{
		$columns = $wizard->add('Columns');
		$column = $columns->addColumn();
		
		$column->add(['View', 'defaultTemplate' => 'license.html'])->setStyle(['max-height' => '500px', 'overflow' => 'auto', 'padding' => '5px']);
		
		$column = $columns->addColumn();
		
		$form = $column->add(new Form());
		$form->addField('copyright', ['Checkbox', 'caption' => __('I will not remove the Copyright notice as required by the MIT license.')], ['required'=>true]);
		$form->addField('logo', ['Checkbox', 'caption' => __('I will not remove "EPESI powered" logo and the link from the application login screen or the toolbar.')], ['required'=>true]);
		$form->addField('support', ['Checkbox', 'caption' => __('I will not remove "Support -> About" credit page from the application menu.')], ['required'=>true]);
		$form->addField('store', ['Checkbox', 'caption' => __('I will not remove or rename "EPESI Store" links from the application.')], ['required'=>true]);
		
		$form->onSubmit(function ($form) use ($wizard) {
			return $wizard->jsNext();
		});
			
		$wizard->setForm($form);
	}
	
	public static function stepDatabase($wizard)
	{
		$wizard->addRequiredNote();
		
		$form = $wizard->add('Form');
		
		$form->addField('host', __('Database Host'), ['required'=>true])->placeholder = __('e.g. localhost');
		$form->addField('port', __('Database Port'));
		
		$form->addField('driver', ['DropDown', 'values' => [
				'mysql' => 'MySQL',
				'postgre' => 'PostgeSQL',
		], 'caption' => __('Database Engine')], ['required'=>true]);
		
		$form->addField('database', __('Database Name'));
		$form->addField('username', __('Database Server User'), ['required'=>true]);
		$form->addField('password', ['Password', 'caption' => __('Database Server Password')], ['required'=>true]);
		
		$form->addField('create', ['Checkbox', 'caption' => __('Create New Database')])->on('change', new jsExpression('if ($(event.target).is(":checked")) alert([])', [__('WARNING: Make sure you have CREATE access level to do this!')]));

		foreach ($wizard->recall('connection', []) as $name => $value) {
			if (! $field = $form->fields[$name]?? null) continue;
			
			$field->set($value);
		}

		$form->onSubmit(function ($form) use ($wizard) {
			$connection = $form->model->get();

			$wizard->memorize('connection', $connection);

			Artisan::call('epesi:database-connection', ['--connection' => $connection]);
		
			if ($connection['create']) {
				Artisan::call('epesi:database-create', ['name' => $connection['database'], '--connection' => $connection]);
			}
			
			ModuleManager::clearCache();
			
			Artisan::call('config:clear');
			Artisan::call('cache:clear');

			return $wizard->jsNext();
		});
	}
	
	public static function stepEnvironment($wizard)
	{
		Artisan::call('migrate');
		
		ob_start();
		ModuleManager::install('system');
		ob_end_clean();
		
		$wizard->add(new SystemEnvironmentOverview());
	}
	
	public static function stepInstallationCompleted($wizard)
	{
		$wizard->add(['Header', __(':epesi was successfully installed!', ['epesi' => config('epesi.app.title')]), 'huge centered']);
	}
}
