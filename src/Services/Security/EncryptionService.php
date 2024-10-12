<?php 

namespace App\Services\Security;

use App\Exceptions\Security\EncryptionException;

class EncryptionService
{
    protected $key;
    protected $cipher = 'AES-256-CBC';
    protected $hmacAlgo = 'sha256';

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

        // Concat the IV with the encrypted data
        $encryptedData = base64_encode($iv . $encrypted);

        // Calculate the HMAC of the encrypted data
        $hmac = hash_hmac($this->hmacAlgo, $encryptedData, $this->key);

        // Concat the encrypted data with the HMAC
        return base64_encode($encryptedData . '.' . $hmac);
    }

    public function decrypt($encryptedData)
    {
        if (!$encryptedData) {
            return '';
        }

        // Decode the data
        $decodedData = base64_decode($encryptedData);

        // Split the data from the HMAC
        list($encryptedData, $hmac) = explode('.', $decodedData);

        // Calculate the HMAC of the encrypted data and compare it with the provided HMAC
        $calculatedHmac = hash_hmac($this->hmacAlgo, $encryptedData, $this->key);
        if (!hash_equals($hmac, $calculatedHmac)) {
            // HMAC validation failed
            throw new EncryptionException('Data integrity check failed.');
        }

        $encryptedData = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = substr($encryptedData, 0, $ivLength);
        $encrypted = substr($encryptedData, $ivLength);

        // Decrypt the data
        return openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $iv);
    }
}