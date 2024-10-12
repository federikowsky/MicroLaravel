<?php
// src/Middleware/CsrfMiddleware.php

namespace App\Middlewares;

use App\Exceptions\Auth\CsrfTokenMismatchException;

class CSRFMiddleware
{
    protected $container;

    public function __construct()
    {
    }

    private function verify_csrf_token(string $token): bool
    {
        return hash_equals(session()->get('_csrf_token'), $token);
    }

    public function handle(callable $next)
    {
        // Applica il controllo CSRF solo alle richieste che modificano dati
        if (in_array(request()->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if (request()->missing('csrf_token') || !$this->verify_csrf_token(request()->post('csrf_token'))) {
                // Gestisci il fallimento del token CSRF
                throw new CsrfTokenMismatchException('Invalid CSRF token');
            }
        }

        // Continua con il flusso della richiesta
        // call_user_func($next);
        return $next();
    }
}
