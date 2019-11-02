<?php 

namespace Epesi\Core\System\Integration\Modules\Concerns;

trait HasOptions
{
	/**
	 * Define the option elements and return as array
	 * Options are class specific and can be modified by the user
	 */
	public function elements() {}
		
	/**
	 * Define the server side form validation rules
	 */
	public function rules() {
		return [];
	}
	
	public function defaultOptions()
	{
		return collect($this->elements())->pluck('default', 'name');
	}
}