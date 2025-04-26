<?php

namespace App\Controller;

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

        if(!isset($data['username'], $data['password']))
        {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid Input']);

            return;
        }

        $user = $this->userService->getUserByName($data['username']);

        if(!$user || !password_verify($data['password'], $user->password))
        {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);

            return;
        }

        $token = $this->jwtService->generateToken($user->id, $user->username, $user->role->name);

        echo json_encode(['token' => $token]);
    }
}