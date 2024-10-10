<?php

namespace App\HTTP;

use App\HTTP\Cookie;

class ResponseData
{
    protected string $content = '';
    protected int $status = 200;
    protected array $headers = [];
    protected array $cookies = [];

    public function set_content(string $content): void
    {
        $this->content = $content;
    }
    
    public function set_status(int $status): void
    {
        if ($status < 100 || $status > 599) {
            throw new \InvalidArgumentException('Invalid HTTP status code');
        }
        $this->status = $status;
    }

    public function add_header(string $key, $values, bool $replace = true): void
    {
        if ($replace || !isset($this->headers[$key])) {
            $this->headers[$key] = $values;
        } else {
            $this->headers[$key] = array_merge((array)$this->headers[$key], (array)$values);
        }
    }

    public function add_cookie(string $name, string $value, int $minutes = 60, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): void
    {
        if (isset($this->cookies[$name])) {
            $cookie = $this->cookies[$name];
            
            $cookie->value = $value;
            $cookie->expire = time() + $minutes * 60;
            $cookie->path = $cookie->path != $path ? $cookie->path : $path;
            $cookie->domain = $cookie->domain != $domain ? $cookie->domain : $domain;
            $cookie->secure = $cookie->secure != $secure ? $cookie->secure : $secure;
            $cookie->httpOnly = $cookie->httpOnly != $httpOnly ? $cookie->httpOnly : $httpOnly;

            return;
        }

        $expire = time() + $minutes * 60;

        $this->cookies[$name] = new Cookie(
            $name,
            $value,
            $expire,
            $path,
            $domain,
            $secure,
            $httpOnly
        );
    }

    public function remove_cookie(string $name): void
    {
        if (isset($this->cookies[$name])) {
            $this->cookies[$name]->expire = time() - 3600;
            $this->cookies[$name]->value = '';
        }
    }

    public function get_content(): string
    {
        return $this->content;
    }


    public function get_status(): int
    {
        return $this->status;
    }


    public function get_headers(string $param = null): array|string|null
    {
        if ($param) {
            return $this->headers[$param] ?? null;
        }
        
        return $this->headers;
    }

    public function get_cookies(string $name = null): array|Cookie|null
    {
        if ($name) {
            return $this->cookies[$name] ?? null;
        }
        return $this->cookies;
    }
}
