<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Services\AuthService;

class UserController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function index()
    {

    }
}