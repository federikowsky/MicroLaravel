<?php

require_once '../src/bootstrap.php';
require_once '../src/Router.php';
require_once '../src/Controllers/ErrorController.php';


$exceptionHandler = new ErrorController($container->getLazy('logger'));
set_exception_handler([$exceptionHandler, 'handle']);


// inizialize the router
$router = new Router($container);

// Load the routes from the modular configuration files
$router->loadRoutes([
    __DIR__ . '/../config/routes/app.php',
    __DIR__ . '/../config/routes/auth.php',
    __DIR__ . '/../config/routes/admin.php',
    __DIR__ . '/../config/routes/user.php',
    __DIR__ . '/../config/routes/post.php'
]);

// Parse the request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->route($uri);
