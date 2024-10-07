<?php

namespace App\Facades;

use App\Facades\Facade;
use App\HTTP\Response as ResponseService;

class Response extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ResponseService::class;
    }
}
