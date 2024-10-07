<?php

namespace App\Facades;

use App\Facades\Facade;
use App\HTTP\Redirect as RedirectService;

class Redirect extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RedirectService::class;
    }
}