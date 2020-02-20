<?php

namespace Epesi\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Epesi\Core\UI as App;

class UI extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return App::class;
    }
}
