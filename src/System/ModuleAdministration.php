<?php

namespace Epesi\Core\System;

use Epesi\Core\System\Integration\Modules\ModuleView;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\System\Integration\Modules\ModuleManager;
use Epesi\Core\Layout\Seeds\ActionBar;
use atk4\ui\jsFunction;
use atk4\ui\jsExpression;

class ModuleAdministration extends ModuleView
{
	protected $label = 'Module Administration';
	
	public static function access()
	{
		return Auth::user()->can('modify system settings');
	}
	
	public function body()
	{
		$this->addControlButtons();

		$this->addAccordion($this, $this->topLevelModules());
	}
	
	public function topLevelModules()
	{
		$modules = ModuleManager::getAll();
		
		return $modules->filter(function ($subModuleClass) use ($modules) {
			return ! $modules->map(function($moduleClass){
				return $moduleClass::namespace();
			})->contains($subModuleClass::parentNamespace());
		})->sort();
	}
	
	public function addAccordion($container, $modules)
	{
		$accordion = $container->add(['Accordion', 'type' => ['styled', 'fluid'], 'settings' => ['animateChildren' => false]])->setStyle(['max-width' => '800px', 'margin-left' => 'auto', 'margin-right' => 'auto']);
		
		foreach ($modules as $moduleClass) {
			$section = $accordion->addSection($moduleClass::label());
			
			
			$section->add(['Message', 'ui' => 'tiny message'])->template->appendHTML('Content', $this->formatModuleInfo($moduleClass));

			if (ModuleManager::isInstalled($moduleClass)) {
				$label = ['Label', __('Installed'), 'green'];
				
				$this->addUninstallButton($section, $moduleClass);
				
				$this->addReinstallButton($section, $moduleClass);
			}
			else {
				$label = ['Label', __('Available'), 'yellow'];
				
				$this->addInstallButton($section, $moduleClass);
			}
			
			$section->add($label, 'title')->setStyle('float', 'right');
			
			$submodules = ModuleManager::getAll()->filter(function ($subModuleClass) use ($moduleClass) {
				return $subModuleClass::isSubModuleOf($moduleClass);
			});
			
			if ($submodules->isEmpty()) continue;
			
			$this->addAccordion($section, $submodules);
		}
	}
	
	public function formatModuleInfo($moduleClass)
	{
		$moduleInfo = (array) ($moduleClass::info()?: __(' No details provided by author'));
		
		$ret = [];
		foreach ($moduleInfo as $label => $text) {
			$ret[] = (is_string($label)? "<strong>$label</strong>: ": '') . $text;
		}
		
		return implode('<br>', $ret);
	}
	
	public function addInstallButton($container, $moduleClass)
	{
		$installCallback = $this->add('jsCallback')->set(function() use ($moduleClass) {
			ob_start();
			ModuleManager::install($moduleClass);
			
			return ob_get_clean();
		});
			
		$modal = $this->add(['Modal', 'title' => __('Module Installation')])->set(function($view) use ($installCallback) {
			$view->add('Header')->set('Module has following dependencies which will be installed');
				
			$view->add(['Button', __('Install'), 'primary'])->on('click', [
					$installCallback
			]);
		});
				
		$container->add(['Button', __('Install'), 'class' => ['green']])->on('click', $modal->show());
	}
	
	public function addUninstallButton($container, $moduleClass)
	{
		$container->add(['Button', __('Uninstall'), 'class' => ['red']]);
	}
	
	public function addReinstallButton($container, $moduleClass)
	{
		$container->add(['Button', __('Re-install')]);
	}
	
	public function addControlButtons()
	{
		ActionBar::addButton('back')->link(url('view/system'));
		
		$this->addClearCacheButton();
	}
	
	public function addClearCacheButton()
	{
		ActionBar::addButton(['icon' => 'refresh', 'label' => __('Clear Cache')])->callback(function($callback) {
			ModuleManager::clearCache();
			
			return $this->notify(__('Cache cleared!'));
		});
	}
	
}
