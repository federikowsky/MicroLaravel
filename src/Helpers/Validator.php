<?php

namespace App\Helpers;

use App\Facades\CWT;
use App\Models\User;

class Validator
{
    protected $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    const DEFAULT_VALIDATION_ERRORS = [
        'required' => 'Please enter the %s',
        'email' => 'The %s is not a valid email address',
        'min' => 'The %s must have at least %s characters',
        'max' => 'The %s must have at most %s characters',
        'between' => 'The %s must have between %d and %d characters',
        'same' => 'The %s must match with %s',
        'alphanumeric' => 'The %s should have only letters and numbers',
        'secure' => 'The %s must have between 8 and 64 characters and contain at least one number, one upper case letter, one lower case letter and one special character',
        'unique' => 'The %s already exists',
        'setted' => 'The %s is not set',
        'agree' => 'You must agree to the terms and conditions',
    ];

    public function validate(array $data, array $fields, array $messages = []): array
    {
        $split = fn($str, $separator) => array_map('trim', explode($separator, $str));
        $rule_messages = array_filter($messages, fn($message) => is_string($message));
        $validation_errors = array_merge(self::DEFAULT_VALIDATION_ERRORS, $rule_messages);
        $errors = [];

        foreach ($fields as $field => $option) {
            $rules = $split($option, '|');
            foreach ($rules as $rule) {
                $params = [];
                if (strpos($rule, ':')) {
                    [$rule_name, $param_str] = $split($rule, ':');
                    $params = $split($param_str, ',');
                } else {
                    $rule_name = trim($rule);
                }

                $fn = 'is_' . $rule_name;

                if (method_exists($this, $fn)) {
                    $pass = $this->$fn($data, $field, ...$params);
                    if (!$pass) {
                        $errors[$field] = sprintf(
                            $messages[$field][$rule_name] ?? $validation_errors[$rule_name],
                            $field,
                            ...$params
                        );
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Return true if a string is not empty
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function is_required(array $data, string $field): bool
    {
        return isset($data[$field]) && trim($data[$field]) !== '';
    }

    /**
     * Return true if the value is a valid email
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function is_email(array $data, string $field): bool
    {
        if (empty($data[$field])) {
            return true;
        }
        return filter_var($data[$field], FILTER_VALIDATE_EMAIL);
    }

    /**
     * Return true if a string has at least min length
     * @param array $data
     * @param string $field
     * @param int $min
     * @return bool
     */
    public function is_min(array $data, string $field, int $min): bool
    {
        if (!isset($data[$field])) {
            return true;
        }
        return mb_strlen($data[$field]) >= $min;
    }

    /**
     * Return true if a string cannot exceed max length
     * @param array $data
     * @param string $field
     * @param int $max
     * @return bool
     */
    public function is_max(array $data, string $field, int $max): bool
    {
        if (!isset($data[$field])) {
            return true;
        }
        return mb_strlen($data[$field]) <= $max;
    }

    /**
     * @param array $data
     * @param string $field
     * @param int $min
     * @param int $max
     * @return bool
     */
    public function is_between(array $data, string $field, int $min, int $max): bool
    {
        if (!isset($data[$field])) {
            return true;
        }
        $len = mb_strlen($data[$field]);
        return $len >= $min && $len <= $max;
    }

    /**
     * Return true if a string equals the other
     * @param array $data
     * @param string $field
     * @param string $other
     * @return bool
     */
    public function is_same(array $data, string $field, string $other): bool
    {
        if (isset($data[$field], $data[$other])) {
            return $data[$field] === $data[$other];
        }
        if (!isset($data[$field]) && !isset($data[$other])) {
            return true;
        }
        return false;
    }

    /**
     * Return true if a string is alphanumeric
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function is_alphanumeric(array $data, string $field): bool
    {
        if (!isset($data[$field])) {
            return true;
        }
        return ctype_alnum($data[$field]);
    }

    /**
     * Return true if a password is secure
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function is_secure(array $data, string $field): bool
    {
        if (!isset($data[$field])) {
            return false;
        }
        $pattern = "#.*^(?=.{8,64})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#";
        return preg_match($pattern, $data[$field]);
    }

    /**
     * Return true if the $value is unique in the column of a table
     * @param array $data
     * @param string $field
     * @param string $table
     * @param string $column
     * @return bool
     */
    public function is_unique(array $data, string $field, string $table, string $column): bool
    {
        if (!isset($data[$field])) {
            return true;
        }
    
        $sql = "SELECT $column FROM $table WHERE $column = :value LIMIT 1";
    
        $stmt = $this->userModel->execute_query($sql, [':value' => $data[$field]]);
    
        return $stmt->fetchColumn() === false;
    }

    public function is_setted(array $data, string $field, string $table, string $column): bool
    {
        if (!isset($data[$field])) {
            return true;
        }
    
        $sql = "SELECT $column FROM $table WHERE $column = :value LIMIT 1";
    
        $stmt = $this->userModel->execute_query($sql, [':value' => $data[$field]]);
    
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Return true if the value is true
     * @param array $data
     * @param string $field
     * @return bool
     */
    public function is_agree(array $data, string $field): bool
    {
        return isset($data[$field]) && $data[$field] === '1';
    }
}
