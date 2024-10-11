<?php

namespace App\Facades;

use App\Facades\BaseFacade;
use App\HTTP\Response as ResponseService;

class Response extends BaseFacade
{
    protected static function get_facade_accessor()
    {
        // name of the service in the container
        return ResponseService::class;
    }
}
