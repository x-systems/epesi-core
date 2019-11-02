<?php

namespace Epesi\Core\System\Integration\Modules;

use atk4\ui\View;

abstract class ModuleView extends View
{
	use Concerns\HasModule;
	use Concerns\HasLinks;
	use Concerns\HasAssetsAccess;
	use Concerns\HasAccessControl;
	use Concerns\HasLocation;
	use Concerns\HasVariables;
	use Concerns\Notifies;
	
	/**
	 * @var \Epesi\Core\App
	 */
	public $app;
	
	/**
	 * Generates content in the layout using defined module method based on profided arguments / properties
	 * 
	 * @param string $method
	 * @param string $args
	 */
	final public function displayModuleContent($method, $args) {
		// if method not callbale abort to 'not found'
		if (! is_callable([$this, $method])) abort(404);
		
		// if user has no access abort 'no access'
		if (! $this->access()) abort(401);
		
		$args = $this->decodeArgs($args);
		
		// set the associative array keys as view properties
		$this->setDefaults($args);
		
		// filter for entries with numeric keys use values as method arguments
		$args = array_filter($args, function($key) {
			return is_numeric($key);
		}, ARRAY_FILTER_USE_KEY);
		
		ksort($args);
		
		// method can add seeds to the module seed
		// the content echoed in the method is assigned to the module view content region
		ob_start();
		$this->{$method}(...$args);
		$content = ob_get_clean();
		
		$this->set('Content', $content);
	}

}
