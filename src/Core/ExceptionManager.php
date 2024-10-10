<?php

namespace App\Core;

use App\Core\Logger;
use App\Exceptions\BaseException;

use Throwable;


class ExceptionManager
{
    protected $logger;

    // Inietta il ServiceContainer e ottieni il logger se esiste
    public function __construct(Logger $logger)
    {
        // Prova a ottenere il logger dal container, ma non obbligarlo
        $this->logger = $logger;
    }

    protected function response($view, $status)
    {
        return response(
            view($view)->render(), 
            $status
        )
        ->with_headers(['Content-Type' => 'text/html'])
        ->send();
    }

    protected function logException(Throwable $exception)
    {
        $message = sprintf(
            "[%s] %s: %s in %s:%d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        $this->logger->error($message);
    }

    // Funzione generica per gestire errori
    public function handle(Throwable $exception)
    {
        $this->logException($exception);

        // Se è un'eccezione personalizzata
        if ($exception instanceof BaseException) {
            return $this->response($exception->getView(), $exception->getStatusCode());
        }

        // Se non è un'eccezione gestita, ritorna messaggio di errore che l'ha generata
        return response($exception->getMessage(), 500)
            ->header('Content-Type', 'text/plain')
            ->send();
    }
}
