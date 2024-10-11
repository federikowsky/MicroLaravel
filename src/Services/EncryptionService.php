<?php 

namespace App\Services;

class EncryptionService
{
    protected $key;
    protected $cipher = 'AES-256-CBC';

    public function __construct($key)
    {
        $this->key = hash('sha256', $key, true);
    }

    public function encrypt($data)
    {
        if (!$data) {
            return '';
        }
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $iv);

        // Concatena l'IV (Initialization Vector) con i dati criptati
        return base64_encode($iv . $encrypted);
    }

    public function decrypt($encryptedData)
    {
        if (!$encryptedData) {
            return '';
        }
        
        $encryptedData = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = substr($encryptedData, 0, $ivLength);
        $encrypted = substr($encryptedData, $ivLength);

        return openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $iv);
    }
}
