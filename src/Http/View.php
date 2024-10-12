<?php

namespace App\HTTP;

use App\Core\Flash;

use App\Http\BaseResponse;
use App\Http\ResponseData;

class View extends BaseResponse
{
    protected array $data = [];
    protected array $flash_message = [];
    protected string $view;

    public function __construct(ResponseData $response_data)
    {
        parent::__construct($response_data);
    }

    public function make(string $view): View
    {
        if (!$view) {
            throw new \Exception('View must be set.');
        }

        $this->view = $view;
        return $this;
    }

    public function with($key, $value = null): View
    {
        if (!$key || array_is_list($key) || (is_string($key) && is_null($value))) {
            throw new \InvalidArgumentException('key must be a string or an associative array');
        }

        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function with_message(string $message, string $type = Flash::FLASH_SUCCESS): View
    {
        if (!$message) {
            throw new \Exception('Message must be set.');
        }

        $this->flash_message = ['message' => $message, 'type' => $type];
        return $this;
    }

    /**
     * Render the view
     * 
     * @return string
     */
    public function render(): string
    {
        if (!$this->view) {
            throw new \Exception('View not set.');
        }
        
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
    public function send(): void
    {
        $content = $this->render();
        $this->response_data->set_content($content);
        
        parent::send();
    }
}
