<?php

return [
    'middleware' => ['CSRFMiddleware'], // Middleware condiviso per tutte le rotte auth
    'routes' => [
        '/auth/login' => [
            'controller' => 'AuthController',
            'method' => 'login',
            'middleware' => ['CSRFMiddleware']
        ],
        '/auth/logout' => [
            'controller' => 'AuthController',
            'method' => 'logout',
            'middleware' => ['CSRFMiddleware']
        ],
        '/auth/register' => [
            'controller' => 'AuthController',
            'method' => 'register',
            'middleware' => ['CSRFMiddleware']
        ],
        '/auth/activate' => [
            'controller' => 'AuthController',
            'method' => 'activate',
            'middleware' => ['CSRFMiddleware']
        ]
    ]
];
