<?php

namespace App\Exceptions\Auth;

use App\Exceptions\AppException;
use Exception;

class TokenMismatchException extends AppException
{
    // Custom message for the exception
    protected $message = 'Token mismatch error occurred.';
    protected $view = '';

    // You can add custom properties or methods if needed
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        if ($message) {
            $this->message = $message;
        }
        parent::__construct($this->message, $this->view, $this->code, $previous);
    }
}