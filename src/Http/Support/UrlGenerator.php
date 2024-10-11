<?php

namespace App\HTTP\Support;

use App\Core\ServiceContainer;
use App\HTTP\Router;

class UrlGenerator
{
    /**
     * Crea un URL a partire da un percorso relativo.
     *
     * @param string $path
     * @param bool|null $secure
     * @return string
     */
    public static function to($path, $secure = null)
    {
        $scheme = $secure ? 'https://' : 'http://';
        $host = request()->host();
        return $scheme . $host . '/' . ltrim($path, '/');
    }

    /**
     * Genera un URL per una rotta specifica.
     *
     * @param string $route
     * @param array $parameters
     * @return string
     */
    public static function route($route, $parameters = [])
    {
        $router = ServiceContainer::get_container()->get(Router::class);

        $url = self::to($router->get_uri($route));

        if (!empty($parameters)) {
            // Questo permette di gestire parametri come /user/{id}
            $url = preg_replace_callback('/\{[^\}]+\}/', function ($matches) use (&$parameters) {
                return array_shift($parameters);
            }, $url);

            // Aggiungi i parametri query se ce ne sono
            if (!empty($parameters)) {
                $url .= '?' . http_build_query($parameters);
            }
        }

        return $url;
    }

    /**
     * Genera un URL per un'azione del controller.
     *
     * @param string $action
     * @param array $parameters
     * @return string
     */
    public static function action($action, $parameters = [])
    {
        // Esempio: HomeController@index
        list($controller, $method) = explode('@', $action);

        $url = self::to("/{$controller}/{$method}");

        // Aggiungi i parametri query se ce ne sono
        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        return $url;
    }
}
