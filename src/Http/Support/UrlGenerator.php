<?php

namespace App\HTTP\Support;

use App\Facades\RouterHelper;

class UrlGenerator
{
    /**
     * Make a URL from relative path
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
     * Make a URL from route name
     *
     * @param string $route
     * @param array $parameters
     * @return string
     */
    public static function route(string $route, array $parameters = []): string
    {
        $uri = RouterHelper::get_route_uri($route);

        if (!$uri) {
            throw new \Exception("Route '{$route}' not found.");
        }

        // Trova i placeholder come {slug} o {id} nella rotta
        $pattern = '/\{([^\}]+)\}/';

        // Rimuovi i parametri utilizzati per i placeholder dalla lista dei parametri
        $uri = preg_replace_callback($pattern, function ($matches) use (&$parameters) {
            $paramName = $matches[1];

            // Se il parametro esiste tra quelli forniti, usalo per sostituire il placeholder
            if (isset($parameters[$paramName])) {
                $value = $parameters[$paramName];
                unset($parameters[$paramName]);
                return $value;
            }

            // Se non esiste, lancia un'eccezione
            throw new \Exception("Missing required parameter: '{$paramName}' for route.");
        }, $uri);

        // Aggiungi i parametri rimanenti come query string
        if (!empty($parameters)) {
            $uri .= '?' . http_build_query($parameters);
        }

        return self::to($uri);
    }


    /**
     * Make a URL from action
     *
     * @param string $action
     * @param array $parameters
     * @return string
     */
    public static function action(string $action, array $parameters = []): string
    {
        // e.g., HomeController@index
        $uri = RouterHelper::get_action_uri($action);

        if (!$uri) {
            throw new \Exception("Action '{$action}' not found.");
        }

        // Replace placeholders with parameters if needed
        $pattern = '/\{[^\}]+\}/';
        $uri = preg_replace_callback($pattern, function ($matches) use (&$parameters) {
            return array_shift($parameters) ?? $matches[0];
        }, $uri);

        // If there are remaining parameters, append them as query string
        if (!empty($parameters)) {
            $uri .= '?' . http_build_query($parameters);
        }

        return self::to($uri);
    }
}
