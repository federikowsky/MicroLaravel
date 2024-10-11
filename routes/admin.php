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
        '/admin/dashboard' => [
            'controller' => 'AdminController',
            'method' => 'dashboard',
            'name' => 'admin.dashboard',
            'middleware' => []
        ],
        '/admin/users' => [
            'controller' => 'AdminController',
            'method' => 'users',
            'name' => 'admin.users',
            'middleware' => []
        ],
        '/admin/posts' => [
            'controller' => 'AdminController',
            'method' => 'posts',
            'name' => 'admin.posts',
            'middleware' => []
        ],
        '/admin/posts/create' => [
            'controller' => 'AdminController',
            'method' => 'create_post',
            'name' => 'admin.posts.create',
            'middleware' => []
        ],
        '/admin/posts/edit' => [
            'controller' => 'AdminController',
            'method' => 'edit_post',
            'name' => 'admin.posts.edit',
            'middleware' => []
        ],
        '/admin/posts/delete' => [
            'controller' => 'AdminController',
            'method' => 'delete_post',
            'name' => 'admin.posts.delete',
            'middleware' => []
        ],
        '/admin/posts/{id}' => [
            'controller' => 'AdminController',
            'method' => 'show_post',
            'name' => 'admin.posts.show',
            'middleware' => []
        ],
    ]
];
