<?php

namespace App\Middlewares;

use App\Exceptions\Security\ExpiredTokenException;
use App\Facades\CWT;

class CWTMiddleware
{
    protected $container;

    public function __construct()
    {
    }


    public function handle(callable $next)
    {
        if (request()->missing('cwt')) {
            throw new ExpiredTokenException('Token not found.');
        }

        try {
            CWT::decode(request()->input('cwt'));
        } catch (\Exception $e) {
            throw $e;
        }

        // Continua con il flusso della richiesta
        return $next();
    }
}
