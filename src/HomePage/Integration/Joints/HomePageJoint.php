<?php 

namespace Epesi\Core\HomePage\Integration\Joints;

use Epesi\Core\System\Modules\ModuleJoint;

abstract class HomePageJoint extends ModuleJoint
{
	/**
	 * Caption to display for the homepage
	 */
	abstract public function caption();
	
	/**
	 * Link to the homepage
	 * 
	 * @return string 
	 */
	abstract public function link();
}