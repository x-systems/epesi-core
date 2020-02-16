<?php 

namespace Epesi\Core\Layout\Integration\Joints;

use Epesi\Core\System\Modules\ModuleJoint;

abstract class NavMenuJoint extends ModuleJoint
{
	abstract public function items();
}