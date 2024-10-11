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
    public static function route($route, $parameters = [])
    {
        $url = self::to(RouterHelper::get_route_uri($route));

        if (!empty($parameters)) {
            // handle parameter like /user/{id}
            $url = preg_replace_callback('/\{[^\}]+\}/', function ($matches) use (&$parameters) {
                return array_shift($parameters);
            }, $url);

            // Add query parameters if any
            if (!empty($parameters)) {
                $url .= '?' . http_build_query($parameters);
            }
        }

        return $url;
    }

    /**
     * Make a URL from action
     *
     * @param string $action
     * @param array $parameters
     * @return string
     */
    public static function action($action, $parameters = [])
    {
        // e.g. HomeController@index
        $url = self::to(RouterHelper::get_action_uri($action));

        // Add query parameters if any
        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        return $url;
    }
}
