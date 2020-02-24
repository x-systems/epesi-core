<?php

namespace Epesi\Core\Layout\View;

use atk4\ui\Item;
use atk4\ui\jsCallback;

class ActionBarItem extends Item
{
    public $weight = 10;
    
    public $callback;
    
    public $hint;
    
    public function renderView()
    {
        $this->addCallback();
        
        if ($this->hint) {
            $this->attr['title'] = $this->hint;
        }
        
        parent::renderView();
    }
    
    public function callback($callable)
    {
        $this->callback = $callable;
        
        return $this;
    }
    
    public function addCallback()
    {
        if (is_callable($callable = $this->callback)) {
            $callable = $this->add('jsCallback')->set($callable);
        }
        
        if ($callable instanceof jsCallback) {
            $this->on('click', $callable);
        }
        
        return $this;
    }
}
