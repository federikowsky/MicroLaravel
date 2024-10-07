<?php

namespace App\Exceptions\HTTP;

use App\Exceptions\AppException;
use Exception;

class NotFoundException extends AppException
{
    protected $message = 'Page not found';
    protected $code = 404;

    protected $view = 'errors/404';


    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        if ($message) {
            $this->message = $message;
        }
        if ($code) {
            $this->code = $code;
        }
        parent::__construct($this->message, $this->view, $this->code, $previous);
    }
}