<?php

namespace App\Facades;

use App\Facades\BaseFacade;
use App\HTTP\Support\RouterHelper as RouterHelperService;

class RouterHelper extends BaseFacade
{
    protected static function get_facade_accessor()
    {
        // name of the service in the container
        return RouterHelperService::class;
    }
}
