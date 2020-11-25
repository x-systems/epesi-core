<?php

namespace Epesi\Core\System;

use Epesi\Core\System\Modules\ModuleView;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\System\Modules\ModuleManager;
use Epesi\Core\Layout\View\ActionBar;
use Epesi\Core\System\View\Form;

class ModuleAdministration extends ModuleView
{
	protected $label = 'Module Administration';
	
	protected $accordion;
	
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
		$modules = collect(ModuleManager::getAll());

		return $modules->filter(function ($subModuleClass) use ($modules) {
			return ! $modules->map(function($moduleClass){
				return $moduleClass::namespace();
			})->contains($subModuleClass::parentNamespace());
		})->sort();
	}
	
	public function addAccordion($container, $modules)
	{
		$accordion = \atk4\ui\Accordion::addTo($container, ['type' => ['styled', 'fluid'], 'settings' => ['animateChildren' => false]])->setStyle(['max-width' => '800px', 'margin-left' => 'auto', 'margin-right' => 'auto']);
		
		if ($container == $this) {
		    $this->accordion = $accordion;
		}
		
		foreach ($modules as $moduleClass) {
			$section = $accordion->addSection($moduleClass::label());

			\atk4\ui\Message::addTo($section, ['ui' => 'tiny message'])->template->appendHTML('Content', $this->formatModuleInfo($moduleClass));

			if (ModuleManager::isInstalled($moduleClass)) {
				$label = [\atk4\ui\Label::class, __('Installed'), 'green'];
				
				$this->addUninstallButton($section, $moduleClass);
				
// 				$this->addReinstallButton($section, $moduleClass);
			}
			else {
				$label = [\atk4\ui\Label::class, __('Available'), 'yellow'];
				
				$this->addInstallButton($section, $moduleClass);
			}

			$section->add($label, 'title')->setStyle('float', 'right');
			
			$submodules = collect(ModuleManager::getAll())->filter(function ($subModuleClass) use ($moduleClass) {
				return $subModuleClass::isSubModuleOf($moduleClass);
			});
			
			if ($submodules->isEmpty()) continue;
			
			$this->addAccordion($section, $submodules);
		}
		
		return $accordion;
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
		$button = \atk4\ui\Button::addTo($container, [__('Install'), 'class' => ['green']]);
	    
		$callback = $installCallback = \atk4\ui\JsCallback::addTo($this)->set(function() use ($moduleClass, $container) {
			ob_start();
			ModuleManager::install($moduleClass);
			
			$message = ob_get_clean();
			
			return [
			        $this->notifySuccess($message),
			        new \atk4\ui\JsReload($this->accordion),
			];
		});
		
		$dependencies = ModuleManager::listDependencies($moduleClass);		
		$recommended = ModuleManager::listRecommended($moduleClass);
		
		if ($dependencies || $recommended) {
			$modal = \atk4\ui\Modal::addTo($this, ['title' => __(':module Module Installation', ['module' => $moduleClass::label()])])->set(function($view) use ($installCallback, $moduleClass, $dependencies, $recommended) {
				if ($dependencies) {
					$message = \atk4\ui\Message::addTo($view, [__('Module has following dependencies which will be installed')]);
					
					foreach ($dependencies as $parentModule) {
						$message->text->addParagraph($parentModule::label());
					}
				}
				
				if ($recommended) {
					$message = \atk4\ui\Message::addTo($view, [__('Select to install recommended modules for best experience')]);
					
					$form = Form::addTo($view);
					foreach ($recommended as $childModule) {
						if (! ModuleManager::isAvailable($childModule)) continue;
						
						$form->addControl($childModule::alias(), ['CheckBox', 'caption' => $childModule::label()]);
					}
					
					$form->onSubmit(function ($form) use ($moduleClass) {
						ob_start();
						ModuleManager::install($moduleClass, array_keys($form->getValues(), true));
						
						return ob_get_clean();
					});
				}
				
				\atk4\ui\Button::addTo($view, [__('Install'), 'primary'])->on('click', [
						isset($form)? $form->submit(): $installCallback
				]);
			});
			
			$callback = $modal->show();
		}		
		
		$button->on('click', $callback);
	}
	
	public function addUninstallButton($container, $moduleClass)
	{
		$button = \atk4\ui\Button::addTo($container, [__('Uninstall'), 'class' => ['red']]);
	    
		$callback = $uninstallCallback = \atk4\ui\JsCallback::addTo($button, ['confirm' => __('Are you sure you want to uninstall :module', ['module' => $moduleClass::label()])])->set(function() use ($moduleClass) {
		    ob_start();
			ModuleManager::uninstall($moduleClass);
			
			$message = ob_get_clean();
			
			return [
			        $this->notifySuccess($message),
			        new \atk4\ui\JsReload($this->accordion),
			];
		});
		
		if ($dependents = ModuleManager::listDependents()[$moduleClass]?? []) {
			$modal = \atk4\ui\Modal::addTo($this, ['title' => __(':module Module Installation', ['module' => $moduleClass::label()])])->set(function($view) use ($moduleClass, $dependents, $uninstallCallback) {
				$message = \atk4\ui\Message::addTo($view, [__('Module is required by following modules')]);
					
				foreach ($dependents as $childModule) {
					$message->text->addParagraph($childModule::label());
				}
				
				\atk4\ui\Button::addTo($view, [__('Install'), 'primary'])->on('click', $uninstallCallback);
			});
			
			$callback = $modal->show();
		}
			
		$button->on('click', $callback);
	}
	
	public function addReinstallButton($container, $moduleClass)
	{
		\atk4\ui\Button::addTo($container, [__('Re-install')]);
	}
	
	public function addControlButtons()
	{
		ActionBar::addItemButton('back')->link(url('view/system'));
		
		$this->addClearCacheButton();
	}
	
	public function addClearCacheButton()
	{
	    ActionBar::addItemButton([__('Clear Cache'), 'icon' => 'refresh', 'hint' => __('Clears module cache and re-discovers all available modules')])->callback(function($callback) {
			ModuleManager::clearCache();
			
			return [
					new \atk4\ui\JsReload($this->accordion),
			        $this->notifySuccess(__('Cache cleared!'))
			];
		});
	}
	
}
