<?php

namespace App\HTTP;

use App\HTTP\ {
    Cookie,
    BaseResponse
};
use Throwable;

class Response extends BaseResponse
{
    protected $exception;
    protected $callback;
    
    public function __construct(ResponseData $response_data)
    {
        parent::__construct($response_data);
    }

    // Factory method (inizializza i valori di response)
    public function make(string $content, int $status = 200 , array $headers = []): Response
    {
        $this->set_content($content)
            ->set_status($status)
            ->with_headers($headers);
        
        return $this;
    }

    /************************ SETTER ************************/
    public function set_content(string $content): Response
    {
        $this->response_data->set_content($content);
        return $this;
    }

    public function set_status(int $status): Response
    {
        $this->response_data->set_status($status);
        return $this;
    }

    /************************ GETTER ************************/
    /**
     * Get the content of the response.
     *
     * @return string
     */
    public function get_content(): string
    {
        return $this->response_data->get_content();
    }

    /**
     * Get the status code of the response.
     *
     * @return int
     */
    public function get_status(): int
    {
        return $this->response_data->get_status();
    }

    /**
     * Get the headers to be sent with the response.
     * If a header name is provided, return the header value.
     * Otherwise, return an array of all headers.
     *
     * @param string|null $param
     * @return array|string|null
     */
    public function get_headers(string $param = null): array|string|null
    {
        return $this->response_data->get_headers($param);
    }

    /**
     * Get the cookies to be sent with the response.
     * If a cookie name is provided, return the cookie object.
     * Otherwise, return an array of all cookies.
     *
     * @param string|null $name
     * @return array|Cookie|null
     */
    public function get_cookies(string $name = null): array|Cookie|null
    {
        return $this->response_data->get_cookies($name);
    }

    /**
    * Get the exception that occurred during the request.
    *
    * @return Throwable
    */
    public function get_exception(): Throwable
    {
        return $this->exception;
    }

    /**
     * Get the callback function to be executed for streaming responses.
     * 
     * @return callable
     */
    public function get_callback(): callable
    {
        return $this->callback;
    }

    /**
     * Set the headers to be sent with the response.
     * 
     * @param string $key
     * @param mixed $value
     * @param bool $replace
     * @return Response
     */
    public function header(string $key, string $value, bool $replace = true): Response
    {
        $this->response_data->add_header($key, $value, $replace);
        return $this;
    }

    /**
     * Set multiple headers at once.
     * 
     * @param array $headers
     * @return Response
     */
    public function with_headers(array $headers): Response
    {
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }
        return $this;
    }

    /**
     * Set a cookie to be sent with the response.
     * 
     * @param string $name
     * @param string $value
     * @param int $minutes
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return Response
     */
    public function cookie(string $name, string $value, int $minutes = 60, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): Response
    {
        $this->response_data->add_cookie(
            $name, 
            $value, 
            $minutes, 
            $path, 
            $domain, 
            $secure, 
            $httpOnly
        );
        return $this;
    }

    /**
     * Remove a cookie from the response.
     * 
     * @param string $name
     * @return Response
     */
    public function without_cookie(string $name): Response
    {
        $this->response_data->remove_cookie($name);
        return $this;
    }

    /**
     * Set the exception that occurred during the request.
     * 
     * @param Throwable $e
     * @return Response
     */
    public function with_exception(Throwable $e): Response
    {
        $this->exception = $e;
        return $this;
    }

    /**
     * Set the response to be json type 
     * 
     * @param mixed $data
     * @param mixed $status
     * @param array $headers
     * @param mixed $flags
     * @return Response
     */
    public function json(array $data = [], int $status = 200, array $headers = [], int $flags = 0): Response
    {
        $this->make(json_encode($data, $flags), $status, $headers)
            ->header('Content-Type', 'application/json');

        return $this;
    }

    /**
     * Set the response to be jsonp type.
     * 
     * @param string $callback
     * @param mixed $data
     * @param mixed $status
     * @param array $headers
     * @param mixed $flags
     * @return Response
     */
    public function jsonp(string $callback, array $data = [], int $status = 200, array $headers = [], int $flags = 0): Response
    {
        $jsonData = json_encode($data, $flags);
        $this->make("{$callback}({$jsonData})", $status, $headers)
            ->header('Content-Type', 'application/javascript');
        
        return $this;
    }

    /**
     * Set the response to be a stream.
     * 
     * @param callable $callback
     * @param mixed $status
     * @param array $headers
     * @return Response
     */
    public function stream(callable $callback, int $status = 200, array $headers = []): Response
    {
        $this->make('', $status, $headers)
            ->callback = $callback;

        return $this;
    }

    /**
     * Set the response to be a file download.
     * 
     * @param string $file
     * @param string|null $name
     * @param array $headers
     * @param string $disposition
     * @return Response
     */
    public function download(string $file, string $name = null, array $headers = [], string $disposition = 'attachment'): Response
    {
        $this->make('', 200, $headers)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', "{$disposition}; filename={$name}")
            ->header('Content-Length', filesize($file));

        ob_clean();
        flush();
        readfile($file);

        return $this;
    }

    /**
     * Set the response with no content
     * 
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public function no_content(int $status = 204, array $headers = []): Response
    {
        $this->make('', $status, $headers);
        return $this;
    }

    /**
     * Set the response to redirect to a different URL.
     * 
     * @return void
     */
    public function send(): void
    {
        if ($this->exception) {
            throw $this->exception;
        }

        if ($this->callback) {
            call_user_func($this->callback);
        } 

        parent::send();
    }
}
