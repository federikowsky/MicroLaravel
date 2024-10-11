<?php

namespace App\HTTP;

use App\Core\ServiceContainer;

use App\Exceptions\HTTP\ {
    NotFoundException
};

class Router
{
    protected $routes = [];
    protected $container;
    protected $cacheFile = __DIR__ . '/../../storage/cache/routes_cache.php';

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Carica tutte le rotte dai file di configurazione modulari
     * @param array $routeFiles
     */
    public function load_routes(array $routeFiles): void
    {
        // Controlla se esiste il file di cache
        if (file_exists($this->cacheFile)) {
            $this->routes = require $this->cacheFile;
        } else {
            // Carica le rotte dai file modulari
            foreach ($routeFiles as $file) {
                if (file_exists($file)) {
                    $routeConfig = require $file;
                    $this->add_routes($routeConfig['routes'], $routeConfig['middleware'] ?? []);
                } else {
                    throw new \InvalidArgumentException("Route file '$file' not found.");
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
    protected function add_routes(array $routes, array $groupMiddleware): void
    {
        foreach ($routes as $route => $config) {
            $this->routes[$route] = [
                'controller' => $config['controller'],
                'method' => $config['method'],
                'name' => $config['name'] ?? null,
                'middleware' => array_unique(array_merge($groupMiddleware, $config['middleware'] ?? []))
            ];
        }
    }

    public function get_routes(?string $route = null): ?array
    {
        if ($route) {
            return $this->routes[$route] ?? null;
        }
        return $this->routes;
    }

    /**
     * Gestisce la richiesta e chiama il metodo del controller appropriato
     * @param string $uri
     * @throws NotFoundException se la rotta non viene trovata
     */
    public function route(string $uri): void
    {
        $routeConfig = $this->find_route($uri);

        if ($routeConfig) {
            $controllerName = $routeConfig['controller'];
            $method = $routeConfig['method'];
            $params = $routeConfig['params'] ?? []; // Parametri dinamici trovati
            $middlewareStack = $routeConfig['middleware'];
            

            // Esegui middleware e metodo del controller
            $response = $this->execute_middleware_stack($middlewareStack, function() use ($controllerName, $method, $params) {
                return $this->execute_controller($controllerName, $method, $params);
            });
            $response->send();

        } else {
            throw new NotFoundException("Route '$uri' not found.");
        }
    }

    /**
     * Trova la rotta, anche se contiene parametri dinamici come /user/{id}
     * @param string $uri
     * @return array|null
     */
    public function find_route(string $uri)
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
    protected function execute_middleware_stack(array $middlewareStack, callable $next)
    {
        if (empty($middlewareStack)) {
            return $next();
        }

        $middlewareName = array_shift($middlewareStack);

        // Assumi che i middleware siano nel namespace App\Middlewares
        $middlewareClass = "App\\Middlewares\\{$middlewareName}";


        if (class_exists($middlewareClass)) {

            $middleware = $this->container->getLazy($middlewareClass);

            if (method_exists($middleware, 'handle')) {
                return $middleware->handle(function() use ($middlewareStack, $next) {
                   return $this->execute_middleware_stack($middlewareStack, $next);
                });
            } else {
                throw new \Exception("Method 'handle' not found in middleware class '$middlewareClass'.");
            }

        } else {
            throw new \Exception("Middleware class '$middlewareClass' not found.");
        }
    }

    /**
     * Esegui il controller e il metodo
     * @param string $controllerName
     * @param string $method
     * @param array $params
     */
    protected function execute_controller(string $controllerName, string $method, array $params = [])
    {
        // Assumi che i controller siano nel namespace App\Controllers
        $controllerClass = "App\\Controllers\\{$controllerName}";

        if (class_exists($controllerClass)) {
            $controller = $this->container->getLazy($controllerClass);

            if (method_exists($controller, $method)) {
                // Passa i parametri dinamici al metodo del controller
                return call_user_func_array([$controller, $method], $params);
            } else {
                throw new NotFoundException("Method '$method' not found in controller '$controllerClass'.");
            }
        } else {
            throw new NotFoundException("Controller '$controllerClass' not found.");
        }
    }
}