<?php

require_once __DIR__ . '/Exceptions/NotFoundException.php';

class Router
{
    protected $routes = [];
    protected $container;
    protected $cacheFile = __DIR__ . '/../cache/routes_cache.php';

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Carica tutte le rotte dai file di configurazione modulari
     * @param array $routeFiles
     */
    public function loadRoutes(array $routeFiles)
    {
        // Controlla se esiste il file di cache
        if (file_exists($this->cacheFile)) {
            $this->routes = require $this->cacheFile;
        } else {
            // Carica le rotte dai file modulari
            foreach ($routeFiles as $file) {
                if (file_exists($file)) {
                    $routeConfig = require $file;
                    $this->addRoutes($routeConfig['routes'], $routeConfig['middleware'] ?? []);
                }
            }

            // Salva le rotte in cache
            file_put_contents($this->cacheFile, '<?php return ' . var_export($this->routes, true) . ';');
        }
    }

    /**
     * Aggiunge le rotte e il middleware associato alla configurazione interna
     * @param array $routes
     * @param array $middleware
     */
    protected function addRoutes(array $routes, array $groupMiddleware)
    {
        foreach ($routes as $route => $config) {
            $this->routes[$route] = [
                'controller' => $config['controller'],
                'method' => $config['method'],
                'middleware' => array_merge($groupMiddleware, $config['middleware'] ?? [])
            ];
        }
    }

    /**
     * Gestisce la richiesta e chiama il metodo del controller appropriato
     * @param string $uri
     * @throws NotFoundException se la rotta non viene trovata
     */
    public function route(string $uri): void
    {
        $routeConfig = $this->findRoute($uri);

        if ($routeConfig) {
            $controllerName = $routeConfig['controller'];
            $method = $routeConfig['method'];
            $middlewareStack = $routeConfig['middleware'];
            $params = $routeConfig['params'] ?? []; // Parametri dinamici trovati

            // Esegui middleware e metodo del controller
            $this->executeMiddlewareStack($middlewareStack, function() use ($controllerName, $method, $params) {
                $this->executeController($controllerName, $method, $params);
            });
        } else {
            throw new NotFoundException("Route '$uri' not found.");
        }
    }

    /**
     * Trova la rotta, anche se contiene parametri dinamici come /user/{id}
     * @param string $uri
     * @return array|null
     */
    protected function findRoute(string $uri)
    {
        foreach ($this->routes as $route => $config) {
            // Usa una regex per sostituire i parametri dinamici come {id}
            $pattern = preg_replace('/\{[^\}]+\}/', '([a-zA-Z0-9_-]+)', $route);
            if (preg_match("#^$pattern$#", $uri, $matches)) {
                array_shift($matches); // Rimuove il match completo
                $config['params'] = $matches; // Aggiunge i parametri dinamici trovati
                return $config;
            }
        }
        return null;
    }

    /**
     * Esegui la pila di middleware
     * @param array $middlewareStack
     * @param callable $next
     */
    protected function executeMiddlewareStack(array $middlewareStack, callable $next): void
    {
        if (empty($middlewareStack)) {
            $next();
            return;
        }

        $middlewareClass = array_shift($middlewareStack);

        if (class_exists($middlewareClass)) {
            $middleware = new $middlewareClass($this->container);
            if (method_exists($middleware, 'handle')) {
                $middleware->handle(function() use ($middlewareStack, $next) {
                    $this->executeMiddlewareStack($middlewareStack, $next);
                });
            } else {
                throw new Exception("Method 'handle' not found in middleware class '$middlewareClass'.");
            }
        } else {
            throw new Exception("Middleware class '$middlewareClass' not found.");
        }
    }

    /**
     * Esegui il controller e il metodo
     * @param string $controllerName
     * @param string $method
     * @param array $params
     */
    protected function executeController(string $controllerName, string $method, array $params = []): void
    {
        $controllerPath = "../src/Controllers/{$controllerName}.php";

        if (file_exists($controllerPath)) {
            require_once $controllerPath;

            if (class_exists($controllerName)) {
                $controller = new $controllerName($this->container);

                if (method_exists($controller, $method)) {
                    // Passa i parametri dinamici al metodo del controller
                    call_user_func_array([$controller, $method], $params);
                } else {
                    throw new NotFoundException("Method '$method' not found in controller '$controllerName'.");
                }
            } else {
                throw new NotFoundException("Controller '$controllerName' not found.");
            }
        } else {
            throw new NotFoundException("Controller file '$controllerPath' not found.");
        }
    }
}