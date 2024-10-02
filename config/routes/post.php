<?php

return [
    'middleware' => [], // Nessun middleware globale per le rotte dei post
    'routes' => [
        '/post/{slug}' => [
            'controller' => 'PostController',
            'method' => 'show',
            'middleware' => []
        ]
    ]
];
