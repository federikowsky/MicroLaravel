<?php

namespace App\HTTP;

use App\HTTP\UrlGenerator;

class Redirect
{
    protected $urlGenerator;
    protected $url;
    protected $status;
    protected $headers;

    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function make($path, $status, $headers)
    {
        $this->url = $path;
        $this->status = $status;
        $this->headers = $headers;
        return $this;
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
     * Usage: redirect()->to('/home');
     * Usage: redirect()->to('/home', 301);
     * Usage: redirect()->to('/home', 301, ['X-Header' => 'Value']);
     * Usage: redirect()->to('/home')->with('key', 'value');
     * Usage: redirect()->to('/home')->with(['key' => 'value']);
     * Usage: redirect()->to('/home')->with_input(['key' => 'value']);  
     */
    public function to($path, $status, $headers, $secure)
    {
        $this->url = $this->urlGenerator->to($path, $secure);
        $this->status = $status;
        $this->headers = array_merge($this->headers, $headers);
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
     * Usage: redirect()->route('home');
     * Usage: redirect()->route('post.show', ['id' => 1]);
     * Usage: redirect()->route('post.show', ['id' => 1], 301);
     * Usage: redirect()->route('post.show', ['id' => 1], 301, ['X-Header' => 'Value']);
     * Usage: redirect()->route('post.show', ['id' => 1])->with('key', 'value');
     * Usage: redirect()->route('post.show', ['id' => 1])->with(['key' => 'value']);
     * Usage: redirect()->route('post.show', ['id' => 1])->with_input(['key' => 'value']);  
     */
    public function route($route, $parameters = [], $status = 302, $headers = [])
    {
        $this->url = $this->urlGenerator->route($route, $parameters);
        $this->status = $status;
        $this->headers = array_merge($this->headers, $headers);
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
     * Usage: redirect()->action('HomeController@index');
     * Usage: redirect()->action('PostController@show', ['id' => 1]);
     * Usage: redirect()->action('PostController@show', ['id' => 1], 301);
     * Usage: redirect()->action('PostController@show', ['id' => 1], 301, ['X-Header' => 'Value']);
     * Usage: redirect()->action('PostController@show', ['id' => 1])->with('key', 'value');
     * Usage: redirect()->action('PostController@show', ['id' => 1])->with(['key' => 'value']);
     * Usage: redirect()->action('PostController@show', ['id' => 1])->with_input(['key' => 'value']);
     * 
     */
    public function action($action, $parameters = [], $status = 302, $headers = [])
    {
        $this->url = $this->urlGenerator->action($action, $parameters);
        $this->status = $status;
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Set custom headers for the redirect response.
     * 
     * @param array $headers
     * @return $this
     * 
     * Usage: redirect()->to('/home')->withHeaders(['X-Header' => 'Value', 'Y-Header' => 'Value']);
     * Usage: redirect()->route('home')->withHeaders(['X-Header' => 'Value', 'Y-Header' => 'Value']);
     * Usage: redirect()->action('HomeController@index')->withHeaders(['X-Header' => 'Value', 'Y-Header' => 'Value']);
     *  
     */
    public function with_headers(array $headers = [])
    {
        $this->headers = array_merge($this->headers, $headers);
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
     * Usage: redirect()->back();
     * Usage: redirect()->back(301);
     * Usage: redirect()->back(301, ['X-Header' => 'Value']);
     * Usage: redirect()->back(301, ['X-Header' => 'Value'], '/fallback');
     * Usage: redirect()->back(301, ['X-Header' => 'Value'], '/fallback')->with('key', 'value');
     * Usage: redirect()->back(301, ['X-Header' => 'Value'], '/fallback')->with(['key' => 'value']);
     * Usage: redirect()->back(301, ['X-Header' => 'Value'], '/fallback')->with_input(['key' => 'value']);
     * 
     */
    public function back($status = 302, $headers = [], $fallback = '/')
    {
        $this->url = $_SERVER['HTTP_REFERER'] ?? $this->urlGenerator->to($fallback);
        $this->status = $status;
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Redirect the user with a specific flash message.
     * 
     * @param string $message
     * @param string $type
     * @return $this
     * 
     * Usage: redirect()->to('/home')->with_message('Welcome back!', 'success');
     * Usage: redirect()->route('home')->with_message('Welcome back!', 'success');
     * Usage: redirect()->action('HomeController@index')->with_message('Welcome back!', 'success');
     * 
     */
    public function with_message($message, $type = FLASH_SUCCESS)
    {
        flash('flash_' . uniqid(), $message, $type);
        return $this;
    }

    /**
     * Redirect the user with specific data in the session (input data).
     * 
     * @param array $input
     * @return $this
     * 
     * Usage: redirect()->to('/home')->with_input(['username' => 'John']);
     * Usage: redirect()->route('home')->with_input(['username' => 'John']);
     * Usage: redirect()->action('HomeController@index')->with_input(['username' => 'John']);
     * 
     */
    public function with_input(array $input)
    {
        foreach($input as $key => $value) {
            $_SESSION['inputs'][$key] = $value;
        }
        return $this;
    }

    /**
     * Send the response when invoked.
     * NOTE: This method should not be called. It is called automatically by the framework.
     * 
     * 
     */
    public function send()
    {
        response('', $this->status, $this->headers)
            ->header('Location', $this->url)
            ->send();
    }
}
