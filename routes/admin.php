<?php

return [
    'middleware' => ['AuthMiddleware', 'AdminMiddleware'], // Middleware condiviso per tutte le rotte admin
    'routes' => [
        '/admin' => [
            'controller' => 'AdminController',
            'method' => 'index',
            'middleware' => []
        ],
    ]
];
