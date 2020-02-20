<?php

namespace Epesi\Core\Layout\Seeds;

use atk4\ui\Menu;
use atk4\ui\Item;

class ActionBar extends Menu
{
    public $ui = 'actionbar menu';
    
	protected static $buttons = [];
	
	protected static $menus = [];
	
	protected static function getPredefined($key)
	{
		$predefined = [
				'back' => [
						__('Back'),
						'icon' => 'arrow left',
						'weight' => 10000,
						'attr' => [
								'href' => $_SERVER['HTTP_REFERER']?? 'javascript:window.history.back()'
						],
				],
				'save' => [
						__('Save'),
						'icon' => 'save',
				],
				'add' => [
						__('Add'),
						'icon' => 'add',
				],
				'edit' => [
						__('Edit'),
						'icon' => 'edit'
				],
				'delete' => [
						__('Delete'),
						'icon' => 'trash'
				],
		];
		
		return $predefined[$key] ?? ['label' => $key];
	}
	
	public function renderView()
    {
        $this->elements = collect($this->elements)->sortByDesc(function ($element) {
            return $element->weight ?? 10;
        })->toArray();

        return parent::renderView();
	}
	
	/**
	 * Adds a button to the ActionBar
	 * 
	 * @param string|array|ActionBarItem $button
	 * 
	 * @return Item
	 */
	public static function addItemButton($button, $defaults = [])
	{
		$button = is_string($button)? self::getPredefined($button): $button;
		
		$button = is_array($button)? new ActionBarItem($button): $button;
		
		$actionBar = self::instance();
		
		return $actionBar->addItem($actionBar->mergeSeeds($button, $defaults));
	}
	
	public static function addButtons($buttons)
	{
		foreach ((array) $buttons as $button) {
			self::addItemButton($button);
		}
	}
	
	public static function addMenuButton($menu)
	{
	    return self::instance()->addMenu($menu);
	}

	public static function clear()
	{
		self::instance()->elements = null;
	}
	
	/**
	 * @return self
	 */
	public static function instance()
	{
		return ui()->layout->actionBar;
	}
}
