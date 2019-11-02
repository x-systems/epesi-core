<?php

namespace Epesi\Core\System;

use Epesi\Core\System\Integration\Modules\ModuleView;
use Epesi\Core\System\Integration\Joints\SystemSettingsJoint;
use Illuminate\Support\Facades\Auth;

class SystemSettings extends ModuleView
{
	protected $label = 'System Administration';
	
	public static function access()
	{
		return Auth::user()->can('modify system settings');
	}
	
	public function body()
	{
		$layout = $this->add(['View'])->addStyle('max-width:1200px;margin:auto;');
		$layout->add(['Header', __($this->label)]);
		$segment = $layout->add(['View', ['ui' => 'segment']]);

		$sections = [];
		/**
		 * @var SystemSettngsJoint $joint
		 */
		foreach (SystemSettingsJoint::collect() as $joint) {
			$sections[$joint->section()][$joint->label()] = $joint;
		}

		ksort($sections);
		
		foreach ($sections as $sectionName => $sectionJoints) {
			$segment->add(['Header', $sectionName]);
			
			ksort($sectionJoints);
			
			foreach ($sectionJoints as $joint) {
				$segment->add($joint->button());
			}
		}
	}
}
