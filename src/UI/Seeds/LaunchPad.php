<?php

namespace Epesi\Core\UI\Seeds;

use Illuminate\Support\Facades\URL;
use atk4\ui\jsExpression;
use atk4\ui\VirtualPage;
use Closure;

class LaunchPad extends jsExpression
{
	protected $parent;
	
	public function __construct($parent)
	{
    	$this->parent = $parent;
    	
		parent::__construct('$(this).atkCreateModal([arg])', [
				'arg' => [
						'uri' => $this->getURL(),
						'title' => __('Launchpad'),
						'mode' => 'json'
				]
		]);
	}
    
	protected function getURL()
    {
    	return $this->parent->add('VirtualPage')->set(Closure::fromCallable([$this, 'getContents']))->getJSURL('cut');
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
