<?php

namespace Epesi\Core\Tests;

use Orchestra\Testbench\TestCase;
use Epesi\Core\System\Models\Variable;

class VariablesTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Variable::migrate();
    }
    
    public function testVariableStorage()
    {
        $varString = 'test';
        
        Variable::memorize('testString', $varString);
        
        $this->assertEquals($varString, Variable::recall('testString'), 'String variable not stored correctly in database');
        
        Variable::forget('testString');
        
        $this->assertEmpty(Variable::recall('testString'), 'String variable not cleared correctly from database');
        
        $varArray = [
                'aa' => 'test1',
                'bb' => 'test2'
        ];
        
        Variable::memorize('testArray', $varArray);
        
        $this->assertEquals($varArray, Variable::recall('testArray'), 'Array variable not stored correctly in database');
        
        Variable::forget('testArray');
        
        $this->assertEmpty(Variable::recall('testArray'), 'String variable not cleared correctly from database');
    }
    

}