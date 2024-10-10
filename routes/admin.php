<?php

return [
    'middleware' => ['EncryptCookiesMiddleware', 'AuthMiddleware', 'AdminMiddleware'], // Middleware condiviso per tutte le rotte admin
    'routes' => [
        '/admin' => [
            'controller' => 'AdminController',
            'method' => 'index',
            'name' => 'admin.index',
            'middleware' => []
        ],
    ]
];
