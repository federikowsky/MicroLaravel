<?php

namespace App\Services\Security;

use App\Exceptions\Auth\CwtTokenMismatchException;
use App\Exceptions\Security\ExpiredTokenException;

class CustomWebTokenService
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
        $header = json_encode(['alg' => 'HS256', 'typ' => 'CWT']);
        $payload['exp'] = time() + $expiration_seconds;
        $payload['iat'] = time();
        $payload['iss'] = APP_URL;

        // Codifica l'header e il payload
        $headerEncoded = base64_encode($header);

        $payloadEncoded = base64_encode(json_encode($payload));

        // Firma il token
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->secret_key, true);
        $signatureEncoded = base64_encode($signature);

        $tk = $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
        
        return $tk;
    }

    /**
     * Verifica e decodifica un CWT.
     *
     * @param string $tk
     * @return array|null
     */
    public function decode(string $tk): ?array
    {
        
        [$headerEncoded, $payloadEncoded, $signatureEncoded] = explode('.', $tk);

        // Ricrea la firma per confrontarla
        $expectedSignature = base64_encode(hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->secret_key, true));

        // Verifica che la firma sia valida
        if (!hash_equals($expectedSignature, $signatureEncoded)) {
            throw new CwtTokenMismatchException('Invalid token signature.');
        }

        // Decodifica il payload
        $payload = json_decode(base64_decode($payloadEncoded), true);

        // Verifica che il token non sia scaduto
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new ExpiredTokenException('Token has expired.');
        }

        return $payload;
    }

    /**
     * Verifica la validità di un CWT senza decodificarlo.
     *
     * @param string $tk
     * @return bool
     */
    public function validate(string $tk): bool
    {
        try {
            $this->decode($tk);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}