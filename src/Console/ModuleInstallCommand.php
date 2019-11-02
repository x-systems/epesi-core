<?php

namespace Epesi\Core\Console;

use Illuminate\Console\Command;
use Epesi\Core\System\Integration\Modules\ModuleManager;

class ModuleInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epesi:module-install {module : The class name or alias of the module.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the epesi module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
    	try {
    		ob_start();
    		ModuleManager::install($this->argument('module'));
    		
    		$this->alert(ob_get_clean());
    	} catch (\Exception $e) {
    		$this->error($e->getMessage());
    	}
    }
    
}
