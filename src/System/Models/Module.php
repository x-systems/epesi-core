<?php

namespace Epesi\Core\System\Models;

use atk4\data\Model;
use Epesi\Core\Data\HasEpesiConnection;

class Module extends Model
{
    use HasEpesiConnection;
    
    public $table = 'modules';
    
    function init() {
        parent::init();
        
        $this->addFields([
                'class',
                'alias',
                'priority' => ['default' => 0],
                'state' => ['default' => 1]
        ]);
    }
}
