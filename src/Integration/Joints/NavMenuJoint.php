<?php 

namespace Epesi\Core\Integration\Joints;

use Epesi\Core\Integration\Module\ModuleJoint;

abstract class NavMenuJoint extends ModuleJoint
{
	abstract public function items();
}