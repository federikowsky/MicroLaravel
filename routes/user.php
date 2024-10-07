<?php

return [
    'middleware' => ['AuthMiddleware'], // Middleware per tutte le rotte user
    'routes' => [
        '/user/profile' => [
            'controller' => 'UserController',
            'method' => 'index',
            'middleware' => []
        ],
        '/user/{id}' => [
            'controller' => 'UserController',
            'method' => 'show',
            'middleware' => []
        ],
        '/user/{id}/edit' => [
            'controller' => 'UserController',
            'method' => 'edit',
            'middleware' => ['CSRFMiddleware']
        ]
    ]
];
