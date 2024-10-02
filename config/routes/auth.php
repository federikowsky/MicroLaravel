<?php

return [
    'middleware' => [], // Middleware condiviso per tutte le rotte auth
    'routes' => [
        '/auth/login' => [
            'controller' => 'AuthController',
            'method' => 'login'
        ],
        '/auth/logout' => [
            'controller' => 'AuthController',
            'method' => 'logout'
        ],
        '/auth/register' => [
            'controller' => 'AuthController',
            'method' => 'register'
        ],
        '/auth/activate' => [
            'controller' => 'AuthController',
            'method' => 'activate'
        ]
    ]
];
