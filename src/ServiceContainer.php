<?php

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
     * @throws Exception
     */
    public function get($name) {
        if (!isset($this->services[$name])) {
            throw new Exception("Servizio '{$name}' non trovato.");
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
     * @throws Exception
     */
    public function getLazy($name) {
        if (!isset($this->services[$name])) {
            throw new Exception("Servizio '{$name}' non trovato.");
        }

        // If the service is a closure, resolve it
        if (is_callable($this->services[$name])) {
            $this->services[$name] = call_user_func($this->services[$name]);
        }

        return $this->services[$name];
    }
}
