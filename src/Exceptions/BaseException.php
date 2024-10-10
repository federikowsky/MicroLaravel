<?php

namespace App\Exceptions;

use Exception;

class BaseException extends Exception
{
    protected $view;
    protected $statusCode;

    public function __construct($message = '', $view = 'errors/500', $statusCode = 500, Exception $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->view = $view;
        $this->statusCode = $statusCode;
    }

    public function getView()
    {
        return $this->view;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
