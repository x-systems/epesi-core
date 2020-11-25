<?php 

namespace Epesi\Core\Layout\View;

use Illuminate\Support\Collection;
use atk4\ui\Menu as BaseMenu;
use Epesi\Core\Layout\Integration\Joints\NavMenuJoint;
use atk4\ui\jQuery;

class NavMenu extends BaseMenu
{
    public $ui = 'inverted nav menu';
    
//     public $defaultTemplate = 'layout/maestro-sidenav.html';
    
    protected function init(): void
	{		
		parent::init();
		
		$this->addHeader($this->getApp()->title);
		
		$items = collect();
		foreach(NavMenuJoint::collect() as $joint) {
			$items = $items->merge($joint->items());
		}

		$this->addItems($this, $items);
		
// 		$this->js(true)->find('.toggle-group .header')->click(new jsFunction(['e'], [new jsExpression('$(e.target).next(".menu").slideToggle()')]))->click();
		
// 		$this->getApp()->addStyle('
// 			.toggle-group .header {
// 				cursor: pointer;
// 			}
// 		');
	}

	public static function addItems($menu, Collection $items)
	{
		$items->sort(function ($entry1, $entry2) {
			$weight1 = $entry1['weight']?? 10;
			$weight2 = $entry2['weight']?? 10;
			
			return $weight1 <=> $weight2;
		})->map(function($entry, $caption) use ($menu) {
			if (! ($entry['access'] ?? true)) return;

			if (!is_array($entry)) {
				$entry = ['action' => $entry];
			}
			
			$entry['item'] = $entry['item'] ?? $caption;
			
			if (is_array($entry['item'])) {
				$entry['item'] = [$caption] + $entry['item'];
			}
			
			if ($subitems = $entry['menu'] ?? []) {		
				$submenu = $menu->addMenu($entry['item']);
				
// 				$submenu->addClass('right pointing');
// 				$submenu->js = ['transition' => 'swing left', 'on' => 'click'];

				self::addItems($submenu, collect($subitems));
			}
			elseif ($subitems = $entry['group'] ?? []) {
			    $subgroup = $menu->addGroup($entry['item']);

				self::addItems($subgroup, collect($subitems));
			}
			else {
			    $menu->addItem($entry['item'], $entry['action'] ?? '');
			}
		});
	}
	
// 	public function addGroup($name, string $template = 'menugroup.html')
// 	{
// 	    return parent::addGroup($name, $template)->addClass('atk-maestro-sidenav')->removeClass('item');
// 	}
	
// 	public function addItem($item = null, $action = null)
// 	{
// 	    return parent::addItem($item, $action)->addClass('atk-maestro-sidenav');
// 	}
	
// 	public function renderView(): void
// 	{
// 	    parent::renderView();
	    
// 	    //initialize all menu group at ounce.
// 	    //since atkSideNav plugin default setting are for Maestro, no need to pass settings to initialize it.
// 	    $js = (new jQuery('.atk-maestro-sidenav'))->atkSidenav();
	    
// 	    $this->js(true, $js);
// 	}
}