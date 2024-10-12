<?php

namespace App\Services\Security;

use App\Exceptions\Security\ExpiredTokenException;
use App\Facades\Encrypt;

class EncryptedWebTokenService
{
    protected $secret_key = SECRET_KEY;

    public function __construct() {}

    /**
     * Genera un CWT con i dati forniti.
     *
     * @param array $payload
     * @param int $expiration_seconds
     * @return string
     */
    public function generate(array $payload, int $expiration_seconds = 3600): string
    {
        $payload['exp'] = time() + $expiration_seconds;
        $payload['iat'] = time();
        $payload['iss'] = APP_URL;

        $payloadEncoded = base64_encode(json_encode($payload));

        return Encrypt::encrypt($payloadEncoded);
    }

    /**
     * Verifica e decodifica un CWT.
     *
     * @param string $token
     * @return array|null
     */
    public function decode(string $token): ?array
    {
        $token = Encrypt::decrypt($token);
        
        // Decodifica il payload
        $payload = json_decode(base64_decode($token), true);

        // Verifica che il token non sia scaduto
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new ExpiredTokenException('Token has expired.');
        }

        return $payload;
    }

    /**
     * Verifica la validità di un CWT senza decodificarlo.
     *
     * @param string $token
     * @return bool
     */
    public function validate(string $token): bool
    {
        try {
            $this->decode($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}