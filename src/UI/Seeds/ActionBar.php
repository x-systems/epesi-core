<?php

namespace Epesi\Core\UI\Seeds;

use atk4\ui\View;
use Illuminate\Support\Collection;

class ActionBar extends View
{
	public $ui = 'actionbar segment';
	
	/**
	 * @var Collection
	 */
	protected static $buttons;
	
	protected static function getPredefined($key)
	{
		$predefined = [	
				'back' => [
						'label' => __('Back'),
						'icon' => 'arrow left',
						'weight' => 10000,
						'attr' => [
								'href' => $_SERVER['HTTP_REFERER']?? 'javascript:window.history.back()'
						],
				],
				'save' => [
						'label' => __('Save'),
						'icon' => 'save',
				],
				'edit' => [
						'label' => __('Edit'),
						'icon' => 'edit'
				],
				'delete' => [
						'label' => __('Delete'),
						'icon' => 'trash'
				],
		];
		
		return $predefined[$key]?? ['label' => $key];
	}
	
	public function __construct($label = null, $class = null)
	{
		parent::__construct($label, $class);
		
		self::$buttons = collect();
	}
	
	public function renderView()
	{
		$this->prepareButtons();
		
		parent::renderView();
	}
	
	protected function prepareButtons()
	{
		foreach (self::$buttons->sortByDesc(function ($button) {
			return $button->weight;
		}) as $button) {
			$this->add($button);
		}
	}
	
	public static function addButton($button)
	{
		if (is_string($button)) {
			$button = self::getPredefined($button);
		}
		
		if (is_array($button)) {
			$button = new ActionButton($button);
		}
		
		self::$buttons->add($button);
		
		return $button;
	}
	
	public static function addButtons($buttons)
	{
		foreach (is_array($buttons)? $buttons: [$buttons] as $button) {
			self::addButton($button);
		}
	}

	public static function clear()
	{
		self::$buttons = [];
	}
}
