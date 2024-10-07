<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


use App\Models\ {
    User
};

use App\Core\ {
    ServiceContainer,
    Logger
};

use App\Facades\ {
    Facade
};

use App\HTTP\ {
    View,
    Response,
    UrlGenerator,
    Redirect
};

use App\Helpers\ {
    Sanitizer,
    Validator,
    Filter,
};

use App\Services\ {
    AuthService,
    UserService,
    AssetsService
};


// Initialize the service container
$container = new ServiceContainer();

/**
 * return the database connection instance 
 * @param string $name
 * @param Closure $function
 * 
 * @return PDO
 */
$container->registerLazy('db', function() {
    // function db() is defined in config/database.php 

    return db();  
});

/**
 * return the user model instance
 * @param string $name
 * @param Closure $function
 * 
 * @return User
 */
$container->registerLazy(User::class, function() use ($container) {
    return new User($container->getLazy('db'));
});

/**
 * return the logger instance
 * @param string $name
 * @param Closure $function
 * 
 * @return Logger
 */
$container->registerLazy(Logger::class, function() {
    return new Logger(__DIR__ . '/../storage/logs/app.log');  // Esempio di un logger
});


/**
 * return the sanitizer instance
 * @param string $name
 * @param Closure $function
 * 
 * @return Sanitizer
 */
$container->registerLazy(Sanitizer::class, function () {
    return new Sanitizer();
});

/**
 * return the validator instance
 * @param string $name
 * @param Closure $function
 * 
 * @return Validator
 */
$container->registerLazy(Validator::class, function () use ($container) {
    return new Validator($container->getLazy('db'));  // Passa il database alla classe Validator
});

/**
 * return the filter instance
 * @param string $name
 * @param Closure $function
 * 
 * @return Filter
 */
$container->registerLazy(Filter::class, function () use ($container) {
    return new Filter($container->getLazy(Sanitizer::class), $container->getLazy(Validator::class));
});

/**************************************************** SERVICE ****************************************************/

/**
 * return the auth service instance
 * @param string $name
 * @param Closure $function
 * 
 * @return AuthService
 */

$container->registerLazy(AuthService::class, function () use ($container) {
    return new AuthService($container->getLazy(User::class));
});

/**
 * return the user service instance
 * @param string $name
 * @param Closure $function
 * 
 * @return UserService
 */
$container->registerLazy(UserService::class, function () use ($container) {
    return new UserService($container->getLazy(User::class));
});

/**
 * return the assets service instance
 * @param string $name
 * @param Closure $function
 * 
 * @return AssetsService
 */
$container->registerLazy(AssetsService::class, function () {
    return new AssetsService();
});

/**************************************************** FACADE ****************************************************/

Facade::setContainer($container);

/**
 * return the view instance
 * @param string $name
 * @param Closure $function
 * 
 * @return View
 */
$container->registerLazy(View::class, function () {
    return new View();
});

/**
 * return the response instance
 * @param string $name
 * @param Closure $function
 * 
 * @return Response
 */
$container->registerLazy(Response::class, function () {
    return new Response();
});

/**
 * return the url generator instance
 * @param string $name
 * @param Closure $function
 * 
 * @return UrlGenerator
 */
$container->registerLazy(UrlGenerator::class, function () {
    return new UrlGenerator();
});

/**
 * return the redirect instance
 * @param string $name
 * @param Closure $function
 * 
 * @return Redirect
 */
$container->registerLazy(Redirect::class, function () use ($container) {
    return new Redirect($container->getLazy(UrlGenerator::class));
});
