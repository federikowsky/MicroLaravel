<?php

namespace App\Facades;

use App\Facades\BaseFacade;
use App\Services\Security\CustomWebTokenService;

class CWT extends BaseFacade
{
    protected static function get_facade_accessor()
    {
        return CustomWebTokenService::class;
    }
}
