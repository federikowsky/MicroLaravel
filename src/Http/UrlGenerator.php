<?php

namespace App\HTTP;

class UrlGenerator
{
    /**
     * Crea un URL a partire da un percorso relativo.
     *
     * @param string $path
     * @param bool|null $secure
     * @return string
     */
    public function to($path, $secure = null)
    {
        $scheme = $secure ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        return $scheme . $host . '/' . ltrim($path, '/');
    }

    /**
     * Genera un URL per una rotta specifica.
     *
     * @param string $route
     * @param array $parameters
     * @return string
     */
    public function route($route, $parameters = [])
    {
        // Supponiamo che ci sia una mappa delle rotte per trovare i parametri dinamici
        $url = $this->to($route);

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
    public function action($action, $parameters = [])
    {
        // Esempio: HomeController@index
        list($controller, $method) = explode('@', $action);

        $url = $this->to("/{$controller}/{$method}");

        // Aggiungi i parametri query se ce ne sono
        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        return $url;
    }
}
