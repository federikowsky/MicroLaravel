<?php

namespace App\Exceptions\Security;

use Exception;
use App\Exceptions\BaseException;


class ExpiredTokenException extends BaseException
{
    protected $message = 'Token has expired.';
    protected $code = 401;
    protected $view = 'errors/401';


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