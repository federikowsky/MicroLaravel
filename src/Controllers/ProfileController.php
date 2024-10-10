<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\AuthService;


class ProfileController extends BaseController
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function index()
    {
        if (!$this->authService->is_user_logged_in()) {
            return view('home');
        }

        return view('profile');
    }
}