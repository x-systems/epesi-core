<?php

namespace Epesi\Core\Tests;

use Orchestra\Testbench\TestCase;
use Epesi\Core\System\Modules\ModuleManager;
// use Epesi\Core\Layout\LayoutCore;

class ModuleManagerTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        config([
                'epesi.modules' => [
                        '\\Epesi\\Core\\' => __DIR__ . implode(DIRECTORY_SEPARATOR, ['..', '..', 'src']),
                ]
        ]);
        
        ModuleManager::install('system');
    }
    
    public function testContentStorage()
    {
        $installedModules = ModuleManager::getInstalled();
        
        $this->assertArrayHasKey('system', $installedModules, 'System module not installed');
    }
    

}