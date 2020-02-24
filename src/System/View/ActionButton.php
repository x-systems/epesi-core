<?php

namespace Epesi\Core\System\View;

use atk4\ui\View;
use atk4\ui\Icon;
use atk4\ui\jsCallback;

class ActionButton extends View
{
    public $defaultTemplate = 'action_button.html';

    public $ui = 'basic inverted action button';

    /**
     * Icon that will appear on the button (top).
     *
     * @var string|array|Icon
     */
    public $icon;
    
    public $label;

    public $element = 'a';
    
    public $weight = 10;
    
    public $callback;

    public function renderView()
    {
        $this->addIcon();
        
        $this->addLabel();
        
        $this->addCallback();

        parent::renderView();
    }
    
    protected function addIcon()
    {
    	if (! $icon = $this->getIcon()) return;
    	
    	$this->add($icon, 'Icon')->id = null;
    	
    	$this->addClass('icon');
    	
    	return $this;
    }
    
    protected function addLabel()
    {
    	$this->content = $this->label?: $this->content;
    	
    	return $this;
    }

    public function getIcon()
    {
    	return is_object($this->icon)? $this->icon: new Icon($this->icon);
    }
    
    public function callback($callable)
    {
    	$this->callback = $callable;
    	
    	return $this;
    }
    
    public function addCallback()
    {
    	if (!$callable = $this->callback) return;
    	
    	if (is_callable($callable)) {
    		$callable = $this->add('jsCallback')->set($callable);
    	}
    	
    	if ($callable instanceof jsCallback) {
    		$this->on('click', $callable);
    	}
    	
    	return $this;
    }

}
