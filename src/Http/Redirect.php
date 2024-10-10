<?php

namespace App\HTTP;

use App\Core\Flash;
use App\HTTP\BaseResponse;
use App\HTTP\Support\UrlGenerator;

class Redirect extends BaseResponse
{
    protected $url;

    public function __construct(ResponseData $response_data)
    {
        parent::__construct($response_data);
    }

    public function make(string $path, int $status = 302 , array $headers = []): Redirect
    {
        if (!$path) {
            throw new \Exception('Path must be set.');
        }

        $this->url = $path;
        $this->response_data->set_status($status);
        $this->with_headers($headers);
        return $this;
    }

    public function get_url(): string
    {
        return $this->url;
    }

    public function get_status(): int
    {
        return $this->response_data->get_status();
    }

    public function get_headers(): array|string|null
    {
        return $this->response_data->get_headers();
    }
    

    /**
     * Redirect to a specific path.
     * 
     * @param string $path
     * @param int $status
     * @param array $headers
     * @param bool|null $secure
     * @return $this
     * 
     * @use redirect()->to('/home');
     * @use redirect()->to('/home', 301);
     * @use redirect()->to('/home', 301, ['X-Header' => 'Value']);
     * @use redirect()->to('/home')->with_header('key', 'value');
     * @use redirect()->to('/home')->with_input(['key' => 'value']);  
     */
    public function to(string $path, int $status = 302, array $headers = [], bool $secure = false): Redirect
    {
        if (!$path) {
            throw new \Exception('Path must be set.');
        }

        $this->url = UrlGenerator::to($path, $secure);
        $this->response_data->set_status($status);
        $this->with_headers($headers);
        return $this;
    }

    /**
     * Redirect to a named route.
     * 
     * @param string $route
     * @param array $parameters
     * @param int $status
     * @param array $headers
     * @return $this
     * 
     * @use redirect()->route('home');
     * @use redirect()->route('post.show', ['id' => 1]);
     * @use redirect()->route('post.show', ['id' => 1], 301);
     * @use redirect()->route('post.show', ['id' => 1], 301, ['X-Header' => 'Value']);
     * @use redirect()->route('post.show', ['id' => 1])->with_header('key', 'value');
     * @use redirect()->route('post.show', ['id' => 1])->with_input(['key' => 'value']);  
     */
    public function route(string $route, array $parameters = [], int $status = 302, array $headers = []): Redirect
    {
        if (!$route) {
            throw new \Exception('Route must be set.');
        }

        $this->url = UrlGenerator::route($route, $parameters);
        $this->response_data->set_status($status);
        $this->with_headers($headers);
        return $this;
    }

    /**
     * Redirect to a controller action.
     * 
     * @param string $action
     * @param array $parameters
     * @param int $status
     * @param array $headers
     * @return $this
     * 
     * @use redirect()->action('HomeController@index');
     * @use redirect()->action('PostController@show', ['id' => 1]);
     * @use redirect()->action('PostController@show', ['id' => 1], 301);
     * @use redirect()->action('PostController@show', ['id' => 1], 301, ['X-Header' => 'Value']);
     * @use redirect()->action('PostController@show', ['id' => 1])->with_header('key', 'value');
     * @use redirect()->action('PostController@show', ['id' => 1])->with_input(['key' => 'value']);
     * 
     */
    public function action(string $action, array $parameters = [], int $status = 302, array $headers = []): Redirect
    {
        if (!$action) {
            throw new \Exception('Action must be set.');
        }

        $this->url = UrlGenerator::action($action, $parameters);
        $this->response_data->set_status($status);
        $this->with_headers($headers);
        return $this;
    }

    protected function with_headers(array $headers = []): Redirect
    {
        foreach($headers as $key => $value) {
            $this->response_data->add_header($key, $value);
        }
        return $this;
    }

    /**
     * Set a custom header for the redirect response.
     * 
     * @param string $key
     * @param string $value
     * @return $this
     * 
     * @use redirect()->to('/home')->with_header('key', 'value');
     * @use redirect()->route('home')->with_header('key', 'value');
     * @use redirect()->action('HomeController@index')->with_header('key', 'value');
     * 
     */
    public function with_header(string $key, string $value): Redirect
    {
        $this->response_data->add_header($key, $value);
        return $this;
    }

    /**
     * Redirect to the previous URL.
     * 
     * @param int $status
     * @param array $headers
     * @param string $fallback
     * @return $this
     * 
     * @use redirect()->back();
     * @use redirect()->back(301);
     * @use redirect()->back(301, ['X-Header' => 'Value']);
     * @use redirect()->back(301, ['X-Header' => 'Value'], '/fallback');
     * @use redirect()->back(301, ['X-Header' => 'Value'], '/fallback')->with_header('key', 'value');
     * @use redirect()->back(301, ['X-Header' => 'Value'], '/fallback')->with_input(['key' => 'value']);
     * 
     */
    public function back(int $status = 302, array $headers = [], string $fallback = '/'): Redirect
    {
        $this->url = request()->server('HTTP_REFERER') ?? UrlGenerator::to($fallback);
        $this->response_data->set_status($status);
        $this->with_headers($headers);
        return $this;
    }

    /**
     * Redirect the user with a specific flash message.
     * 
     * @param string $message
     * @param string $type
     * @return $this
     * 
     * @use redirect()->to('/home')->with_message('Welcome back!', 'success');
     * @use redirect()->route('home')->with_message('Welcome back!', 'success');
     * @use redirect()->action('HomeController@index')->with_message('Welcome back!', 'success');
     * 
     */
    public function with_message(string $message, string $type = Flash::FLASH_SUCCESS): Redirect
    {
        if (!$message) {
            throw new \Exception('Message must be set.');
        }

        flash('flash_' . uniqid(), $message, $type);
        return $this;
    }

    /**
     * Redirect the user with specific data in the session (input data).
     * 
     * @param array $input
     * @return $this
     * 
     * @use redirect()->to('/home')->with_input(['username' => 'John']);
     * @use redirect()->route('home')->with_input(['username' => 'John']);
     * @use redirect()->action('HomeController@index')->with_input(['username' => 'John']);
     * 
     */
    public function with_input(array $input): Redirect
    {
        if (!$input) {
            throw new \Exception('Input data must be set.');
        }

        foreach($input as $key => $value) {
            session()->set('inputs', [
                $key => $value
            ]);
        }
        return $this;
    }

    /**
     * Send the response when invoked.
     * NOTE: This method should not be called. It is called automatically by the framework.
     * 
     * 
     */
    public function send(): void
    {
        $this->response_data->add_header('Location', $this->url);
        
        parent::send();
    }
}
