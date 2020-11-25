<?php

namespace Epesi\Core\System;

use Epesi\Core\System\Modules\ModuleView;
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
		$layout = \atk4\ui\View::addTo($this)->addStyle('max-width:1200px;margin:auto;');
		\atk4\ui\Header::addTo($layout, [__($this->label)]);
		$segment = \atk4\ui\View::addTo($layout, ['ui' => 'segment']);

		$sections = [];
		/**
		 * @var SystemSettingsJoint $joint
		 */
		foreach (SystemSettingsJoint::collect() as $joint) {
			$sections[$joint->section()][$joint->label()] = $joint;
		}

		ksort($sections);
		
		foreach ($sections as $sectionName => $sectionJoints) {
			\atk4\ui\Header::addTo($segment, [$sectionName]);
			
			ksort($sectionJoints);
			
			foreach ($sectionJoints as $joint) {
				$segment->add($joint->button());
			}
		}
	}
}
