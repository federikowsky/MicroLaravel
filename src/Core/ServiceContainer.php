<?php

namespace App\Core;

use Closure;
use ReflectionClass;

class ServiceContainer {
    protected $services = [];

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
    public function getLazy($name) {
        // If service is already registered, resolve if necessary
        if (isset($this->services[$name])) {
            // If the service is a closure, resolve it
            if (is_callable($this->services[$name])) {
                $this->services[$name] = call_user_func($this->services[$name]);
            }
            return $this->services[$name];
        }

        // If the service is not registered, attempt to resolve the class automatically
        if (class_exists($name)) {
            return $this->resolve($name);
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
    public function resolve($className): ?object {
        $reflector = new ReflectionClass($className);

        // Check if the class has a constructor
        $constructor = $reflector->getConstructor();
        if (!$constructor) {
            return new $className;
        }

        // Get the constructor parameters
        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getType();
            
            if ($dependency && !$dependency->isBuiltin()) {
                // Resolve the dependency
                $dependencies[] = $this->getLazy($dependency->getName());
            } else {
                throw new \Exception("Unable to resolve dependency [{$parameter->name}]");
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
