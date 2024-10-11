<?php

namespace App\Core;

use Closure;
use ReflectionClass;

class ServiceContainer {
    protected $services = [];

    public static function get_instance(): ServiceContainer {
        static $instance = null;
        if ($instance === null) {
            $instance = new static();
        }
        return $instance;
    }

    public static function get_container(): ServiceContainer {
        return static::get_instance();
    }

    /**
     * Store a service in the container.
     *
     * @param string $name
     * @param mixed $service
     */
    public function register($name, $service) {
        $this->services[$name] = $service;
    }

    /**
     * Get a service from the container.
     *
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function get($name) {
        if (!isset($this->services[$name])) {
            throw new \Exception("Servizio '{$name}' non trovato.");
        }

        return $this->services[$name];
    }

    /**
     * Store a lazy service in the container.
     *
     * @param string $name
     * @param Closure $callable
     */
    public function registerLazy($name, Closure $callable) {
        $this->services[$name] = $callable;
    }

    /**
     * Get a lazy service from the container. If the service is a closure, resolve it.
     *
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function getLazy($name, array $parameters = []) {
        // If service is already registered, resolve if necessary
        if (isset($this->services[$name])) {
            // If the service is a closure, resolve it
            if (is_callable($this->services[$name])) {
                $this->services[$name] = call_user_func($this->services[$name], ...$parameters);
            }
            return $this->services[$name];
        }

        // If the service is not registered, attempt to resolve the class automatically
        if (class_exists($name)) {
            $instance = $this->resolve($name, $parameters);
            $this->register($name, $instance);
            return $instance;
        }

        throw new \Exception("Service '{$name}' not found and could not be resolved.");
    }

    /**
     * Resolve a class and its dependencies automatically.
     *
     * @param string $className
     * @return object
     * @throws \Exception
     * 
     */    
    public function resolve($className, array $parameters = [])
    {
        $reflector = new ReflectionClass($className);

        // Verifica se la classe ha un costruttore
        $constructor = $reflector->getConstructor();
        if (!$constructor) {
            return new $className;
        }

        // Ottieni i parametri del costruttore
        $dependencies = $constructor->getParameters();
        $resolvedParameters = [];

        foreach ($dependencies as $dependency) {
            $type = $dependency->getType();

            // Controlla se il tipo è una classe e non un tipo built-in
            if ($type && !$type->isBuiltin()) {
                // Risolvi le dipendenze usando il container
                $resolvedParameters[] = $this->getLazy($type->getName());
            } else {
                // Se il parametro è stato passato manualmente, usalo
                if (isset($parameters[$dependency->getName()])) {
                    $resolvedParameters[] = $parameters[$dependency->getName()];
                } elseif ($dependency->isDefaultValueAvailable()) {
                    // Usa il valore predefinito se disponibile
                    $resolvedParameters[] = $dependency->getDefaultValue();
                } else {
                    throw new \Exception("Impossibile risolvere la dipendenza [{$dependency->getName()}] per la classe {$className}");
                }
            }
        }

        return $reflector->newInstanceArgs($resolvedParameters);
    }
}
