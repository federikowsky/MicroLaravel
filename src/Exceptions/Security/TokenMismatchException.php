<?php

namespace App\Exceptions\Security;

use App\Exceptions\BaseException;
use Exception;

class TokenMismatchException extends BaseException
{
    // Custom message for the exception
    protected $message = 'Token mismatch error occurred.';
    protected $code = 403;
    protected $view = 'errors/403';


    // You can add custom properties or methods if needed
    public function __construct($message = null, $code = null, Exception $previous = null)
    {
        if ($message !== null) {
            $this->message = $message;
        }
        if ($code !== null) {
            $this->code = $code;
        }
        parent::__construct($this->message,  $this->view, $this->code, $previous);
    }
}