<?php

namespace App\Facades;

use App\Core\ServiceContainer;

abstract class Facade
{
    protected static $container;

    public static function setContainer(ServiceContainer $container)
    {
        static::$container = $container;
    }

    protected static function getFacadeAccessor()
    {
        throw new \Exception('Facade does not implement getFacadeAccessor method.');
    }

    public static function __callStatic($method, $arguments)
    {
        $instance = static::$container->getLazy(static::getFacadeAccessor());

        if (! $instance) {
            throw new \Exception("Service not found.");
        }

        return $instance->$method(...$arguments);
    }
}
