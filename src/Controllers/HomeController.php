<?php

namespace Epesi\Core\Controllers;

use Illuminate\Routing\Controller;
use Epesi\Core\System\SystemCore;
use Epesi\Core\HomePage\HomePageCommon;

class HomeController extends Controller
{
    public function index()
    {
    	return SystemCore::isInstalled()? redirect('home'): redirect('install');
    }
    
    public function home()
    {
    	return redirect(HomePageCommon::getUserHomePagePath());
    }
}
