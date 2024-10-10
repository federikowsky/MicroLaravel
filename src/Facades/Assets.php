<?php

namespace App\Facades;

use App\Facades\BaseFacade;
use App\Services\AssetsService;

class Assets extends BaseFacade
{
    protected static function get_facade_accessor()
    {
        // name of the service in the container
        return AssetsService::class; 
    }
}
