<?php 

namespace Epesi\Core\System\Modules\Concerns;

use atk4\ui\Form;

trait HasOptions
{
	/**
	 * Define the option elements and return as array
	 * Options are class specific and can be modified by the user
	 */
	public function addOptionControls(Form $form) {}
		
	/**
	 * Define the server side form validation rules
	 */
	public function rules() {
		return [];
	}
	
	public function getOptionDefaults()
	{
		$form = new Form();
		
		$this->addOptionControls($form);
		
		return $form->model->get();
	}
}