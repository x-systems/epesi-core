<?php 

namespace Epesi\Core\Integration\Concerns;

trait Notifies
{
	public function notify($options, $attachTo = null)
	{
		$options = array_merge(['duration'=> 1500], is_array($options)? $options: ['content' => $options]);
		
		$attachTo = $attachTo?: $this;
		
		return (new \atk4\ui\jsNotify($options, $attachTo));
	}
	
	public function notifyError($options, $attachTo = null)
	{
		$options = array_merge(['color' => 'red'], is_array($options)? $options: ['content' => $options]);
		
		return $this->notify($options, $attachTo);
	}
}