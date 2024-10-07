<?php

namespace App\Exceptions\Auth;

use App\Exceptions\AppException;
use Exception;

class UnauthorizedException extends AppException
{
    protected $message = 'Access denied due to invalid credentials.';
    protected $view = '';

    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        if ($message) {
            $this->message = $message;
        }
        parent::__construct($this->message, $this->view, $this->code, $previous);
    }
}