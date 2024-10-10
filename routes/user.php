<?php

return [
    'middleware' => ['EncryptCookiesMiddleware', 'AuthMiddleware'], // Middleware per tutte le rotte user
    'routes' => [
        '/user/profile' => [
            'controller' => 'UserController',
            'method' => 'index',
            'name' => 'user.index',
            'middleware' => []
        ],
        '/user/{id}' => [
            'controller' => 'UserController',
            'method' => 'show',
            'name' => 'user.show',
            'middleware' => []
        ],
        '/user/{id}/edit' => [
            'controller' => 'UserController',
            'method' => 'edit',
            'name' => 'user.edit',
            'middleware' => ['CSRFMiddleware']
        ]
    ]
];
