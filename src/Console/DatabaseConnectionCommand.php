<?php

namespace Epesi\Core\Console;

use Illuminate\Console\Command;

class DatabaseConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epesi:database-connection {--connection= : DB connection settings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update database connection for epesi application';
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
    	$connection = $this->option('connection');
    	
		$map = [
				'driver' => 'DB_CONNECTION',
				'host' => 'DB_HOST',
				'port' => 'DB_PORT',
				'database' => 'DB_DATABASE',
				'username' => 'DB_USERNAME',
				'password' => 'DB_PASSWORD',
		];
		
		$env = [];
		foreach ($map as $key => $name) {
			if (!isset($connection[$key])) continue;
			
			$env[$name] = $connection[$key];
		}
		
		if (!$env) {
			$this->comment('No DB connection settings to update!');
			return;
		}
    	
    	$this->call('epesi:env', [
    			'name' => $env
    	]);
    	
    	$this->comment('DB connection settings updated!');
    }
}
