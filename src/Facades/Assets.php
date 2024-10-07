<?php

namespace App\Facades;

use App\Facades\Facade;
use App\Services\AssetsService;

class Assets extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AssetsService::class; // Il nome registrato nel container
    }
}
