<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{
    private string $secretKey;
    private string $issuer;
    private int $expirationTime;

    public function __construct(string $secretKey, string $issuer, int $expirationTime = 3600)
    {
        $this->secretKey = $secretKey;
        $this->issuer = $issuer;
        $this->expirationTime = $expirationTime;
    }

    public function generateToken(int $userId, string $username, string $role): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $this->expirationTime;
        $payload = 
        [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'iss' => $this->issuer,
            'sub' => $userId,
            'username' => $username,
            'role' => $role
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function decodeToken(string $jwt)
    {
        try
        {
            return JWT::decode($jwt, $this->secretKey, ['HS256']);
        }
        catch(\Exception $e)
        {
            return null;
        }
    }

    public function isValidToken(string $jwt): bool
    {
        return $this->decodeToken($jwt) !== null;
    }
}