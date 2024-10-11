<?php

return [
    'middleware' => ['EncryptCookiesMiddleware'], // Nessun middleware globale per le rotte dei post
    'routes' => [
        '/post/{id}' => [
            'controller' => 'PostController',
            'method' => 'show',
            'name' => 'post.show',
            'middleware' => ['CSRFMiddleware']
        ],
        '/post/{id}/comment/{comment_id}' => [
            'controller' => 'PostController',
            'method' => 'comment',
            'name' => 'post.comment',
            'middleware' => ['CSRFMiddleware']
        ],
    ]
];
