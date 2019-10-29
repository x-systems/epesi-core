<?php

namespace Epesi\Core\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Epesi\Core\App as Epesi;
use Epesi\Core\System\Database\Models\Module;

class ModuleController extends Controller
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	
	public function view(Epesi $epesi, $module, $method = 'body', $args = [])
	{
		$alias = explode('_', $module);
		
		$moduleAlias = $alias[0];
		$viewAlias = $alias[1]?? null;
		
		$view = null;		
		if ($module = Module::getClass($moduleAlias)) {
			$viewClass = $module::view($viewAlias);
			
			if (class_exists($viewClass)) {
				$view = new $viewClass();
			}
		}

		if (empty ($view)) abort(404);

		$epesi->add($view)->displayModuleContent($method, $args);
		
		$epesi->setLocation($view->location());

		return $epesi->response();
	}
}
