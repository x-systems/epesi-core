<?php

namespace Epesi\Core\System\Model;

use atk4\data\Model;
use Epesi\Core\Data\HasEpesiConnection;

class Module extends Model
{
    use HasEpesiConnection;
    
    public $table = 'modules';
    
    protected function init(): void
    {
        parent::init();
        
        $this->addFields([
                'class',
                'alias',
                'priority' => ['default' => 0],
                'state' => ['default' => 1]
        ]);
    }
}
