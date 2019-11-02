<?php 

namespace Epesi\Core\Layout\Integration\Joints;

use Epesi\Core\System\Integration\Modules\ModuleJoint;

abstract class NavMenuJoint extends ModuleJoint
{
	abstract public function items();
}