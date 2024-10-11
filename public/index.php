<?php

// Carica il file bootstrap
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

use App\Core\ExceptionManager;
use App\Core\Logger;
use App\HTTP\Router;

// Set the exception handler
$exceptionHandler = new ExceptionManager($container->getLazy(Logger::class));
set_exception_handler([$exceptionHandler, 'handle']);

// Initialize the router
$router = $container->getLazy(Router::class);


// Load the routes from the configuration files
$router->load_routes([
    __DIR__ . '/../routes/app.php',
    __DIR__ . '/../routes/auth.php',
    __DIR__ . '/../routes/admin.php',
    __DIR__ . '/../routes/user.php',
    __DIR__ . '/../routes/post.php'
]);

// Route the request
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->route($uri);