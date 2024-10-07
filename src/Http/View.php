<?php

namespace App\HTTP;

class View
{
    protected $data = [];
    protected $flash_message = [];
    protected $view;

    public function __construct()
    {
    }

    public function make($view, $data)
    {
        $this->view = $view;
        $this->data = $data;
        return $this;
    }

    public function with($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->data[$k] = $v;
            }
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function with_message($message, $type = FLASH_SUCCESS)
    {
        $this->flash_message = ['message' => $message, 'type' => $type];
        return $this;
    }

    /**
     * Render the view
     * 
     * @return string
     */
    public function render()
    {
        ob_start();
        extract($this->data);
        // se flash message is set, call the flash function and pass the message and type
        if (!empty($this->flash_message)) {
            flash('flash_' . uniqid(), $this->flash_message['message'], $this->flash_message['type']);
        }

        // get the css and js files associated with the view
        // as a result of the load method, we get an array with two elements: 
        // the first element is an array of css files, 
        // the second element is an array of js files
        [$css_files, $js_files] = assets($this->view);

        require_once __DIR__ . "/../Views/Inc/header.php";
        require_once __DIR__ . "/../Views/{$this->view}.php";
        require_once __DIR__ . "/../Views/Inc/footer.php";
        return ob_get_clean();
    }

    /**
     * Send the response to the browser
     * 
     * NOTE: This method should not be called. It is called automatically by the framework.
     */
    public function send()
    {
        $content = $this->render();
        response($content)
            ->send();
    }
}
