<?php


namespace App\Services;

use App\Models\User;

class JwtService
{
    public function generateToken(User $user, string $type = 'access'): string
    {

        $header = [

            'alg' => 'HS256',

            'typ' => 'JWT',

        ];

        $expiration = match ($type) {
            'access' => time() + (config('jwt.access_ttl') * 60),
            'refresh' => time() + (config('jwt.refresh_ttl') * 60)
        };


        $payload = [
            'sub' => $user->getKey(),
            'email' => $user->email,
            'type' => $type,
            'iat' => time(),
            'exp' => $expiration,
            'jti' => bin2hex(random_bytes(16))
        ];

        $headerJson = json_encode($header);
        $payloadJson = json_encode($payload);

        $headerEncoded = $this->base64UrlEncode($headerJson);
        $payloadEcoded = $this->base64UrlEncode($payloadJson);

        $data = $headerEncoded . '.' . $payloadEcoded;

        $signiture = hash_hmac(
            'sha256',
            $data,
            config('jwt.secret'),
            true
        );
        $signitureEncoded = $this->base64UrlEncode($signiture);

        return $headerEncoded . '.' . $payloadEcoded . '.' . $signitureEncoded;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(
            strtr(base64_encode($data), '+/', '-_'),
            '='
        );
    }

    public function verifyToken(String $token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $header = json_decode(
            $this->base64UrlDecode($headerEncoded),
            true
        );

        $payload = json_decode(
            $this->base64UrlDecode($payloadEncoded),
            true
        );

        if (!$header || !$payload) {
            return null;
        }

        $data = $headerEncoded . '.' . $payloadEncoded;

        $expectedSigniture = hash_hmac(
            'sha256',
            $data,
            config('jwt.secret'),
            true
        );
        $expectedSignitureEncoded = $this->base64UrlEncode($expectedSigniture);

        if (! hash_equals($expectedSignitureEncoded, $signatureEncoded)) {
            return null;
        }

        return $payload;
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;

        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(
            strtr($data, '-_', '+/')
        );
    }

    public function decode(String $token) {}
}
