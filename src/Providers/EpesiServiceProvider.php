<?php

namespace Epesi\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Epesi\Core\UI;
use Epesi\Core\System\Modules\ModuleManager;
use Epesi\Core\Middleware\NoCacheHeaders;
use Epesi\Core\Data\Persistence\SQL;

class EpesiServiceProvider extends ServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot()
    {
    	$this->ensureHttps();
    	
    	if (env('APP_DEBUG', false)) ModuleManager::clearCache();
    	
    	Route::group(['namespace' => 'Epesi\Core', 'middleware' => 'web'], function() {
    		Route::any('/', 'Controller@index');
    		Route::get('logo', 'Controller@logo');
    		Route::any('install', 'Controller@install');
    		
    		Route::group(['middleware' => ['auth', NoCacheHeaders::class]], function() {
    			Route::any('home', 'Controller@home')->name('home');
    			
    			Route::any('view/{alias}/{method?}/{args?}', 'Controller@view');
    		});
    	});

    	// call boot methods on all modules
    	ModuleManager::call('boot');
    		
		foreach (ModuleManager::collect('translations') as $path) {
			$this->loadJsonTranslationsFrom($path);
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
    	$this->app->singleton(UI::class);
    	
    	$this->app->singleton(
    			SQL::class,
    			function ($app) {
    				/**
    				 * Database Manager
    				 *
    				 * @var \Illuminate\Database\DatabaseManager $db
    				 */
    				$db = DB::getFacadeRoot();
    				
    				return new SQL($db);
    	});
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
}
