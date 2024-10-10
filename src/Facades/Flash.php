<?php

namespace App\Facades;

use App\Facades\BaseFacade;
use App\Core\Flash as FlashService;

class Flash extends BaseFacade
{
    protected static function get_facade_accessor()
    {
        // name of the service in the container
        return FlashService::class;
    }
}