<?php

namespace Epesi\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Epesi\Core\System\Database\Models\Module;

class AdminServiceProvider extends ServiceProvider
{
	protected $commands = [
			\Epesi\Core\Console\AdminCommand::class
	];
	
    /**
     * Booting the package.
     */
    public function boot()
    {
    	// Register migrations from installed modules
    	$this->loadMigrationsFrom(Module::collect('migrations'));

    	// Publish files from installed modules
    	$this->publishes(Module::collect('public'), 'epesi.module.public');
    	
    	// Publish epesi configuration files
    	$this->publishes([__DIR__.'/../../config' => config_path()], 'epesi.config');
    }

    /**
     * Register the provider.
     */
    public function register()
    {
    	$this->commands($this->commands);
    }
}
