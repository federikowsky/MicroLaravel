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
        // Substitute * with .* and . with \.
        $regex = str_replace('.', '\.', $pattern);
        $regex = str_replace('*', '.*', $regex);
        $regex = '#^' . $regex . '$#';

        // Compila il pattern per i caratteri tra parentesi quadre (es: [A-Za-z0-9_-])
        // e gestisci correttamente i caratteri speciali come @, !, #.
        $regex = preg_replace_callback('/\[(.*?)\]/', function ($matches) {
            // Mantieni il contenuto della parentesi quadra come parte della regex
            return '[' . $matches[1] . ']';
        }, $regex);
    
        // Scorri le route e verifica se c'è una corrispondenza con il pattern regex generato
        foreach ($this->router->get_routes() as $route) {
            // Verifica se la rotta ha un nome e se corrisponde al pattern
            if (isset($route['name']) && preg_match($regex, $route['name'])) {
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
        $uri = request()->server("REQUEST_URI") ?? '/';
        $currentRoute = $this->router->find_route($uri);
        return $currentRoute['name'] ?? null;
    }
}
