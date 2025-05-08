<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key; // Dodaj ten use (choć Key może działać inaczej w Twojej wersji encode/decode)
use stdClass; // Dodaj ten use

class JWTService
{
    private string $secretKey;
    private string $serverName;

    public function __construct(string $secretKey, string $serverName)
    {
        $this->secretKey = $secretKey;
        $this->serverName = $serverName;
    }

    public function generateToken(int $userId, string $username, string $role): string
    {
        // Ten kod powinien być poprawny dla encode HS256 w v6+
        $issuedAt = new \DateTimeImmutable();
        $expire = $issuedAt->modify('+60 minutes')->getTimestamp();
        $serverName = $this->serverName;

        $data = [
            'iat'  => $issuedAt->getTimestamp(),
            'iss'  => $serverName,
            'nbf'  => $issuedAt->getTimestamp(),
            'exp'  => $expire,
            'sub'  => $userId,
            'username' => $username,
            'role' => $role
        ];

        return JWT::encode(
            $data,
            $this->secretKey, // Drugi argument: String klucza sekretnego
            'HS256' // Trzeci argument w encode: Algorytm (string)
        );
    }

    public function decodeToken(string $token): ?object
    {
        $secretKey = $this->secretKey;
        // $allowedAlgorithms = ['HS256']; // Ta zmienna NIE jest trzecim argumentem w Twojej wersji decode

        try {
            // --- DEBUGOWANIE PRZED JWT::decode ---
            //error_log("DEBUG: Decoding token process started.");
            //error_log("DEBUG: Token string type: " . gettype($token) . ", value: " . $token);
            //error_log("DEBUG: Secret key type: " . gettype($secretKey) . ", value: " . $secretKey);
            // --- DEBUGOWANIE PRZED JWT::decode ---


            // Poprawione wywołanie JWT::decode() ZGODNE Z TWOJĄ SYGNATURĄ:
            // decode(string $jwt, $keyOrKeyArray, ?stdClass &$headers = null): stdClass
            $headers = new stdClass(); // Zmienna do przekazania przez referencję, oczekiwany typ ?stdClass
            $decoded = JWT::decode(
                $token,
                new Key($secretKey, 'HS256'), // Drugi argument: obiekt Key (jak w przykładzie z GitHub)
                $headers    // Trzeci argument: ?stdClass &$headers = null (przekazujemy zmienną stdClass przez referencję)
                // Algorytmy nie są przekazywane jako oddzielny argument w tym wariancie użycia decode
            );

             // Opcjonalna walidacja emitenta
             if (isset($decoded->iss) && $decoded->iss !== $this->serverName) {
                 //error_log("JWT Error: Invalid issuer - Expected " . $this->serverName . " but got " . $decoded->iss);
                 return null;
             }

            return $decoded;

        } catch (\Firebase\JWT\ExpiredException $e) {
             //error_log("JWT Error: Token expired - " . $e->getMessage());
            return null;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
              //error_log("JWT Error: Invalid signature - " . $e->getMessage()); // Ten błąd może się pojawić teraz, bo nie podajemy algorytmów
            return null;
        } catch (\UnexpectedValueException $e) {
              //error_log("JWT Error: Unexpected value - " . $e->getMessage());
            return null;
        } catch (\DomainException $e) {
               //error_log("JWT Error: Domain error - " . $e->getMessage());
            return null;
        } catch (\InvalidArgumentException $e) {
               //error_log("JWT Error: Invalid argument to decode - " . $e->getMessage());
            return null;
        } catch (\Throwable $e) {
             //error_log("JWT Error: An unexpected error occurred during decoding - " . $e->getMessage() . " called in " . $e->getFile() . " on line " . $e->getLine());
             return null;
        }
    }
}