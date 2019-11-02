<?php

namespace Epesi\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class DatabaseCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epesi:database-create {name : The name of the database.} 
													{--connection=mysql DB connection settings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create database for the epesi application';
    
    protected $connection;

    /**
     * Execute the console command.
     */
    public function handle()
    {
    	DB::connection($this->connection())->statement('CREATE DATABASE `' . $this->argument('name') . '`');
    }
    
    /**
     * Creates the configuration for the connection and returns the key
     * 
     * @return void
     */
    protected function connection()
    {
    	$connection = $this->option('connection');
    	
    	// Just get access to the config.
    	$config = App::make('config');
    	
    	// Will contain the array of connections that appear in our database config file.
    	$connections = $config->get('database.connections');
    	
    	$driver = is_string($connection)? $connection: $connection['driver'];

    	$defaultConnection = $connections[$driver]?? $connections[$config->get('database.default')];
    	
    	$newConnection = array_merge($defaultConnection, is_string($connection)? []: $connection);

    	// Do not select database
    	$newConnection['database'] = '';

    	// This will add our new connection to the run-time configuration for the duration of the request.
    	$config->set('database.connections.create-db', $newConnection);

    	return 'create-db';
    }
}
