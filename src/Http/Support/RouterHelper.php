<?php 

namespace App\HTTP\Support;

use App\HTTP\Router;

class RouterHelper
{
    protected Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Check if the current route is the same as the given pattern.
     *
     * @param string $pattern
     * @return bool
     */
    public function route_is(string $pattern): bool
    {
        $routes = $this->router->get_routes();
        $regex = '#^' . preg_quote($pattern, '#') . '$#';
        $regex = str_replace('\*', '.*', $regex);

        foreach ($this->router->get_routes() as $routes) {
            if (isset($routes['name']) && preg_match($regex, $routes['name'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the URI for a given route name.
     *
     * @param string $route_name
     * @return string|null
     */
    public function get_route_uri(string $route_name): ?string
    {
        foreach ($this->router->get_routes() as $route => $config) {
            if ($config['name'] === $route_name) {
                return $route;
            }
        }
        return null;
    }

    /**
     * Get the URI for a given action.
     *
     * @param string $action
     * @return string|null
     */
    public function get_action_uri(string $action): ?string
    {
        foreach ($this->router->get_routes() as $route => $config) {
            if ($config['controller'] . '@' . $config['method'] === $action) {
                return $route;
            }
        }
        return null;
    }

    /**
     * Get the current route name.
     *
     * @return string|null
     */
    public function get_curr_route_name(): ?string
    {
        $uri = request()->header("REQUEST_URI") ?? '/';
        $currentRoute = $this->router->find_route($uri);
        return $currentRoute['name'] ?? null;
    }
}
