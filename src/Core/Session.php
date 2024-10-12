<?php
namespace App\Core;

use App\Session\Contracts\SessionDriverInterface;

class Session
{
    protected $driver;

    public function __construct(SessionDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function get($key, $default = null)
    {
        return $this->driver->get($key, $default);
    }

    public function set($key, $value)
    {
        $this->driver->set($key, $value);
    }

    public function update($key, $value)
    {
        $this->driver->update($key, $value);
    }

    public function pull($key, $default = null)
    {
        return $this->driver->pull($key, $default);
    }

    public function has($key)
    {
        return $this->driver->has($key);
    }

    public function exists($key)
    {
        return $this->driver->exists($key);
    }

    public function remove($key)
    {
        $this->driver->remove($key);
    }

    // public function flash($key, $value)
    // {
    //     $this->driver->flash($key, $value);
    // }

    // public function reflash()
    // {
    //     $this->driver->reflash();
    // }

    // public function keep($keys = null)
    // {
    //     $this->driver->keep($keys);
    // }

    public function all()
    {
        return $this->driver->all();
    }

    public function clear()
    {
        $this->driver->clear();
    }

    public function regenerate($destroy = false)
    {
        $this->regenerate_token();
        return $this->driver->regenerate($destroy);

    }

    public function previousUrl()
    {
        return $this->driver->previousUrl();
    }

    public function increment($key, $amount = 1)
    {
        $this->driver->increment($key, $amount);
    }

    public function decrement($key, $amount = 1)
    {
        $this->driver->decrement($key, $amount);
    }

    public function token()
    {
        return $this->driver->token();
    }

    public function token_time()
    {
        return $this->driver->token_time();
    }

    public function regenerate_token($minutes = 60)
    {
        $this->driver->regenerate_token($minutes);
    }

    public function save()
    {
        $this->driver->save();
    }

    public function migrate($destroy = false)
    {
        $this->driver->migrate($destroy);
    }

    public function isStarted()
    {
        return $this->driver->isStarted();
    }

    public function destroy()
    {
        $this->driver->destroy();
    }
}
