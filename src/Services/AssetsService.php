<?php

namespace App\Services;

class AssetsService
{
    protected $css = [];
    protected $js = [];

    public function __construct()
    {
    }

    public function add_css($css)
    {
        $this->css[] = $css;
    }

    public function add_js($js)
    {
        $this->js[] = $js;
    }

    public function get_css()
    {
        return $this->css;
    }

    public function get_js()
    {
        return $this->js;
    }

    public function load($view)
    {
        // cerca i file css e js associati alla view
        $viewName = strtolower($view);

        // Cerca il file CSS specifico
        $cssPath = "/css/{$viewName}.css";
        if (file_exists(__DIR__ . '/../../public' . $cssPath)) {
            $this->add_css($cssPath);
        }

        // Cerca il file JS specifico
        $jsPath = "/js/{$viewName}.js";
        if (file_exists(__DIR__ . '/../../public' . $jsPath)) {
            $this->add_js($jsPath);
        }

        return [$this->css, $this->js];
    }
}