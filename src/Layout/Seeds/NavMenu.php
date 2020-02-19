<?php 

namespace Epesi\Core\Layout\Seeds;

use Illuminate\Support\Collection;
use atk4\ui\Menu as BaseMenu;
use Epesi\Core\Layout\Integration\Joints\NavMenuJoint;

class NavMenu extends BaseMenu
{
    public $ui = 'inverted nav menu';
    
	public function init()
	{		
		parent::init();
		
		$this->addHeader($this->app->title);
		
		$items = collect();
		foreach(NavMenuJoint::collect() as $joint) {
			$items = $items->merge($joint->items());
		}

		$this->addItems($this, $items);
		
// 		$this->js(true)->find('.toggle-group .header')->click(new jsFunction(['e'], [new jsExpression('$(e.target).next(".menu").slideToggle()')]))->click();
		
// 		$this->app->addStyle('
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
			
			if (!isset($entry['action']) && !is_array($entry)) {
				$entry = ['action' => $entry];
			}
			
			$entry['item'] = $entry['item'] ?? $caption;
			
			if (is_array($entry['item'])) {
				$entry['item'] = [$caption] + $entry['item'];
			}
			
			if ($subitems = $entry['menu'] ?? []) {
				$submenu = $menu->addMenu($entry['item']);
				
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
}