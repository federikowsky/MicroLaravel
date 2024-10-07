<?php

/**
 * return response object from the container
 */
if (!function_exists('response')) {
    function response($content = '', $status = 200, array $headers = [])
    {
        return \App\Facades\Response::make($content, $status, $headers);
    }
}

/**
 * return view object from the container
 */
if (!function_exists('view')) {
    function view($view, $data = [])
    {
        return \App\Facades\View::make($view, $data);
    }
}

/**
 * return redirect object from the container
 */
if (!function_exists('redirect')) {
    function redirect($path = null, $status = 302, $headers = [], $secure = null)
    {
        $redirector = \App\Facades\Redirect::make($path, $status, $headers);

        if ($path) {
            // Se viene fornito un percorso, esegue subito la redirezione.
            return $redirector->to($path, $status, $headers, $secure);
        }

        // Altrimenti, restituisce l'istanza per permettere il chaining come redirect()->to() o redirect()->route()
        return $redirector;
    }
}

if (!function_exists('assets')) {
    function assets($view)
    {
        return \App\Facades\Assets::load($view);
    }
}







/**
 * Generate a CSRF token
 *
 * @return string
 */
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 64-character token
    }

    return $_SESSION['csrf_token'];
}

/**
 * Return a CSRF field
 *
 * @return string
 */
function csrf_field(): string
{
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}


/**
 * Return the error class if error is found in the array $errors
 *
 * @param array $errors
 * @param string $field
 * @return string
 */
function error_class(array $errors, string $field): string
{
    return isset($errors[$field]) ? 'error' : '';
}

/**
 * Return true if the request method is POST
 *
 * @return boolean
 */
function is_post_request(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
}

/**
 * Return true if the request method is GET
 *
 * @return boolean
 */
function is_get_request(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD']) === 'GET';
}


/**
 * Flash data specified by $keys from the $_SESSION
 * @param ...$keys
 * @return array
 */
function session_flash(...$keys): array
{
    $data = [];
    foreach ($keys as $key) {
        if (isset($_SESSION[$key])) {
            $data[] = $_SESSION[$key];
            unset($_SESSION[$key]);
        } else {
            $data[] = [];
        }
    }
    return $data;
}
