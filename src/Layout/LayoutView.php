<?php

namespace Epesi\Core\Layout;

use atk4\ui\jQuery;
use Illuminate\Support\Arr;
use Epesi\Core\System\Modules\ModuleView;

/**
 * Implements a classic 100% width admin layout.
 *
 * Optional left menu in inverse with fixed width is most suitable for contextual navigation or
 *  providing core object list (e.g. folders in mail)
 *
 * Another menu on the top for actions that can have a pull-down menus.
 *
 * A top-right spot is for user icon or personal menu, labels or stats.
 *
 * On top of the content there is automated title showing page title but can also work as a bread-crumb or container for buttons.
 *
 * Footer for a short copyright notice and perhaps some debug elements.
 *
 * Spots:
 *  - LeftMenu  (has_menuLeft)
 *  - Menu
 *  - RightMenu (has_menuRight)
 *  - Footer
 *
 *  - Content
 */
class LayoutView extends ModuleView
{
    public $menuLeft;    // vertical menu
    public $menu;        // horizontal menu
    public $menuRight;   // vertical pull-down
    public $actionBar;   
    public $locationBar;
    
    public $burger = true;      // burger menu item

    /*
     * Whether or not left Menu is visible on Page load.
     */
    public $isMenuLeftVisible = true;

    public $defaultTemplate = 'layout/admin.html';
    
    protected $location;

    public function init()
    {
        parent::init();

        $this->setMenu();
        $this->setActionBar();
        
//         if (request()->pjax()) return;
        
        $this->setMenuLeft();        
        $this->setMenuRight();
        
        $this->setVersion();
        
//         $this->js(true, null, 'body')->niceScroll();
        
//         $this->js(true, null, new jsExpression('window'))->on('pageshow', new jsFunction(['event'], [
//         			new jsExpression('
// 		if (event.originalEvent.persisted) {
//         	window.location.reload(); 
//     	}')]));
    }
    
    public function setMenu()
    {
        if ($this->menu) return;
 
        $this->menu = $this->add(['Menu', 'atk-topMenu fixed horizontal', 'element' => 'header'], 'TopMenu');

        // company logo
        // see Epesi\Core\Controllers\SystemController::logo
        $this->menu->add(['class' => ['epesi-logo'], 'style' => ['width' =>  '167px']])->add(['Image', url('logo')])->setStyle(['max-height' => '32px']);
        
        if ($this->burger) {
        	/** @scrutinizer ignore-call */
        	$this->burger = $this->menu->addItem(['class' => ['icon atk-leftMenuTrigger']]);
        }
       	
		// home icon redirects to /home path 
		// see Epesi\Core\Controllers\SystemController::home
        $this->menu->addItem(['class' => ['icon epesi-home']], url('home'))->add(['Icon', 'home']);

		// location bar
		$this->locationBar = $this->menu->add(['View',	['ui' => 'epesi-location']]);
	}

	public function setLocation($crumbs)
	{
		$this->location = $crumbs;

		$crumb = $this->locationBar->add('BreadCrumb');
		
		$title = [];
		foreach ($crumbs as $level) {
			$label = $level['label']?? $level;
			$link = $level['link']?? null;
			
			$crumb->addCrumb($label, $link);
			
			$title[] = $label;
		}

		$crumb->popTitle();
		
		$this->app->title = implode(' > ', Arr::prepend($title, config('epesi.app.title', 'EPESI')));
		
		return $this;
	}
	
	public function getLocation()
	{
		return $this->location;
	}
	
	public function setMenuRight()
	{
		if ($this->menuRight) return;

		$this->menuRight = $this->menu->add(new Seeds\RightMenu([
				'ui' => false
		]), 'RightMenu')->addClass('right menu')->removeClass('item');
	}
	
    public function setMenuLeft()
    {
        if ($this->menuLeft) return;
        
        $this->menuLeft = $this->add(new Seeds\NavMenu('left vertical labeled sidebar'), 'LeftMenu');

        if (! $this->burger) return;

        if (! session()->get('menu', 1)) {
        	$this->isMenuLeftVisible = false;
        }
        
        $this->burger->add(['Icon',	'content'])->on('click', [
        		(new jQuery('.ui.left.sidebar'))->toggleClass('visible'),
        		(new jQuery('.epesi-logo'))->toggleClass('expanded'),
        		(new jQuery('body'))->toggleClass('atk-leftMenu-visible'),
        		$this->burger->add('jsCallback')->set(function($j, $visible) {
        			session()->put('menu', $visible? 1: 0);
        			session()->save();
        		}, [new \atk4\ui\jsExpression( '$("#' . $this->menuLeft->id . '").hasClass("visible")? 1: 0' )])
        ]);
    }
    
    public function setActionBar()
    {
    	if ($this->actionBar) return;
    	
    	$this->actionBar = $this->add(new Seeds\ActionBar(), 'ActionBar');
    }
    
    public function setVersion()
    {
    	$this->template->trySet('version', $this->app->version);
    }

    /**
     * {@inheritdoc}
     */
    
    public function renderView()
    {
    	if ($this->menuLeft && $this->isMenuLeftVisible) {
               $this->menuLeft->addClass('visible');
        }
        parent::renderView();
    }
}
