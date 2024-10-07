<?php

define('DB_HOST', $_ENV['DB_HOST']);           // Connect to MySQL on 127.0.0.1
define('DB_NAME', $_ENV['DB_NAME']);       // Name of the database
define('DB_USER', $_ENV['DB_USER']);       // User name
define('DB_PASSWORD', $_ENV['DB_PASSWORD']);   // User password


/**
 * Connect to the database and returns an instance of PDO class
 * or false if the connection fails
 *
 * @return PDO
 */
function db(): PDO
{
    static $pdo;

    if (!$pdo) {
        $pdo = new PDO(
            sprintf("mysql:host=%s;dbname=%s;charset=UTF8", DB_HOST, DB_NAME),
            DB_USER,
            DB_PASSWORD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    
    return $pdo;
}

