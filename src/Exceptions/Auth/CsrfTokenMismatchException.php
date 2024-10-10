<?php

namespace App\Exceptions\Auth;

use Exception;
use App\Exceptions\BaseException;

class CsrfTokenMismatchException extends BaseException
{
    protected $message = 'CSRF token mismatch.';
    protected $view = '';

    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        if ($message) {
            $this->message = $message;
        }
        parent::__construct($this->message, $this->view, $this->code, $previous);
    }
}