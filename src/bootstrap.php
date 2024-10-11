<?php

use App\Core\ {
    ServiceContainer,
    Logger,
    Session,
};

use App\Facades\ {
    BaseFacade
};
use App\HTTP\ {
    Router
};

use App\Services\ {
    EncryptionService
};

use App\Session\ {
    SessionManager,
};



require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Initialize the service container
$container = ServiceContainer::get_instance();


/**
 * return the database connection instance 
 * @param string $name
 * @param Closure $function
 * 
 * @return PDO
 */
$container->registerLazy(PDO::class, function(): PDO {
    return db();  
});

/*****************************************************************************************************************/
/*--------------------------------------------------- SESSION ---------------------------------------------------*/
/*****************************************************************************************************************/


// Initialize the session manager
$session_config = require __DIR__ . '/../config/session.php';
$session_manager = new SessionManager($session_config, $container);



/***************************************************************************************************************/
/*--------------------------------------------------- MODEL ---------------------------------------------------*/
/***************************************************************************************************************/



/**************************************************************************************************************/
/*--------------------------------------------------- CORE ---------------------------------------------------*/
/**************************************************************************************************************/

/**
 * return the logger instance
 * @param string $name
 * @param Closure $function
 * 
 * @return Logger
 */
$container->registerLazy(Logger::class, function(): Logger {
    return new Logger(__DIR__ . '/../storage/logs/app.log');  // Esempio di un logger
});

/**
 * return the session instance
 * @param string $name
 * @param Closure $function
 * 
 * @return Session
 */
$container->registerLazy(Session::class, function() use ($session_manager): Session {
    return new Session($session_manager->driver());
});

/*****************************************************************************************************************/
/*--------------------------------------------------- HELPERS ---------------------------------------------------*/
/*****************************************************************************************************************/



/*****************************************************************************************************************/
/*--------------------------------------------------- SERVICE ---------------------------------------------------*/
/*****************************************************************************************************************/

/**
 * return the encryption service instance
 * @param string $name
 * @param Closure $function
 * 
 * @return EncryptionService
 */
$container->registerLazy(EncryptionService::class, function (): EncryptionService {
    return new EncryptionService(APP_KEY);
});

/****************************************************************************************************************/
/*--------------------------------------------------- HTTP ---------------------------------------------------*/
/****************************************************************************************************************/

BaseFacade::set_container($container);

$container->registerLazy(Router::class, function() use ($container): Router {
    return new Router($container);
});