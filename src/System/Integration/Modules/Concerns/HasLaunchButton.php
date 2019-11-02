<?php 

namespace Epesi\Core\System\Integration\Modules\Concerns;

use Epesi\Core\System\Seeds\LaunchButton;
use Epesi\Core\System\Integration\Modules\ModuleView;

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
	 * 
	 * @return array|string
	 */
	public function link() {
		return '';
	}

	/**
	 * Define the launch button
	 * 
	 * @return LaunchButton
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