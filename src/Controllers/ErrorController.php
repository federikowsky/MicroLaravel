<?php

require_once __DIR__ . '/../bootstrap.php';

class ErrorController
{
    protected $logger;

    // Inietta il ServiceContainer e ottieni il logger se esiste
    public function __construct(Logger $logger)
    {
        // Prova a ottenere il logger dal container, ma non obbligarlo
        $this->logger = $logger;
    }

    private function load_view(string $view, array $data = []): void
    {
        // Estrai le variabili per renderle disponibili nella vista
        extract($data);

        // Carica la vista dalla cartella src/Views
        require_once __DIR__ . '/../Views/Errors/' . $view;
    }

    // Funzione generica per gestire errori
    public function handle(Throwable $exception): void
    {
        // Logga l'errore
        
        // Gestione degli errori
        if ($exception instanceof NotFoundException) {
            $this->logger->error('Error 404: ' . $exception->getMessage());
            http_response_code(404);
            $this->load_view('404.php');
        } else {
            $this->logger->error('Error 500: ' . $exception->getMessage());
            http_response_code(500);
            $this->load_view('500.php');
        }

        // Se lo desideri, mostra dettagli sugli errori solo in modalità sviluppo
        if (getenv('APP_ENV') === 'development') {
            echo $exception->getMessage();
        }
    }
}
