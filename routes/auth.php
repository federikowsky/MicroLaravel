<?php

return [
    'middleware' => ['EncryptCookiesMiddleware', 'CSRFMiddleware'], // Middleware condiviso per tutte le rotte auth
    'routes' => [
        '/auth/login' => [
            'controller' => 'AuthController',
            'method' => 'login',
            'name' => 'auth.login',
            'middleware' => []
        ],
        '/auth/logout' => [
            'controller' => 'AuthController',
            'method' => 'logout',
            'name' => 'auth.logout',
            'middleware' => []
        ],
        '/auth/register' => [
            'controller' => 'AuthController',
            'method' => 'register',
            'name' => 'auth.register',
            'middleware' => []
        ],
        '/auth/activate' => [
            'controller' => 'AuthController',
            'method' => 'activate',
            'name' => 'auth.activate',
            'middleware' => ['EWTMiddleware']
        ],
        '/auth/password/forgot' => [
            'controller' => 'AuthController',
            'method' => 'forgot_password',
            'name' => 'auth.forgot_password',
            'middleware' => []
        ],
        '/auth/password/reset' => [
            'controller' => 'AuthController',
            'method' => 'reset_password',
            'name' => 'auth.reset_password',
            'middleware' => ['EWTMiddleware']
        ]
    ]
];
