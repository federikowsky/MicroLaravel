<?php
// src/Middleware/CsrfMiddleware.php

namespace App\Middlewares;


class CSRFMiddleware
{
    protected $container;

    public function __construct()
    {
    }

    private function verify_csrf_token(string $token): bool
    {
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public function handle(callable $next)
    {
        // Applica il controllo CSRF solo alle richieste che modificano dati
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if (!isset($_POST['csrf_token']) || !$this->verify_csrf_token($_POST['csrf_token'])) {
                // Gestisci il fallimento del token CSRF
                throw new \Exception('Invalid CSRF token');
            }
        }

        // Continua con il flusso della richiesta
        call_user_func($next);
    }
}
