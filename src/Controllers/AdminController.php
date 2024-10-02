<?php

require_once __DIR__ .'/../Models/User.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../bootstrap.php';

class AdminController
{
    protected $userModel;
    protected $authController;
    protected $db;

    public function __construct(ServiceContainer $container)
    {
        $this->db = $container->getLazy('db');
        $this->userModel = new User($this->db);
        $this->authController = new AuthController($container);
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

    public function is_admin(): bool
    {
        if ($this->authController->is_user_logged_in()) {
            return $this->userModel->is_admin($_SESSION['user_id']);
        }
        return false;
    }
    
    public function index()
    {
        if (!$this->is_admin()) {
            $this->load_view('home.php');
        }

        $this->load_view('admin.php');
    }
}