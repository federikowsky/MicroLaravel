<?php
// src/Middleware/authmiddleware.php

namespace App\Middlewares;

use App\HTTP\Request;
use App\Services\AuthService;

class AuthMiddleware
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function handle(callable $next)
    {
        // Verifica se l'utente è loggato
        if (!$this->authService->is_user_logged_in()) {
            // L'utente non è autenticato, quindi reindirizza al login
            header('Location: /auth/login');
            return;
        }

        // L'utente è autenticato, quindi esegui il prossimo step della richiesta
        // call_user_func($next);
        return $next();
    }
}
