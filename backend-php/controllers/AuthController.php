<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Services\JWTService;

class AuthController
{
    private UserService $userService;
    private JWTService $jwtService;

    public function __construct(UserService $userService, JWTService $jwtService)
    {
        $this->userService = $userService;
        $this->jwtService = $jwtService;
    }

    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        // Użyj metody uwierzytelniania z UserService (która używa email i hasło)
        $user = $this->userService->authenticateUser($data['email'], $data['password']);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

         // Sprawdź, czy rola użytkownika istnieje przed próbą dostępu do jej nazwy
        $roleName = $user->role ? $user->role->name : 'ROLE_USER';


        // Generuj token używając JWTService
        $token = $this->jwtService->generateToken($user->id, $user->username, $roleName);

        header('Content-Type: application/json');
        echo json_encode(['token' => $token]);
    }
}