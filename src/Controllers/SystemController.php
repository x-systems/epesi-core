<?php

namespace Epesi\Core\Controllers;

use Illuminate\Routing\Controller;
use Epesi\Core\System\SystemCore;
use Epesi\Core\App as Epesi;
use Epesi\Core\System\Integration\Modules\ModuleManager;
use Epesi\Core\Layout\LayoutView;
use Illuminate\Support\Facades\File;

class SystemController extends Controller
{
    public function index()
    {
    	return SystemCore::isInstalled()? redirect('home'): redirect('install');
    }
    
    public function install(Epesi $epesi)
    {
    	// make sure the installation information is fresh
    	ModuleManager::clearCache();
    	
    	if (SystemCore::isInstalled()) return redirect('home');
    	
    	$epesi->title = __(':epesi > Installation', ['epesi' => config('epesi.app.title')]);
    	
    	$epesi->initLayout('Centered');
    	
    	$epesi->layout->set('logo', url('logo'));
    	$epesi->layout->template->setHTML('copyright', config('epesi.app.copyright'));
    	
    	$epesi->add(new \Epesi\Core\System\SystemInstallWizard());
    	
    	return $epesi->response();
    }
    
    public function home()
    {
    	return redirect(SystemCore::isInstalled()? \Epesi\Core\HomePage\HomePageSettings::getUserHomePagePath(): 'install');
    }
    
    public function logo()
    { 
    	$logoFile = \Epesi\Core\System\Logo\LogoSettings::getLogoFile();

    	return response(File::get($logoFile), 200, ['Content-type' => File::mimeType($logoFile)])->setMaxAge(604800)->setPublic();
    }
    
    public function view(Epesi $epesi, $module, $method = 'body', $args = [])
    {
    	$epesi->initLayout(new LayoutView());
    	
    	$alias = explode(':', $module);
    	
    	$moduleAlias = $alias[0];
    	$viewAlias = $alias[1]?? null;
    	
    	$view = null;
    	if ($module = ModuleManager::getClass($moduleAlias, true)) {
    		$viewClass = $module::view($viewAlias);

    		if (class_exists($viewClass)) {
    			$view = new $viewClass();
    		}
    	}

    	if (! $view) abort(404);
    	
    	$epesi->add($view)->displayModuleContent($method, $args);
    	
    	$epesi->setLocation($view->location());
    	
    	return $epesi->response();
    }
}
