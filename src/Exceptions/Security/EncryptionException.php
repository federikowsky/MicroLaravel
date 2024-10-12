<?php

namespace App\Exceptions\Security;

use Exception;
use App\Exceptions\BaseException;

class EncryptionException extends BaseException
{
    protected $message = 'Encryption error.';
    protected $code = 500;
    protected $view = 'errors/500';


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