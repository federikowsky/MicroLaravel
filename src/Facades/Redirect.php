<?php

namespace App\Facades;

use App\Facades\BaseFacade;
use App\HTTP\Redirect as RedirectService;

class Redirect extends BaseFacade
{
    protected static function get_facade_accessor()
    {
        // name of the service in the container
        return RedirectService::class;
    }
}