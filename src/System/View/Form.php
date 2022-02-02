<?php

namespace Epesi\Core\System\View;

use atk4\ui\Form as BaseForm;
use Epesi\Core\System\Modules\Concerns\Notifies;
use atk4\ui\Label;

class Form extends BaseForm
{
	use Notifies;
	
	public $buttonSave = null;
	
	protected $controlRules = [];
	protected $validationRules = [];
	
	public function addElements($elements, $parent = null) {
		$parent = $parent?: $this;
		
		foreach ($elements as $name => $desc) {
			$name = $desc['name']?? $name;
			
			$this->addControlRules($name, $desc['rules']?? []);
			
			switch ($desc['type']?? 'field') {
				case 'field':
					$desc = is_string($desc)? [
						'decorator' => [$desc]
					]: $desc;
					
					$field = $parent->addControl($name, $desc['decorator']?? [], $desc['options']?? []);
					
					if ($default = $desc['default']) {
						$field->set($default);
					}
					
					if ($desc['display']?? false) {
						$this->addControlDisplayRules([$name => $desc['display']]);
					}
					break;
					
				case 'group':
					$seed = $desc['seed']?? [$name];
					
					$group = $parent->addGroup($seed);
					
					$this->addElements($desc['elements'], $group);
					
					if ($desc['display']?? false) {
						$this->addGroupDisplayRules([$name => $desc['display']]);
					}
					break;
					
				case 'header':
					$seed = $desc['seed']?? [$name];
					
					$parent->addHeader($seed);
					break;
					
				case 'view':
					$seed = $desc['seed']?? [Label::class, $name];
					
					$region = $desc['region']?? null;
					
					$this->add($seed, $region);
					break;
					
				default:
					;
					break;
			}
		}
		
		return $this;
	}
	
	public function addControlDisplayRules($controlDisplayRules) {
		$this->setControlsDisplayRules(array_merge($this->controlDisplayRules?: [], $controlDisplayRules));
	}
	
	public function addGroupDisplayRules($groupDisplayRules) {
		$controlDisplayRules = $this->controlDisplayRules;
		
		$this->setGroupDisplayRules($groupDisplayRules);
		
		$this->addControlDisplayRules($controlDisplayRules);
	}
	
	public function addControlRules($field, $rules = []) {
		if (! $rules) return;
		
		$this->controlRules[$field] = $rules['rules']?? [
				'identifier' => $field,
				'rules' => $rules
		];
		
		return $this;
	}
	
	public function validate($callback)
	{
		$this->setApiConfig([
				'beforeSend' => new \atk4\ui\JsFunction(['settings'], [
						new \atk4\ui\JsExpression('return $(this).form("is valid")')
				]),
		]);
		
		$this->setFormConfig([
				'fields' => $this->controlRules
		]);
		
		$this->onSubmit(function ($form) use ($callback) {
			$errors = [];
			foreach ($this->validationRules?: [] as $ruleCallback) {
				if (! is_callable($ruleCallback)) continue;
				
				$ruleErrors = $ruleCallback($form);
				
				$errors = array_merge($errors, $ruleErrors?: []);
			}
			
			return $errors?: $callback($this);
		});
			
		return $this;
	}
	
	public function submit()
	{
		return $this->js()->form('submit');
	}
	
	public function confirmLeave($confirm = true)
	{
		$this->canLeave = ! $confirm;
		
		return $this;
	}
}