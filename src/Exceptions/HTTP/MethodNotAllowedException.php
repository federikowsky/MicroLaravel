<?php

namespace App\Exceptions\HTTP;

use App\Exceptions\AppException;
use Exception;

class MethodNotAllowedException extends AppException
{
    protected $message = 'HTTP Method Not Allowed';
    protected $code = 405;
    protected $view = 'errors/405';

    public function __construct($message = null, $code = null, Exception $previous = null)
    {
        if ($message !== null) {
            $this->message = $message;
        }
        if ($code !== null) {
            $this->code = $code;
        }
        parent::__construct($this->message, $this->view, $this->code, $previous);
    }
}