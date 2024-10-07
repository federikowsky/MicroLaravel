<?php

// Carica il file bootstrap
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

use App\Controllers\ExceptionController;
use App\Core\Logger;
use App\HTTP\Router;

// Imposta il gestore delle eccezioni
$exceptionHandler = new ExceptionController($container->getLazy(Logger::class));
set_exception_handler([$exceptionHandler, 'handle']);

// Inizializza il router
$router = new Router($container);

// Carica le rotte dai file di configurazione modulari
$router->loadRoutes([
    __DIR__ . '/../routes/app.php',
    __DIR__ . '/../routes/auth.php',
    __DIR__ . '/../routes/admin.php',
    __DIR__ . '/../routes/user.php',
    __DIR__ . '/../routes/post.php'
]);

// Analizza l'URI richiesta
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->route($uri);