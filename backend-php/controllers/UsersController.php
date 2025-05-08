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

    public function getAllUsers(): void
    {
        // Ta metoda powinna być chroniona (np. tylko dla admina)
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }
         // Opcjonalnie: Sprawdź rolę użytkownika z $userToken->role i zwróć 403 Forbidden jeśli nie jest adminem

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
        $users = $this->userService->getAllUsers($limit);

        // Usuwamy hasło przed zwróceniem danych użytkowników
        $response = array_map(function($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
                 'role_id' => $user->role ? $user->role->id : null,
                 'role_name' => $user->role ? $user->role->name : null,
            ];
        }, $users);

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getUserById(int $id): void
    {
        // Ta metoda powinna być chroniona (użytkownik może pobrać swoje dane lub admin dane innych)
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }
         // Opcjonalnie: Sprawdź czy $userToken->sub == $id lub czy użytkownik ma rolę admina, w przeciwnym razie zwróć 403 Forbidden


        $user = $this->userService->getUserById($id);

        if ($user) {
             // Usuń hasło przed zwróceniem danych
             $response = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
                 'role_id' => $user->role ? $user->role->id : null,
                 'role_name' => $user->role ? $user->role->name : null,
             ];
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        }
    }

    // Metoda createUser została przeniesiona do RegisterController

    public function updateUser(int $id): void
    {
        // Ta metoda powinna być chroniona (użytkownik może edytować swoje dane lub admin dane innych)
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }
         // Opcjonalnie: Sprawdź czy $userToken->sub == $id lub czy użytkownik ma rolę admina, w przeciwnym razie zwróć 403 Forbidden

        $data = json_decode(file_get_contents('php://input'), true);

        try {
             $user = $this->userService->getUserById($id);
             if(!$user) {
                 http_response_code(404);
                 echo json_encode(['error' => 'User not found']);
                 return;
             }

            $this->userService->updateUser($user, $data);

             http_response_code(200);
             echo json_encode(['message' => 'User updated successfully']);

        } catch (\InvalidArgumentException $e) {
             http_response_code(400);
             echo json_encode(['error' => $e->getMessage()]);
        }
        catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred while updating the user: ' . $e->getMessage()]);
        }
    }

    public function deleteUser(int $id): void
    {
        // Ta metoda powinna być chroniona (użytkownik może usunąć swoje konto lub admin dane innych)
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }
          // Opcjonalnie: Sprawdź czy $userToken->sub == $id lub czy użytkownik ma rolę admina, w przeciwnym razie zwróć 403 Forbidden


        try {
            $this->userService->deleteUser($id);
            http_response_code(200);
            echo json_encode(['message' => 'User deleted successfully']);

        } catch (\Exception $e) {
             http_response_code(500);
             echo json_encode(['error' => 'An error occurred while deleting the user: ' . $e->getMessage()]);
        }
    }
}