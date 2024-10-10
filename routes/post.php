<?php

return [
    'middleware' => ['EncryptCookiesMiddleware'], // Nessun middleware globale per le rotte dei post
    'routes' => [
        '/post/{slug}' => [
            'controller' => 'PostController',
            'method' => 'show',
            'name' => 'post.show',
            'middleware' => ['CSRFMiddleware']
        ]
    ]
];
