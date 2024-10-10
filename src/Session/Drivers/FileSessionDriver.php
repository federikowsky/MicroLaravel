<?php 

namespace App\Session\Drivers;

use App\Helpers\DotNotationManager;
use App\Session\Contracts\SessionDriverInterface;

class FileSessionDriver implements SessionDriverInterface
{
    protected $session_path;

    public function __construct($session_path)
    {
        $this->session_path = $session_path;
        
        if (!is_dir($this->session_path)) {
            mkdir($this->session_path, 0777, true);
        }

        session_save_path($this->session_path);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function get($key, $default = null)
    {
        return DotNotationManager::get($_SESSION, $key, $default);
    }

    public function set($key, $value)
    {
        DotNotationManager::set($_SESSION, $key, $value);
    }

    public function update($key, $value)
    {
        $array = $this->get($key, []);
        $array[] = $value;
        $this->set($key, $array);
    }

    public function pull($key, $default = null)
    {
        $value = $this->get($key, $default);
        $this->remove($key);
        return $value;
    }

    public function has($key)
    {
        return $this->get($key) !== null;
    }

    public function exists($key)
    {
        return array_key_exists($key, $_SESSION);
    }

    public function remove($key)
    {
        DotNotationManager::remove($_SESSION, $key);
    }

    // public function flash($key, $value)
    // {
    //     $this->set('_flash.' . $key, $value);
    // }

    // public function reflash()
    // {
    //     foreach ($this->get('_flash', []) as $key => $value) {
    //         $this->set($key, $value);
    //     }
    //     $this->remove('_flash');
    // }

    // public function keep($keys = null)
    // {
    //     $flash = $this->get('_flash', []);
    //     if ($keys) {
    //         foreach ($keys as $key) {
    //             if (isset($flash[$key])) {
    //                 $this->set($key, $flash[$key]);
    //             }
    //         }
    //     } else {
    //         $this->reflash();
    //     }
    // }

    public function all()
    {
        return $_SESSION;
    }

    public function clear()
    {
        $_SESSION = [];
    }

    public function regenerate($destroy = false): bool
    {
        return session_regenerate_id($destroy);
    }

    public function previousUrl()
    {
        return $_SESSION['_previous_url'] ?? null;
    }

    public function increment($key, $amount = 1)
    {
        $value = $this->get($key, 0) + $amount;
        $this->set($key, $value);
    }

    public function decrement($key, $amount = 1)
    {
        $value = $this->get($key, 0) - $amount;
        $this->set($key, $value);
    }

    public function token()
    {
        return $this->get('_csrf_token');
    }

    public function regenerate_token()
    {
        $this->set('_csrf_token', bin2hex(random_bytes(32)));
    }

    public function save()
    {
        session_write_close();
    }

    public function migrate($destroy = false)
    {
        $this->regenerate($destroy);
    }


    public function isStarted()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function destroy()
    {
        session_destroy();
    }
}
