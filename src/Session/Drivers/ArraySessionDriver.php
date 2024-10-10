<?php 
namespace App\Session\Drivers;


use App\Helpers\DotNotationManager;
use App\Session\Contracts\SessionDriverInterface;

class ArraySessionDriver implements SessionDriverInterface
{
    protected $sessionData = [];

    public function __construct()
    {
        $this->sessionData = [];
    }


    public function get($key, $default = null)
    {
        return DotNotationManager::get($this->sessionData, $key, $default);
    }

    public function set($key, $value)
    {
        DotNotationManager::set($this->sessionData, $key, $value);
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
        return array_key_exists($key, $this->sessionData);
    }

    public function remove($key)
    {
        DotNotationManager::remove($this->sessionData, $key);
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
        return $this->sessionData;
    }

    public function clear()
    {
        $this->sessionData = [];
    }

    public function regenerate($destroy = false)
    {
        $this->sessionData = [];
        return true;
    }

    public function previousUrl()
    {
        return $this->get('_previous_url');
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
    }

    public function migrate($destroy = false)
    {
    }

    public function isStarted()
    {
        return true;
    }

    public function destroy()
    {
        $this->sessionData = [];
    }
}
