<?php
// src/Middleware/authmiddleware.php

require __DIR__ . '/../Controllers/AdminController.php';

class AdminMiddleware
{
    protected $adminController;

    public function __construct(ServiceContainer $container)
    {
        $this->adminController = new AdminController($container);
    }

    public function handle(callable $next)
    {
        // Verifica se l'utente è loggato
        if (!$this->adminController->is_admin()) {
            // L'utente non è autenticato, quindi reindirizza al login
            header('Location: /');
            return;
        }

        // L'utente è autenticato, quindi esegui il prossimo step della richiesta
        call_user_func($next);
    }
}