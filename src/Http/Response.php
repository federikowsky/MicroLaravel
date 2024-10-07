<?php

namespace App\HTTP;

use Throwable;

class Response
{
    protected $content;
    protected $status;
    protected $headers = [];
    protected $cookies = [];
    protected $exception;
    protected $callback;

    public function __construct()
    {
    }

    // Factory method (inizializza i valori di response)
    public function make($content, $status, array $headers)
    {
        $this->set_content($content);
        $this->status = $status;
        $this->headers = $headers;
        return $this;
    }

    // Setter per il contenuto
    public function set_content($content)
    {
        $this->content = $content;
        return $this;
    }

    public function content()
    {
        return $this->content;
    }

    public function status()
    {
        return $this->status;
    }

    public function set_status($status)
    {
        $this->status = $status;
        return $this;
    }

    public function header(string $key, $values, bool $replace = true)
    {
        if ($replace || !isset($this->headers[$key])) {
            $this->headers[$key] = $values;
        } else {
            $this->headers[$key] = array_merge((array)$this->headers[$key], (array)$values);
        }
        return $this;
    }

    public function with_headers(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }
        return $this;
    }

    public function cookie($cookie)
    {
        $this->cookies[] = $cookie;
        return $this;
    }

    public function with_cookie($cookie)
    {
        return $this->cookie($cookie);
    }

    public function without_cookie($cookie, string $path = null, string $domain = null)
    {
        $this->cookies[] = ['cookie' => $cookie, 'expire' => true, 'path' => $path, 'domain' => $domain];
        return $this;
    }

    public function with_exception(Throwable $e)
    {
        $this->exception = $e;
        return $this;
    }

    // Funzioni per risposta JSON
    public function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        $this->make(json_encode($data, $options), $status, $headers);
        $this->header('Content-Type', 'application/json');
        return $this;
    }

    // Funzioni per risposta JSONP
    public function jsonp($callback, $data = [], $status = 200, array $headers = [], $options = 0)
    {
        $jsonData = json_encode($data, $options);
        $this->make("{$callback}({$jsonData})", $status, $headers);
        $this->header('Content-Type', 'application/javascript');
        return $this;
    }

    // Streaming
    public function stream($callback, $status = 200, array $headers = [])
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->callback = $callback;

        return $this;
    }

    // Download
    public function download($file, $name = null, array $headers = [], $disposition = 'attachment')
    {
        $this->status = 200;
        $this->headers = array_merge($headers, [
            'Content-Disposition' => "{$disposition}; filename={$name}",
            'Content-Length' => filesize($file)
        ]);

        ob_clean();
        flush();
        readfile($file);

        return $this;
    }

    public function no_content($status = 204, array $headers = [])
    {
        $this->make('', $status, $headers);
        return $this;
    }

    // Send the response
    public function send()
    {
        http_response_code($this->status);

        if ($this->exception) {
            $this->header('Content-Type', 'text/plain');
            echo $this->exception->getMessage();
            return;
        }

        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        foreach ($this->cookies as $cookie) {
            if (is_array($cookie) && isset($cookie['expire']) && $cookie['expire']) {
                setcookie($cookie['cookie'], '', time() - 3600, $cookie['path'] ?? '/', $cookie['domain'] ?? '');
            } else {
                setcookie($cookie);
            }
        }

        if ($this->callback) {
            call_user_func($this->callback);
        } else {
            echo $this->content;
        }
    }
}
