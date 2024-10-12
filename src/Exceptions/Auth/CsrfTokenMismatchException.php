<?php

namespace App\Exceptions\Auth;

use Exception;
use App\Exceptions\BaseException;

class CsrfTokenMismatchException extends BaseException
{
    protected $message = 'CSRF token mismatch.';
    protected $code = 403;
    protected $view = 'errors/403';


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