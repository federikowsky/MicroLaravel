<?php

return [
    'middleware' => ['EncryptCookiesMiddleware', 'CSRFMiddleware'], // Middleware condiviso per tutte le rotte auth
    'routes' => [
        '/auth/login' => [
            'controller' => 'AuthController',
            'method' => 'login',
            'name' => 'auth.login',
            'middleware' => ['CSRFMiddleware']
        ],
        '/auth/logout' => [
            'controller' => 'AuthController',
            'method' => 'logout',
            'name' => 'auth.logout',
            'middleware' => ['CSRFMiddleware']
        ],
        '/auth/register' => [
            'controller' => 'AuthController',
            'method' => 'register',
            'name' => 'auth.register',
            'middleware' => ['CSRFMiddleware']
        ],
        '/auth/activate' => [
            'controller' => 'AuthController',
            'method' => 'activate',
            'name' => 'auth.activate',
            'middleware' => ['CSRFMiddleware']
        ]
    ]
];
