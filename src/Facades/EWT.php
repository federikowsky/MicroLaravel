<?php 

namespace App\Facades;

use App\Facades\BaseFacade;
use App\Services\Security\EncryptedWebTokenService;

class EWT extends BaseFacade
{
    protected static function get_facade_accessor()
    {
        return EncryptedWebTokenService::class;
    }
}
