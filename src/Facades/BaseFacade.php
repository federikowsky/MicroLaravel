<?php

namespace App\Facades;

use App\Core\ServiceContainer;

abstract class BaseFacade
{
    protected static $container;

    public static function set_container(ServiceContainer $container)
    {
        static::$container = $container;
    }

    public static function get_container()
    {
        return static::$container;
    }

    protected static function get_facade_accessor()
    {
        throw new \Exception('BaseFacade does not implement getFacadeAccessor method.');
    }

    public static function __callStatic($method, $arguments)
    {
        $instance = static::$container->getLazy(static::get_facade_accessor());

        if (! $instance) {
            throw new \Exception("Service not found.");
        }

        return $instance->$method(...$arguments);
    }
}
