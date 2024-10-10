<?php

return [
    'driver' => 'file', // file, array, database
    'session_path' => __DIR__ . '/../storage/framework/sessions',
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'cookie' => 'session',
    'cookie_path' => '/',
    'cookie_domain' => null,
    'secure' => false,
    'http_only' => true,
    'database' => [
        'connection' => 'mysql',
        'table' => 'sessions',
    ],
];
