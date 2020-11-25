<?php

namespace Epesi\Core\Layout;

use atk4\ui\jQuery;
use Illuminate\Support\Arr;
use Epesi\Core\System\Modules\ModuleView;
use atk4\core\SessionTrait;
use atk4\ui\Menu;

class LayoutView extends \atk4\ui\Layout
{
    use SessionTrait;
    
    /**
     * @var View\NavMenu
     */
    public $menuLeft;    // vertical menu
       
    /**
     * @var \atk4\ui\Menu
     */
    public $menuTop;        // horizontal menu
    
    /**
     * @var \atk4\ui\Menu
     */
    public $menuRight;   // vertical pull-down
       
    /**
     * @var View\ActionBar
     */
    public $actionBar;   
    public $locationBar;
    
    public $burger = true;      // burger menu item

    /*
     * Whether or not left Menu is visible on Page load.
     */
    public $isMenuLeftVisible = true;

    public $defaultTemplate = 'layout/admin.html';
    
    protected $location;

    protected function init(): void
    {
        parent::init();
        
        $this->initMenuTop();
        $this->initActionBar();
        
//         if (request()->pjax()) return;
        
        $this->initMenuLeft();  
        $this->initMenuRight();
        
        $this->template->trySet('version', $this->getApp()->version);
        
//         $this->js(true, null, 'body')->niceScroll();
        
//         $this->js(true, null, new jsExpression('window'))->on('pageshow', new jsFunction(['event'], [
//         			new jsExpression('
// 		if (event.originalEvent.persisted) {
//         	window.location.reload(); 
//     	}')]));
    }
    
	/**
	 * @return static 
	 */
	public function setLocation(array $crumbs)
	{
		$this->location = $crumbs;

		$crumb = \atk4\ui\Breadcrumb::addTo($this->locationBar);
		
		$title = [];
		foreach ($crumbs as $level) {
			$label = $level['label'] ?? $level;
			$link = $level['link'] ?? null;
			
			$crumb->addCrumb($label, $link);
			
			$title[] = $label;
		}

		$crumb->popTitle();
		
		$this->getApp()->title = implode(' > ', Arr::prepend($title, config('epesi.ui.title', 'EPESI')));
		
		return $this;
	}
	
	public function getLocation()
	{
		return $this->location;
	}
	
	protected function initMenuTop()
	{
		if ($this->menuTop) return;
		
		$this->menuTop = Menu::addTo($this, ['atk-topMenu fixed horizontal', 'element' => 'header'], ['TopMenu']);
		
		// company logo
		// see \Epesi\Core\Controller::logo
		\atk4\ui\View::addTo($this->menuTop, ['class' => ['epesi-logo'], 'style' => ['width' =>  '167px']])->add([\atk4\ui\Image::class, url('logo')])->setStyle(['max-height' => '32px']);
		
		if ($this->burger) {
			$this->burger = $this->menuTop->addItem(['class' => ['icon atk-leftMenuTrigger']]);
		}
		
		// home icon redirects to /home path
		// see \Epesi\Core\Controller::home
		$this->menuTop->addItem(['class' => ['icon epesi-home']], url('home'))->add([\atk4\ui\Icon::class, 'home']);
		
		// location bar
		$this->locationBar = $this->menuTop->add([\atk4\ui\View::class, ['ui' => 'epesi-location']]);
	}

	protected function initMenuRight()
	{
		if ($this->menuRight) return;

		$this->menuRight = $this->menuTop->add(new View\RightMenu(['ui' => false]), 'RightMenu')->addClass('right menu')->removeClass('item');
	}
	
    protected function initMenuLeft()
    {
        if ($this->menuLeft) return;
        
        $this->menuLeft = View\NavMenu::addTo($this, 'left vertical labeled sidebar', ['LeftMenu']);

        if ($this->burger) {
        	$this->isMenuLeftVisible = $this->learn('menu', $this->isMenuLeftVisible);
        	
        	$this->burger->add([\atk4\ui\Icon::class, 'content'])->on('click', [
        			(new jQuery('.ui.left.sidebar'))->toggleClass('visible'),
        			(new jQuery('.epesi-logo'))->toggleClass('expanded'),
        			(new jQuery('body'))->toggleClass('atk-leftMenu-visible'),
        			\atk4\ui\JsCallback::addTo($this->burger)->set(function($jsCallback, $visible) {
        				$this->memorize('menu', filter_var($visible, FILTER_VALIDATE_BOOLEAN));
        			}, [$this->menuLeft->js(true)->hasClass('visible')])
        	]);
        }
    }
    
    protected function initActionBar()
    {
    	if (!$this->actionBar) {
    		$this->actionBar = View\ActionBar::addTo($this, [], ['ActionBar']);
    	}
    	
    	return $this;
    }

    public function renderView(): void
    {
    	if ($this->menuLeft && $this->isMenuLeftVisible) {
               $this->menuLeft->addClass('visible');
        }
        parent::renderView();
    }
}
