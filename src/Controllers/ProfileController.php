<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Services\AuthService;


class ProfileController extends Controller
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