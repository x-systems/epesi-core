<?php

namespace Epesi\Core\System\Seeds;

use atk4\ui\Form as BaseForm;
use atk4\ui\jsExpression;
use atk4\ui\jsFunction;
use Epesi\Core\System\Modules\Concerns\Notifies;

class Form extends BaseForm
{
	use Notifies;
	
	public $buttonSave = null;
	
	protected $fieldRules = [];
	protected $validationRules = [];
	
	public function addElements($elements, $parent = null) {
		$parent = $parent?: $this;
		
		foreach ($elements as $name => $desc) {
			$name = $desc['name']?? $name;
			
			$this->addFieldRules($name, $desc['rules']?? []);
			
			switch ($desc['type']?? 'field') {
				case 'field':
					$desc = is_string($desc)? [
					'decorator' => [$desc]
					]: $desc;
					
					$field = $parent->addField($name, $desc['decorator']?? [], $desc['options']?? []);
					
					if ($default = $desc['default']) {
						$field->set($default);
					}
					
					if ($desc['display']?? false) {
						$this->addFieldsDisplayRules([$name => $desc['display']]);
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
					$seed = $desc['seed']?? ['Label', $name];
					
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
	
	public function addFieldsDisplayRules($fieldsDisplayRules) {
		$this->setFieldsDisplayRules(array_merge($this->fieldsDisplayRules?: [], $fieldsDisplayRules));
	}
	
	public function addGroupDisplayRules($groupDisplayRules) {
		$fieldsDisplayRules = $this->fieldsDisplayRules;
		
		$this->setGroupDisplayRules($groupDisplayRules);
		
		$this->addFieldsDisplayRules($fieldsDisplayRules);
	}
	
	public function addFieldRules($field, $rules = []) {
		if (! $rules) return;
		
		$this->fieldRules[$field] = $rules['rules']?? [
				'identifier' => $field,
				'rules' => $rules
		];
		
		return $this;
	}
	
	public function validate($callback)
	{
		$this->setApiConfig([
				'beforeSend' => new jsFunction(['settings'], [new jsExpression('return $(this).form("is valid")')]),
		]);
		
		$this->setFormConfig([
				'fields' => $this->fieldRules
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