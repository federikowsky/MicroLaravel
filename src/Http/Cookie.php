<?php 

namespace App\Http;

class Cookie
{
    public string $name;
    public string $value;
    public int $expire;
    public string $path;
    public ?string $domain;
    public bool $secure;
    public bool $httpOnly;



    public function __construct(string $name, string $value, int $expire, string $path = '/', ?string $domain = '', bool $secure = false, bool $httpOnly = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->expire = $expire;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
    }
}