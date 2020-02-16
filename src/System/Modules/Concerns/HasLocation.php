<?php

namespace Epesi\Core\System\Modules\Concerns;

trait HasLocation
{
	use HasModule;
	
	/**
	 * Define the label for the module base location caption
	 * e.g Dashboard >> View, you should define $label = 'Dashboard'
	 * 
	 * @var string
	 */
	protected $label = '';
	
	/**
	 * Stores detailed labels for location caption to be displayed
	 * e.g Dashboard >> Edit >> Layout, you should set $location = ['Edit', 'Layout']
	 * 
	 * @var array
	 */
	protected $location = [];
	
	/**
	 * Get or set the location caption displayed
	 * 
	 * @param string | array $location
	 * @return \Epesi\Core\System\Modules\ModuleView|array
	 */
	public function location($location = null)
	{
		if ($location) {
			$this->location = $location;
			
			return $this;
		}
		
		$this->label = array_map('trans', (array) $this->label);

		return array_merge($this->label, (array) $this->location);
	}
	
	public function label($label)
	{
		if ($label) {
			$this->label = $label;
			
			return $this;
		}
		
		return $this->label;
	}
}
