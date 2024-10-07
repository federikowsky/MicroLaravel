<?php

namespace App\Facades;

use App\Facades\Facade;
use App\HTTP\View as ViewService;

class View extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ViewService::class;
    }
}
