<?php

namespace Epesi\Core\UI\Seeds;

use atk4\ui\View;
use atk4\ui\Icon;

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

    public function renderView()
    {
        $this->addIcon();
        
        $this->addLabel();

        parent::renderView();
    }
    
    protected function addIcon()
    {
    	if (! $icon = $this->getIcon()) return;
    	
    	$this->add($icon, 'Icon')->id = null;
    	
    	$this->addClass('icon');
    }
    
    protected function addLabel()
    {
    	$this->content = $this->label?: $this->content;
    }

    public function getIcon()
    {
    	return is_object($this->icon)? $this->icon: new Icon($this->icon);
    }

}
