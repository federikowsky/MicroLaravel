<?php

namespace App\Session\Drivers;

use App\Helpers\DotNotationManager;
use App\Session\Contracts\SessionDriverInterface;

class DatabaseSessionDriver implements SessionDriverInterface
{
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function get($key, $default = null)
    {
        // get the session value
    }

    public function set($key, $value)
    {
        // set the session value
    }

    public function update($key, $value)
    {
        // update the session value
    }

    public function pull($key, $default = null)
    {
        // pull the session value
    }

    public function has($key)
    {
        // check if the session has a key
    }

    public function exists($key)
    {
        // check if the session exists
    }

    public function remove($key)
    {
        // remove the session key
    }

    // public function flash($key, $value)
    // {
    //     // flash the session key
    // }

    // public function reflash()
    // {
    //     // reflash the session
    // }

    // public function keep($keys = null)
    // {
    //     // keep the session
    // }

    public function all()
    {
        // get all the session
    }

    public function clear()
    {
        // clear the session
    }

    public function regenerate($destroy = false): bool
    {
        // regenerate the session
        return true | false;
    }

    public function previousUrl()
    {
        // get the previous url
    }

    public function increment($key, $amount = 1)
    {
        // increment the session
    }

    public function decrement($key, $amount = 1)
    {
        // decrement the session
    }

    public function token()
    {
        // get the session token
    }

    public function token_time()
    {
        // get the session token time
    }

    public function regenerate_token($minutes)
    {
        // regenerate the session token
    }
    
    public function save()
    {
        // save the session
    }

    public function migrate($destroy = false)
    {
        // migrate the session
    }

    public function isStarted()
    {
        // check if the session is started
    }

    public function destroy()
    {
        // destroy the session
    }
}