<?php 

namespace Epesi\Core\Layout\Seeds;

use Illuminate\Support\Collection;
use atk4\ui\Menu as BaseMenu;
use atk4\ui\Accordion;
use atk4\ui\jsExpressionable;
use Epesi\Core\Layout\Integration\Joints\NavMenuJoint;

class NavMenu extends BaseMenu
{
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
		})->map(function($item, $caption) use ($menu) {
			if (! ($item['access']?? true)) return;
			
			if (!isset($item['link']) && !is_array($item)) {
				$item = [
						'link' => $item
				];
			}
			
			$item['caption'] = $item['caption']?? $caption;
			
			if (is_array($item['caption'])) {
				$item['caption'] = [$caption] + $item['caption'];
			}
			
			if ($subitems = $item['menu']?? []) {
				$submenu = $menu->addMenu($item['caption']);
				
				self::addItems($submenu, collect($subitems));
			}
			elseif ($subitems = $item['group']?? []) {
				$subgroup = $menu->addGroup($item['caption']);
				
				if (($item['toggle']?? false) && !$menu->in_dropdown) {
					$subgroup->addClass('toggle-group');
					$subgroup->add(['Icon', 'dropdown'], 'Icon')->removeClass('item');
				}
				
				self::addItems($subgroup, collect($subitems));
			}
			elseif ($subitems = $item['accordion']?? []) {
				$accordion = $menu->add(['Accordion']);
				
				$section = $accordion->addSection($item['caption']);
				
				foreach ($subitems as $subitem) {
					$subitem = $section->add(['Item', 'Test', 'ui' => 'item'])->setElement('a');
					
					$action = null;
					$link = $item['link']?? '';
					if (is_string($link) || is_array($link)) {
						$action = $section->url($link);
					}
					
					if (is_string($link)) {
						$subitem->setAttr('href', $link);
					}
					
					if ($action instanceof jsExpressionable) {
						$subitem->js('click', $link);
					}
				}
// 				self::addItems($accordion, collect($subitems));
			}
			else {
				if (is_a($menu, Accordion::class)) {
					$menu->add(['View', 'Test']);
				}
				else {
					$menu->addItem($item['caption'], $item['link']?? '');
				}
				
			}
		});
	}
}