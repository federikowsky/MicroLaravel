<?php
// src/Middleware/authmiddleware.php

require_once __DIR__ . '/../Controllers/AuthController.php';

class AuthMiddleware
{
    protected $authController;

    public function __construct(ServiceContainer $container)
    {
        $this->authController = new AuthController($container);
    }

    public function handle(callable $next)
    {
        // Verifica se l'utente è loggato
        if (!$this->authController->is_user_logged_in()) {
            // L'utente non è autenticato, quindi reindirizza al login
            header('Location: /auth/login');
            return;
        }

        // L'utente è autenticato, quindi esegui il prossimo step della richiesta
        call_user_func($next);
    }
}
