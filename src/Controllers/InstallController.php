<?php

namespace Epesi\Core\Controllers;

use Illuminate\Routing\Controller;
use Epesi\Core\App as Epesi;
use Epesi\Core\System\SystemInstallWizard;

class InstallController extends Controller
{
	public function index(Epesi $epesi)
	{
		$epesi->title = config('epesi.app.title') . ' > ' . __('Installation');
		
		$epesi->initLayout('Centered');
		
		$epesi->add(new SystemInstallWizard());
		
		return $epesi->response();
	}
}
