<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Services\JWTService;
use Exception;

class RegisterController
{
    private UserService $userService;
    private ?JWTService $jwtService; // JWTService jest opcjonalne

    public function __construct(UserService $userService, ?JWTService $jwtService = null) // JWTService opcjonalnie w konstruktorze
    {
        $this->userService = $userService;
        $this->jwtService = $jwtService;
    }

    public function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['username'], $data['email'], $data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Username, email, and password are required']);
            return;
        }

        $username = $data['username'];
        $email = $data['email'];
        $password = $data['password'];
        $roleId = $data['role_id'] ?? null;

        try {
            // Sprawdzenie, czy użytkownik o podanym emailu lub nazwie użytkownika już istnieje
            if ($this->userService->getUserByEmail($email)) {
                http_response_code(409);
                echo json_encode(['error' => 'User with this email already exists']);
                return;
            }
             if ($this->userService->getUserByUsername($username)) {
                http_response_code(409);
                echo json_encode(['error' => 'User with this username already exists']);
                return;
            }

            // Tworzenie użytkownika za pomocą serwisu
            $user = $this->userService->createUser($username, $email, $password, $roleId);

            // Jeśli JWTService jest dostępny, możesz opcjonalnie wygenerować token po rejestracji
            if ($this->jwtService) {
                 // Pobranie aktualnej roli użytkownika po utworzeniu (jeśli rola nie została pobrana w createUser)
                 // Zakładając, że createUser zwraca pełny obiekt User z rolą:
                 $roleName = $user && $user->role ? $user->role->name : 'ROLE_USER';
                 $token = $this->jwtService->generateToken($user->id, $user->username, $roleName);
                 header('Content-Type: application/json');
                 http_response_code(201);
                 echo json_encode([
                    'message' => 'User registered successfully',
                    'user_id' => $user->id,
                    'token' => $token // Zwracamy token
                ]);
            } else {
                // Jeśli JWTService nie jest dostępny
                header('Content-Type: application/json');
                http_response_code(201);
                echo json_encode([
                    'message' => 'User registered successfully',
                    'user_id' => $user->id
                ]);
            }


        } catch (\InvalidArgumentException $e) {
             http_response_code(400);
             echo json_encode(['error' => $e->getMessage()]);
        }
        catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred during registration: ' . $e->getMessage()]);
        }
    }
}