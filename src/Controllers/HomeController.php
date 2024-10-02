<?php

require_once __DIR__ .'/../Models/User.php';
require_once __DIR__ . '/../bootstrap.php';

class HomeController
{
    protected $userModel;
    protected $db;

    public function __construct(ServiceContainer $container)
    {
        $this->db = $container->getLazy('db');
        $this->userModel = new User($this->db);
    }

    private function load_view(string $view, array $data = []): void
    {
        // Estrai le variabili per renderle disponibili nella vista
        extract($data);

        // Carica la vista dalla cartella src/Views
        require_once __DIR__ . '/../Views/' . $view;
    }

    private function load_view_message(string $view, string $message, string $type = FLASH_SUCCESS): void
    {
        flash('flash_' . uniqid(), $message, $type);

        $this->load_view($view, []);
    }

    public function index()
    {
        $this->load_view('home.php');
    }
}