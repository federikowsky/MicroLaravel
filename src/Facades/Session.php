<?php

namespace App\Facades;

use App\Facades\BaseFacade;
use App\Core\Session as SessionService;

class Session extends BaseFacade
{
    protected static function get_facade_accessor()
    {
        // name of the service in the container
        return SessionService::class;
    }
}
