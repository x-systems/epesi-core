<?php

namespace Epesi\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\App;
use Epesi\Core\Database\Models\Module;

class EpesiServiceProvider extends ServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot()
    {
    	$this->ensureHttps();
    	
    	Route::group(['namespace' => 'Epesi\Core\Controllers', 'middleware' => ['web', 'auth']], function() {
    		header("Cache-Control: no-cache, no-store, must-revalidate"); //HTTP 1.1
    		header("Pragma: no-cache"); //HTTP 1.0
    		header("Expires: 0");
    		
    		Route::any('view/{alias}/{method?}/{args?}', 'ModuleController@view');
    	});
    	
		// Register providers declared in modules
		foreach (Module::collect('providers') as $provider) {
			$this->app->register($provider);
		}

    	// Register admin service provider if in admin mode or in console
    	// TODO: apply access restriction to admin mode
//     	if ($this->app->runningInConsole() || (request('admin', false) && Auth::user()->can('modify system'))) {
    	if ($this->app->runningInConsole() || request('admin', false)) {
    		$this->app->register(AdminServiceProvider::class);
    	}
    }

    /**
     * Register the provider.
     */
    public function register()
    {
    	$this->app->singleton(App::class);
    }
    
    /**
     * Force to set https scheme if https enabled.
     *
     * @return void
     */
    protected function ensureHttps()
    {
    	if (config('epesi.https') || config('epesi.secure')) {
    		url()->forceScheme('https');
    		$this->app['request']->server->set('HTTPS', true);
    	}
    }
    
    protected function registerStorageDisk()
    {
    	$this->app['config']['filesystems.disks.epesi'] = config('epesi.disk');
    }
}
