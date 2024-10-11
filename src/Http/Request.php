<?php

namespace App\HTTP;

use App\Core\ServiceContainer;
use App\Facades\RouterHelper;
use DateTime;
use DateTimeZone;

use App\Helpers\DotNotationManager;


class Request
{
    protected array $get;
    protected array $post;
    protected array $cookies;
    protected array $files;
    protected array $server;
    protected array $headers;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->headers = $this->parse_headers();
    }

    /**
     * Parse the headers from the $_SERVER superglobal.
     * @return array
     */
    protected function parse_headers(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[str_replace('HTTP_', '', $key)] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $headerKey = str_replace('_', '-', ucwords(strtolower($key)));
                $headers[$headerKey] = $value;
            }
        }
        return $headers;
    }

    /**
     * Get the request path.
     * @return string
     */
    public function path(): string
    {
        return trim(parse_url($this->server('REQUEST_URI'), PHP_URL_PATH), '/');
    }

    /**
     * Check if the request path matches a pattern.
     * @param string $pattern
     * @return bool
     */
    public function is(string $pattern): bool
    {
        return fnmatch($pattern, $this->path());
    }


    /**
     * Check if the request path matches a route pattern.
     * @param string $pattern
     * @return bool
     */
    public function route_is(string $pattern): bool
    {
        return RouterHelper::route_is($pattern);
    }


    /**
     * Check if the request is secure, i.e. using HTTPS.
     * @return bool
     */
    public function is_secure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * Get the full URL of the request.
     * @return string
     */
    public function url(): string
    {
        return $this->scheme_and_http_host() . $this->server('REQUEST_URI');
    }

    /**
     * Get the full URL of the request with additional query parameters.
     * @param array $query
     * @return string
     */
    public function full_url_with_query(array $query): string
    {
        $url = $this->url();
        $query_string = http_build_query(array_merge($this->get, $query));
        return $url . '?' . $query_string;
    }

    /**
     * Get the full URL of the request without specific query parameters.
     * @param array $keys
     * @return string
     */
    public function full_url_without_query(array $keys): string
    {
        $url = $this->url();
        $query = $this->get;
        foreach ($keys as $key) {
            unset($query[$key]);
        }
        $query_string = http_build_query($query);
        return $query_string ? $url . '?' . $query_string : $url;
    }

    /**
     * Get the host of the request.
     * @return string
     */
    public function host(): ?string
    {
        return $this->server('HTTP_HOST');
    }

    /**
     * Get the host and port of the request.
     * @return string
     */
    public function http_host(): string
    {
        return $this->host() . ':' . $this->server('SERVER_PORT');
    }

    /**
     * Get the scheme and host of the request.
     * @return string
     */
    public function scheme_and_http_host(): string
    {
        $scheme = $this->server('REQUEST_SCHEME') ?? ($this->is_secure() ? 'https' : 'http');
        return $scheme . '://' . $this->host();
    }

    /**
     * Get the request method.
     * @return string
     */
    public function method(): string
    {
        return strtoupper($this->server('REQUEST_METHOD'));
    }

    /**
     * Check if the request method is a specific method.
     * @param string $method
     * @return bool
     */
    public function is_method(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    /**
     * Get the request header.
     * @return string
     */
    public function header(string $key, $default = null): ?string
    {
        return $this->headers[strtoupper(str_replace('-', '_', $key))] ?? $default;
    }

    /**
     * Check if the request has a specific header.
     * @param string $key
     * @return bool
     */
    public function has_header(string $key): bool
    {
        return isset($this->headers[strtoupper(str_replace('-', '_', $key))]);
    }

    /**
     * Get the bearer token from the request.
     * @return string|null
     */
    public function bearer_token(): ?string
    {
        $authorization = $this->header('Authorization', '');
        return str_starts_with($authorization, 'Bearer ') ? substr($authorization, 7) : null;
    }

    /**
     * Get the client IP address.
     * @return string
     */
    public function ip(): string
    {
        return $this->server('REMOTE_ADDR');
    }

    /**
     * Get the client IP addresses
     * @return array
     */
    public function ips(): array
    {
        return array_map('trim', explode(',', $this->server('HTTP_X_FORWARDED_FOR') ?? $this->ip()));
    }

    /**
     * Check if the request accepts a specific content type.
     * @return bool
     */
    public function accepts(array $types): bool
    {
        $accept_header = $this->header('Accept', '');
        foreach ($types as $type) {
            if (str_contains($accept_header, $type)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the request expects a JSON response.
     * @return bool
     */
    public function expects_json(): bool
    {
        return $this->accepts(['application/json']);
    }

    /**
     * Get all the request data (GET and POST).
     * @return array 
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    /**
     * Get the request data (GET and POST) using dot notation.
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function input(?string $key = null, $default = null)
    {
        $data = $this->all();
        return $key ? DotNotationManager::get($data, $key, $default) : $data;
    }

    /**
     * Get the request data from the POST superglobal.
     * @param string|null $key
     * @param mixed $default
     * @return string|array
     */
    public function post(?string $key = null, $default = null):string|array
    {
        return $key ? DotNotationManager::get($this->post, $key, $default) : $this->post;
    }

    /**
     * Get the request data from the GET superglobal.
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function get(?string $key = null, $default = null)
    {
        return $key ? DotNotationManager::get($this->get, $key, $default) : $this->get;
    }

    /**
     * Get the request data from the query string.
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function query(?string $key = null, $default = null)
    {
        return $key ? DotNotationManager::get($this->get, $key, $default) : $this->get;
    }

    /**
     * Get the request data from the POST and GET superglobals as a string.
     * @param string $key
     * @return string
     */
    public function string(string $key): string
    {
        return trim($this->input($key, ''));
    }

    /**
     * Get the request data from the POST and GET superglobals as an integer.
     * @param string $key
     * @return int
     */
    public function integer(string $key): int
    {
        return (int) $this->input($key, 0);
    }

    /**
     * Get the request data from the POST and GET superglobals as a boolean.
     * @param string $key
     * @return bool
     */
    public function boolean(string $key): bool
    {
        return filter_var($this->input($key), FILTER_VALIDATE_BOOLEAN);
    }


    /**
     * Get the request data from the POST and GET superglobals as a date string.
     * @param string $key
     * @param string $format
     * @param string|null $timezone
     * @return string|null
     */
    public function date(string $key, string $format = 'Y-m-d', ?string $timezone = null): ?string
    {
        $value = $this->input($key);
        try {
            $date = new DateTime($value, new DateTimeZone($timezone ?: date_default_timezone_get()));
            return $date->format($format);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get only the specified keys from the request data.
     * @param array $keys
     * @return array
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    /**
     * Get all the request data except the specified keys.
     * @param array $keys
     * @return array
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    /**
     * Check if the request has a specific key.
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return !is_null($this->input($key));
    }

    /**
     * Check if the request has any of the specified keys.
     * @param array $keys
     * @return bool
     */
    public function has_any(array $keys): bool
    {
        foreach ($keys as $key) {
            if ($this->has($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the request has all of the specified keys, then call the callback.
     * @param array $keys
     */
    public function when_has(string $key, callable $callback)
    {
        if ($this->has($key)) {
            return $callback($this->input($key));
        }
    }

    /**
     * Check if the request has the specified keys and are not empty.
     * @param array $keys
     */
    public function filled(string $key): bool
    {
        return !empty($this->input($key));
    }

    /**
     * Check if the request has the specified keys and are empty.
     * @param array $keys
     */
    public function is_not_filled(string $key): bool
    {
        return !$this->filled($key);
    }

    /**
     * Check if the request has any of the specified keys and are empty.
     * @param array $keys
     */
    public function is_not_filled_any(array $keys): bool
    {
        foreach ($keys as $key) {
            if ($this->filled($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if the request has any of the specified keys and are not empty.
     * @param array $keys
     */
    public function any_filled(array $keys): bool
    {
        foreach ($keys as $key) {
            if ($this->filled($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * if the request has all of the specified keys and are not empty, then call the callback.
     * @param array $keys
     */
    public function when_filled(string $key, callable $callback)
    {
        if ($this->filled($key)) {
            return $callback($this->input($key));
        }
    }

    /**
     * Check if the request hasn't the specified keys.
     * @param array $keys
     */
    public function missing(string $key): bool
    {
        return !$this->has($key);
    }

    /**
     * if the request hasn't any of the specified keys, then call the callback.
     * @param array $keys
     */
    public function when_missing(string $key, callable $callback)
    {
        if ($this->missing($key)) {
            return $callback();
        }
    }

    /**
     * Merge the request data with the specified data.
     * @param array $data
     */
    public function merge(array $data): void
    {
        $this->post = array_merge($this->post, $data);
    }

    /**
     * Merge the request data with the specified data if the key is missing.
     * @param array $data
     */
    public function merge_if_missing(array $data): void
    {
        foreach ($data as $key => $value) {
            if (!$this->has($key)) {
                $this->post[$key] = $value;
            }
        }
    }
    
    /**
     * Set the request cookie
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function set_cookie(string $name, string $value): void
    {
        $this->cookies[$name] = $value;
    }

    public function remove_cookie(string $name): void
    {
        unset($this->cookies[$name]);
    }

    /**
     * Get the request cookie.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Check if the request has a cookie.
     * @param string $key
     * @return bool
     */
    public function has_cookie(string $name): bool
    {
        return isset($this->cookies[$name]);
    }

    /**
     * Get all the request cookies.
     * @return array
     */
    public function all_cookies(): array
    {
        return $this->cookies;
    }

    /**
     * Get the request file.
     * @param string $key
     * @return array|null
     */
    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Check if the request has a file.
     * @param string $key
     * @return bool
     */
    public function has_file(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Get the request server data.
     * @param string $key
     * @return mixed
     */
    public function server(string $key, $default = null)
    {
        return $this->server[$key] ?? $default;
    }
}
