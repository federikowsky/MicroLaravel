<?php

namespace App\Middlewares;

use App\Exceptions\Security\ExpiredTokenException;
use App\Facades\EWT;

class EWTMiddleware
{
    protected $container;

    public function __construct()
    {
    }


    public function handle(callable $next)
    {
        if (request()->missing('ewt')) {
            throw new ExpiredTokenException('Token not found.');
        }

        try {
            EWT::decode(request()->input('ewt'));
        } catch (\Exception $e) {
            throw $e;
        }

        // Continua con il flusso della richiesta
        return $next();
    }
}
