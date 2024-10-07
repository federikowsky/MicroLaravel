<?php

namespace App\Controllers;

use App\Controllers\Controller;

use App\Core\ServiceContainer;

class HomeController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        return view('home');
    }
}