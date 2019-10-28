<?php 

namespace Epesi\Core\Integration\Concerns;

use Epesi\Core\UI\Seeds\LaunchButton;
use Epesi\Core\Integration\Module\ModuleView;

trait HasLaunchButton
{
	/**
	 * Label to display on the launch button
	 */
	abstract public function label();
	
	/**
	 * Icon to display on the launch button
	 */
	abstract public function icon();
	
	/**
	 * Define the launch button link
	 */
	public function link() {
		return '';
	}

	/**
	 * Define the launch button
	 * 
	 * @return HasLaunchButton
	 */
	final public function button()
	{
		$link = $this->link();
		
		$link = is_array($link)? ModuleView::moduleLink(...$link): $link;
		
		return (new LaunchButton([
				'label' => $this->label(),
				'icon' => $this->icon()
		]))->link($link);
	}
	
}