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

    /**
     * Run a costum query 
     * @param string $sql
     * @param array $params
     * @return bool|\PDOStatement
     */
    public function execute_query(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Check if a user is an admin
     * @param mixed $user_id
     * @return bool
     */
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

    /**
     * Check if a user exists
     * @param mixed $email
     * @param mixed $username
     * @return bool
     */
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

    /**
     * Create a new user 
     * @param string $email
     * @param string $username
     * @param string $password
     * @param string $activation_code
     * @param int $expiry
     * @param bool $is_admin
     * @return bool
     */
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

    /**
     * Activate a user
     * @param int $user_id
     * @return bool
     */
    public function activate(int $user_id): bool
    {
        $query = 'UPDATE users
                SET active = 1, activated_at = CURRENT_TIMESTAMP
                WHERE id=:id';
    
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    
        return $stmt->execute();
    }

    /**
     * Update a user field, only the fields provided in the $data array will be updated
     * if no valid fields provided for update throw an exception
     
     * @param int $user_id
     * @param string $password
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function update($id, array $data): bool
    {
        $validFields = ['username', 'email', 'password', 'is_admin', 'active'];
        $data = array_intersect_key($data, array_flip($validFields));

        // if no valid fields provided for update throw an exception
        if (empty($data)) {
            throw new \InvalidArgumentException('No valid fields provided for update.');
        }

        // Create the SET part of the SQL query
        $setParts = implode(', ', array_map(fn($field) => "$field = :$field", array_keys($data)));

        // Build the query
        $query = "UPDATE users SET $setParts WHERE id = :id";

        $stmt = $this->db->prepare($query);

        // Binding
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete a user
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $query = 'DELETE FROM users
            WHERE id =:id';
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

    /**
     * Insert a new user token for the remember me feature
     * @param string $email
     * @return array
     */
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

    /**
     * Delete all user tokens
     * @param int $user_id
     * @return bool
     */
    public function delete_user_token(int $user_id): bool
    {
        $query = 'DELETE FROM user_tokens WHERE user_id = :user_id';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $user_id);

        return $stmt->execute();
    }

    /**
     * Find a user by email
     * @param string $email
     * @return array
     */
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

    /**
     * Find a user by username
     * @param string $username
     * @return array
     */
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

    /**
     * Find a user by id
     * @param int $id
     * @return array
     */
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

    /**
     * Find a user by activation code 
     * @param string $activation_code
     * @return array
     */
    public function find_by_activation_code($activation_code)
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

    /**
     * Find an unverified user by activation code
     * @param string $activation_code
     * @return array
     */
    public function find_unverified_user($email, $activation_code)
    {
        $hashed_activation_code = hash_hmac('sha256', $activation_code, SECRET_KEY);
        
        // find the user with the activation code
        $query = 'SELECT id, activation_code, activation_expiry < now() as expired
                FROM users
                WHERE active = 0 AND email=:email AND activation_code=:activation_code';

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':activation_code', $hashed_activation_code);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // if the activation code is not expired return the user
            if ((int) $user['expired'] === 0) {
                return $user;
            }
            
            // already expired, delete the in active user with expired activation code
            $this->delete($user['id']);
        }

        return null;
    }

    /**
     * Find a user token by selector
     * @param string $selector
     * @return array
     */
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

    /**
     * Find a user by token
     * @param string $selector
     * @return array
     */
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
