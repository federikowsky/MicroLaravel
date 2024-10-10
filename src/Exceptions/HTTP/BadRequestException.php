<?php

namespace App\Exceptions\HTTP;

use App\Exceptions\BaseException;
use Exception;


class BadRequestException extends BaseException
{
    protected $message = 'Bad Request';
    protected $code = 400;
    protected $view = 'errors/400';

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