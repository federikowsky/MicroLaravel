<?php

namespace App\Session;

use App\Session\Drivers\ {
    FileSessionDriver,
    ArraySessionDriver,
    DatabaseSessionDriver
};
use App\Session\Contracts\SessionDriverInterface;
use App\Core\ServiceContainer;

class SessionManager
{
    protected $driver;
    protected $container;

    public function __construct($config, ServiceContainer $container)
    {
        $this->container = $container;
        $this->driver = $this->resolveDriver($config['driver'], $config);
    }

    protected function resolveDriver($driverName, $config)
    {
        switch ($driverName) {
            case 'file':
                // Usa il service container per risolvere il driver con i parametri necessari
                return $this->container->getLazy(FileSessionDriver::class, [
                    'session_path' => $config['session_path']
                ]);

            case 'array':
                return $this->container->getLazy(ArraySessionDriver::class);

            case 'database':
                return $this->container->getLazy(DatabaseSessionDriver::class, [
                    'db' => $this->container->getLazy('db')
                ]);

            default:
                throw new \Exception('Unsupported session driver.');
        }
    }

    public function driver(): SessionDriverInterface
    {
        return $this->driver;
    }
}

