<?php

const DB_HOST = '127.0.0.1';  // Connetti a MySQL su 127.0.0.1 (localhost tramite TCP/IP)
const DB_NAME = 'auth';        // Nome del database creato
const DB_USER = 'myuser';      // Nome utente
const DB_PASSWORD = 'mypassword';  // Password dell'utente



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

