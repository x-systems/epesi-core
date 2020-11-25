<?php

namespace Epesi\Core\Layout\View;

use Illuminate\Support\Facades\URL;
use atk4\ui\VirtualPage;
use Closure;
use Epesi\Core\System\View\LaunchButton;
use atk4\ui\JsModal;
use atk4\ui\View;

class Launchpad extends View
{
	protected $title = 'Launchpad';
	
	protected $virtualPage;
		
	protected function init(): void
	{
		$this->virtualPage = VirtualPage::addTo($this->getOwner())->set(Closure::fromCallable([$this, 'getContents']));
		
		parent::init();
	}
	
	public function getJsModal()
	{
		return new JsModal($this->title, $this->virtualPage);
	}
	
    protected function getContents(VirtualPage $vp)
    {
    	$vp->add([
    			new LaunchButton([
    					'label' => 'Test Button 1',
    					'icon' => 'user'
    			])
    	])->link(URL::to('/'));
    	
    	$vp->add([
    			new LaunchButton([
    					'label' => 'Test Button 2',
    					'icon' => 'car'
    			])
    	]);
    	
    	$vp->add([
    			new LaunchButton([
    					'label' => 'Test Button 3',
    					'icon' => 'bus'
    			])
    	]);
    }
}
