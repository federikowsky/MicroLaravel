<?php

namespace App\Facades;

use App\Facades\BaseFacade;
use App\HTTP\View as ViewService;

class View extends BaseFacade
{
    protected static function get_facade_accessor()
    {
        // name of the service in the container
        return ViewService::class;
    }
}
