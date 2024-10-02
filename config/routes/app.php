<?php

return [
    'middleware' => [], // Middleware condiviso per tutte le rotte admin
    'routes' => [
        '/' => [
            'controller' => 'HomeController',
            'method' => 'index'
        ],
    ]
];
