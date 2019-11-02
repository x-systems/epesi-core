<?php

namespace Epesi\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Epesi\Core\Console\DatabaseCreateCommand;
use Epesi\Core\Console\EpesiCommand;
use Epesi\Core\Console\SetEnvCommand;
use Epesi\Core\Console\DatabaseConnectionCommand;
use Epesi\Core\System\Integration\Modules\ModuleManager;
use Epesi\Core\Console\ModuleInstallCommand;

class AdminServiceProvider extends ServiceProvider
{
	protected $commands = [
			EpesiCommand::class,
			DatabaseCreateCommand::class,
			DatabaseConnectionCommand::class,
			SetEnvCommand::class,
			ModuleInstallCommand::class
	];
	
    /**
     * Booting the package.
     */
    public function boot()
    {
    	$this->app->register(\JoeDixon\Translation\TranslationServiceProvider::class);
    	
    	// Register migrations from installed modules
    	$this->loadMigrationsFrom(ModuleManager::collect('migrations'));

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
