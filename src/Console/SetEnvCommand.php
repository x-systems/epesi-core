<?php

namespace Epesi\Core\Console;

use Illuminate\Console\Command;

class SetEnvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epesi:env {name : Env variable(s) to set} {value? : Env variable value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update ENV variable';

    /**
     * Execute the console command.
     */
    public function handle()
    {
    	$this->setEnv($this->argument('name'), $this->argument('value'));
    }
    
    private static function setEnv($name, $value = null)
    {
    	$env = $name;
    	if (is_string($name) && !isset($value)) {
    		$env = [
    				$name => $value
    		];
    	}
    	
    	foreach ($env as $key => $value) {
    		$oldSetting = $key . '=' . env($key);
    		$newSetting = $key . '=' . $value;
    		
    		putenv($newSetting);
    		
    		file_put_contents(app()->environmentFilePath(), str_replace(
    			$oldSetting,
    			$newSetting,
    			file_get_contents(app()->environmentFilePath())
    		));
    	}
    }
}
