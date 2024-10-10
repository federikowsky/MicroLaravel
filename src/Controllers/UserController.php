<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\AuthService;

class UserController extends BaseController
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