<?php 

namespace Epesi\Core\System\Modules\Concerns;

trait Notifies
{
    public function notify($options)
    {
        $options = array_merge(['duration'=> 1500], is_array($options)? $options: ['message' => $options]);

        return new \atk4\ui\JsToast($options);
    }
    
    public function notifySuccess($options)
    {
        $options = array_merge(['class' => 'success'], is_array($options)? $options: ['message' => $options]);
        
        return $this->notify($options);
    }
    
    public function notifyError($options)
    {
        $options = array_merge(['class' => 'error'], is_array($options)? $options: ['message' => $options]);
        
        return $this->notify($options);
    }
}