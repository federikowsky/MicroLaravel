<?php

namespace App\Exceptions\HTTP;

use App\Exceptions\BaseException;
use Exception;

class NotFoundException extends BaseException
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