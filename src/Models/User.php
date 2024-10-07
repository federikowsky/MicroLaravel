<?php
// src/Models/User.php

namespace App\Models;

use PDO;

class User {
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Metodo generico per eseguire una query con parametri di binding
    public function execute_query(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }

    // Metodo per verificare se un utente è admin
    public function is_admin($user_id): bool
    {
        $query = 'SELECT is_admin
                FROM users
                WHERE id = :id';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $user_id);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user['is_admin'] === '1';
    }

    // Metodo per verificare se un utente gia esiste
    public function exists($email, $username): bool
    {
        $query = 'SELECT COUNT(*) as count
                FROM users
                WHERE email = :email OR username = :username';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':username', $username);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $result['count'] > 0;
    }

    // Metodo per creare un nuovo utente
    public function create(string $email, string $username, string $password, string $activation_code, int $expiry = 60 * 60 * 24 * 1, bool $is_admin = false): bool
    {
        // sanity check
        if ($this->exists($email, $username)) {
            return false;
        }

        $query = 'INSERT INTO users(username, email, password, is_admin, activation_code, activation_expiry)
            VALUES(:username, :email, :password, :is_admin, :activation_code,:activation_expiry)';
        
        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
        $stmt->bindValue(':is_admin', (int) $is_admin, PDO::PARAM_INT);
        $stmt->bindValue(':activation_code', hash_hmac('sha256', $activation_code, SECRET_KEY));
        $stmt->bindValue(':activation_expiry', date('Y-m-d H:i:s', time() + $expiry));


        return $stmt->execute();
    }

    // Metodo per attivare un utente
    public function activate(int $user_id): bool
    {
        $query = 'UPDATE users
                SET active = 1, activated_at = CURRENT_TIMESTAMP
                WHERE id=:id';
    
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    
        return $stmt->execute();
    }

    // Metodo per aggiornare i dati di un utente
    public function update($id, array $data) {
        $query = 'UPDATE users
            SET username = :username, email = :email, is_admin = :is_admin
            WHERE id = :id';
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':username', $data['username']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':is_admin', $data['is_admin']);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

    // Metodo per eliminare un utente
    public function delete($id) {
        $query = 'DELETE FROM users
            WHERE id =:id';
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

    // Inserisce un token di autenticazione (remember me)
    public function insert_user_token(int $user_id, string $selector, string $validator, string $expiry): bool
    {
        $query = 'INSERT INTO user_tokens(user_id, selector, hashed_validator, expiry)
                VALUES(:user_id, :selector, :hashed_validator, :expiry)';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue(':selector', $selector);
        $stmt->bindValue(':hashed_validator', hash_hmac('sha256', $validator, SECRET_KEY));
        $stmt->bindValue(':expiry', $expiry);

        return $stmt->execute();
    }

    // Elimina un token di autenticazione associato a un utente
    public function delete_user_token(int $user_id): bool
    {
        $query = 'DELETE FROM user_tokens WHERE user_id = :user_id';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $user_id);

        return $stmt->execute();
    }

    // Metodo per trovare un utente tramite l'email
    public function find_by_email($email)
    {
        $query = 'SELECT id, username, email, password, active, is_admin
                FROM users
                WHERE email=:email';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Metodo per trovare un utente tramite l'username
    public function find_by_username($username)
    {
        $query = 'SELECT id, username, email, password, active, is_admin
                FROM users
                WHERE username=:username';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':username', $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Metodo per trovare un utente tramite l'id
    public function find_by_id($id)
    {
        $query = 'SELECT id, username, email, password, active, is_admin
                FROM users
                WHERE id=:id';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function finde_by_activation_code($activation_code)
    {
        $hashed_activation_code = hash_hmac('sha256', $activation_code, SECRET_KEY);

        $query = 'SELECT id, username, email, password, active, is_admin
                FROM users
                WHERE activation_code=:activation_code';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':activation_code', $hashed_activation_code);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Metodo per trovare un utente non verificato
    public function find_unverified_user($activation_code)
    {
        $hashed_activation_code = hash_hmac('sha256', $activation_code, SECRET_KEY);
        
        // find the user with the activation code
        $query = 'SELECT id, activation_code, activation_expiry < now() as expired
                FROM users
                WHERE active = 0 AND activation_code=:activation_code';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':activation_code', $hashed_activation_code);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // already expired, delete the in active user with expired activation code
            if ((int) $user['expired'] === 1) {
                $this->delete($user['id']);
                return null;
            }
            // verify the password
            if (hash_equals($user['activation_code'], $activation_code)) {
                return $user;
            }
        }

        return null;
    }

    // Trova un token di autenticazione in base al selector
    public function find_user_token_by_selector(string $selector)
    {
        $query = 'SELECT id, selector, hashed_validator, user_id, expiry
                FROM user_tokens
                WHERE selector = :selector AND
                      expiry >= now()
                LIMIT 1';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':selector', $selector);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Trova un utente associato a un token
    public function find_user_by_token(string $selector)
    {
        $query = 'SELECT users.id, username
                FROM users
                INNER JOIN user_tokens ON user_id = users.id
                WHERE selector = :selector AND
                      expiry > now()
                LIMIT 1';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':selector', $selector);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
