<?php

namespace App\Controllers;

use App\Services\RoleService;
use App\Services\JWTService; // Dodano użycie JWTService do metody authenticate

class RolesController
{
    private RoleService $roleService;
    private JWTService $jwtService; // Dodano JWTService

    public function __construct(RoleService $roleService, JWTService $jwtService) // Dodano JWTService do konstruktora
    {
        $this->roleService = $roleService;
        $this->jwtService = $jwtService;
    }

     // Metoda do uwierzytelniania - skopiowana z innych kontrolerów
     private function authenticate(): ?object
    {
        $headers = getallheaders();
        if(!isset($headers['Authorization']))
        {
            http_response_code(401);
            echo json_encode(['error' => 'No authorization header']);

            return null;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $decoded = $this->jwtService->decodeToken($token);

        if(!$decoded)
        {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);

            return null;
        }

        return $decoded;
    }


    public function getAllRoles(): void
    {
        // Ta metoda powinna być chroniona (np. tylko dla admina)
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }
         // Opcjonalnie: Sprawdź rolę użytkownika z $userToken->role

        $roles = $this->roleService->getAllRoles();

        if (count($roles) > 0) {
            $response = array_map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            }, $roles);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'No roles found']);
        }
    }

    public function getRoleById(int $id): void
    {
        // Ta metoda powinna być chroniona (np. tylko dla admina)
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }
         // Opcjonalnie: Sprawdź rolę użytkownika z $userToken->role


        $role = $this->roleService->getRoleById($id);

        if ($role) {
            header('Content-Type: application/json');
            echo json_encode([
                'id' => $role->id,
                'name' => $role->name,
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Role not found']);
        }
    }

     public function createRole(): void
     {
         // Ta metoda powinna być chroniona (np. tylko dla admina)
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }
          // Opcjonalnie: Sprawdź rolę użytkownika z $userToken->role i zwróć 403 Forbidden jeśli nie jest adminem

         $data = json_decode(file_get_contents('php://input'), true);

         if (!isset($data['name']) || empty($data['name'])) {
             http_response_code(400);
             echo json_encode(['error' => 'Role name is required']);
             return;
         }

         try {
             $role = $this->roleService->createRole($data['name']);
             header('Content-Type: application/json');
             http_response_code(201);
             echo json_encode([
                'id' => $role->id,
                'name' => $role->name,
                'message' => 'Role created successfully'
             ]);
         } catch (\Exception $e) {
             http_response_code(500);
             echo json_encode(['error' => 'An error occurred while creating the role: ' . $e->getMessage()]);
         }
     }


    public function deleteRole(int $id): void
    {
         // Ta metoda powinna być chroniona (np. tylko dla admina)
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }
          // Opcjonalnie: Sprawdź rolę użytkownika z $userToken->role i zwróć 403 Forbidden jeśli nie jest adminem

        try {
            $status = $this->roleService->deleteRole($id);

            if ($status === 'deleted') {
                 http_response_code(200);
                 echo json_encode(['message' => 'Role deleted successfully']);
            } elseif ($status === 'users_updated') {
                 http_response_code(200); // Może inny kod statusu, np. 200 z informacją
                 echo json_encode(['message' => 'Role deleted, users assigned to default role']);
            } elseif ($status === 'not_found') {
                 http_response_code(404);
                 echo json_encode(['error' => 'Role not found']);
            } else {
                 http_response_code(500);
                 echo json_encode(['error' => 'An unexpected status was returned']);
            }
        } catch (\Exception $e) {
             http_response_code(500);
             echo json_encode(['error' => 'An error occurred while deleting the role: ' . $e->getMessage()]);
        }
    }
}