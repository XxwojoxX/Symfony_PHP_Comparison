<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Services\JWTService;
use Exception;

class UsersController
{
    private UserService $userService;
    private JWTService $jwtService;

    public function __construct(UserService $userService, JWTService $jwtService)
    {
        $this->userService = $userService;
        $this->jwtService = $jwtService;
    }

    private function authenticate(): ?object
    {
        $headers = getallheaders();

        if(!isset($headers['Authorization']))
        {
            http_response_code(401);
            echo json_encode(['error' => 'No authorization header']);

            return null;
        }

        $token = str_replace('Bearer', '', $headers['Authorization']);
        $decoded = $this->jwtService->decodeToken($token);

        if(!$decoded)
        {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);

            return null;
        }

        return $decoded;
    }

    public function getAllUsers(): void
    {
        $user = $this->authenticate();
        if(!$user)
        {
            return;
        }

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
        $users = $this->userService->getAllUsers($limit);

        if(count($users) > 0)
        {
            $response = array_map(function($user)
            {
                return
                [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                    'role' => $user->role?->name
                ];
            }, $users);

            echo json_encode($response);
        }
        else
        {
            http_response_code(404);
            echo json_encode(['error' => 'No users found']);
        }
    }

    public function getUserById($id): void
    {
        $user = $this->userService->getUserById($id);

        if($user)
        {
            echo json_encode([
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'role' => $user->role?->name
            ]);
        }
        else
        {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        }
    }

    public function createUser(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if(!isset($data['username'], $data['email'], $data['password']))
        {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input']);
            return;
        }

        $roleId = $data['role_id'] ?? 2;

        $this->userService->createUser($data['username'], $data['email'], $data['password'], $roleId);

        http_response_code(201);
        echo json_encode(['message' => 'User created successfully']);
    }

    public function updateUser($id): void
    {
        $user = $this->authenticate();
        if(!$user)
        {
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        try
        {
            $this->userService->updateUser($id, $data['username'], $data['email'], $data['password'], $data['role_id']);
            echo json_encode(['message' => 'User updated successfully']);
        }
        catch(Exception $e)
        {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        }
    }

    public function deleteUser($id): void
    {
        try
        {
            $this->userService->deleteUser($id);
            echo json_encode(['message' => 'User deleted successfully']);
        }
        catch(Exception $e)
        {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        }
    }
}