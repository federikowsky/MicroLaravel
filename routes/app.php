<?php

return [
    'middleware' => ['EncryptCookiesMiddleware'], // Middleware condiviso per tutte le rotte admin
    'routes' => [
        '/' => [
            'controller' => 'HomeController',
            'method' => 'index',
            'name' => 'home',
            'middleware' => []
        ],
    ]
];
