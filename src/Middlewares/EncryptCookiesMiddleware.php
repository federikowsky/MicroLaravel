<?php

namespace App\Middlewares;

use App\Services\Security\EncryptionService;

class EncryptCookiesMiddleware
{
    protected $encryptionService;
    protected $except = ['PHPSESSID'];

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function handle(callable $next)
    {
        // decript cookies in request
        $cookies = request()->all_cookies();
        foreach ($cookies as $name => $value) {
            if (!in_array($name, $this->except)) {
                request()->set_cookie(
                    $name, 
                    $this->encryptionService->decrypt($value)
                );
            }
        }

        // Continue with the request
        return $next();
    }
}
