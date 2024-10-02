<?php

session_start();

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/libs/helpers.php';
require_once __DIR__ . '/libs/flash.php';
require_once __DIR__ . '/libs/Sanitizer.php';
require_once __DIR__ . '/libs/Validator.php';
require_once __DIR__ . '/libs/Filter.php';
require_once __DIR__ . '/libs/Logger.php';
require_once __DIR__ . '/Models/User.php';
require_once __DIR__ . '/ServiceContainer.php';
require_once __DIR__ . '/Router.php';


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
 * return the logger instance
 * @param string $name
 * @param Closure $function
 * 
 * @return Logger
 */
$container->registerLazy('logger', function() {
    return new Logger(__DIR__ . '/../logs/app.log');  // Esempio di un logger
});


/**
 * return the sanitizer instance
 * @param string $name
 * @param Closure $function
 * 
 * @return Sanitizer
 */
$container->registerLazy('sanitizer', function () {
    return new Sanitizer();
});

/**
 * return the validator instance
 * @param string $name
 * @param Closure $function
 * 
 * @return Validator
 */
$container->registerLazy('validator', function () use ($container) {
    $db = $container->getLazy('db'); // Ottieni la connessione al database
    return new Validator($db);  // Passa il database alla classe Validator
});

/**
 * return the filter instance
 * @param string $name
 * @param Closure $function
 * 
 * @return Filter
 */
$container->registerLazy('filter', function () use ($container) {
    return new Filter($container->getLazy('sanitizer'), $container->getLazy('validator'));
});
