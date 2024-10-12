<?php 

namespace App\Session\Contracts;

interface SessionDriverInterface
{
    public function get($key, $default = null);
    public function set($key, $value);
    public function update($key, $value);
    public function pull($key, $default = null);
    public function has($key);
    public function exists($key);
    public function remove($key);
    // public function flash($key, $value);
    // public function reflash();
    // public function keep($keys = null);
    public function all();
    public function clear();
    public function regenerate($destroy = false);
    public function previousUrl();
    public function increment($key, $amount = 1);
    public function decrement($key, $amount = 1);
    public function token();
    public function token_time();

    public function regenerate_token($minutes);
    public function save();
    public function migrate($destroy = false);
    public function isStarted();
    public function destroy();
}
