<?php

namespace Epesi\Core;

use Illuminate\Routing\Controller as BaseController;
use Epesi\Core\System\SystemCore;
use Epesi\Core\System\Modules\ModuleManager;
use Epesi\Core\Layout\LayoutView;
use Illuminate\Support\Facades\File;
use atk4\ui\Layout;

class Controller extends BaseController
{
    public function index()
    {
    	return SystemCore::isInstalled()? redirect('home'): redirect('install');
    }
    
    public function install(UI $ui)
    {
    	// make sure the installation information is fresh
    	ModuleManager::clearCache();
    	
    	if (SystemCore::isInstalled()) return redirect('home');
    	
    	$ui->title = __(':epesi > Installation', ['epesi' => config('epesi.ui.title')]);
    	
    	$ui->initLayout(new Layout\Centered());
    	
    	$ui->layout->set('logo', url('logo'));
    	$ui->layout->template->dangerouslySetHTML('copyright', config('epesi.ui.copyright'));
    	
    	$ui->add(new System\SystemInstallWizard());
    	
    	return $ui->response();
    }
    
    public function home()
    {
    	return redirect(SystemCore::isInstalled()? HomePage\Model\HomePage::pathOfUser(): 'install');
    }
    
    public function logo()
    { 
    	$logoFile = \Epesi\Core\System\Logo\LogoSettings::getLogoFile();

    	return response(File::get($logoFile), 200, ['Content-type' => File::mimeType($logoFile)])->setMaxAge(604800)->setPublic();
    }
    
    public function view(UI $ui, $module, $method = 'body', $args = [])
    {
    	$ui->initLayout(new LayoutView());
    	
    	$alias = explode(':', $module);
    	
    	$moduleAlias = $alias[0];
    	$viewAlias = $alias[1]?? null;
    	
    	$view = null;
    	if ($module = ModuleManager::getClass($moduleAlias, true)) {
    		$viewClass = $module::view($viewAlias);

    		if (class_exists($viewClass)) {
    			$view = [$viewClass];
    		}
    	}

    	if (! $view) abort(404);
    	
    	$location = $ui->add($view)->displayModuleContent($method, $args)->location();
    	
    	return $ui->setLocation($location)->response();
    }
}
