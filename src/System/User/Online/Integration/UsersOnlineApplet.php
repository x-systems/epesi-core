<?php 

namespace Epesi\Core\System\User\Online\Integration;

use Epesi\Base\Dashboard\Integration\Joints\AppletJoint;
use Epesi\Base\Dashboard\View\Applet;
use Epesi\Core\System\Modules\Concerns\HasOptions;

class UsersOnlineApplet extends AppletJoint
{
	use HasOptions;
	
	public function caption()
	{
		return __('Users Online');
	}
	
	public function info()
	{
		return __('Shows users currently online');
	}

	public function body(Applet $applet, $options = [])
	{
		
	}
}