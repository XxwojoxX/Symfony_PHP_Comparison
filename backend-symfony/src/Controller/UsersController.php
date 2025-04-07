<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\UserService;

final class UsersController extends AbstractController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #znajdz wszystkich użytkowników
    #[Route('/users', name: 'app_users')]
    public function getAllUsers(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 0);

        $users = $this->userService->getAllUsers($limit > 0 ? $limit : null);

        if(count($users) > 0)
        {
            $userData = [];

            foreach($users as $user)
            {
                $userData[] = 
                [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                    'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                    'role' => $user->getRole() ? $user->getRole()->getName() : null,
                ];
            }

            return new JsonResponse($userData, JsonResponse::HTTP_OK);
        }

        return new JsonResponse(['error' => 'No users found'], JsonResponse::HTTP_NOT_FOUND);
    }

    #znajdz użytkownika po id
    #[Route('/users/{id}', name: 'get_user', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if($user)
        {
            $userData = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                'role' => $user->getRole() ? $user->getRole()->getName() : null,
            ];

            return new JsonResponse($userData, JsonResponse::HTTP_OK);
        }

        return new JsonResponse(['error' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
    }

    #znajdz użytkownika po nazwie użytkownika
    #[Route('/user/{username}', name: 'get_user_by_username', methods: ['GET'])]
    public function getUserByUsername(string $username): JsonResponse
    {
        $user = $this->userService->findUserByUsername($username);

        if($user)
        {
            $userData = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail()
            ];

            return new JsonResponse($userData, JsonResponse::HTTP_OK);
        }

        return new JsonResponse(['error' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
    }

    #stworz nowego użytkownika
    #[Route('/user/create', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if(!isset($data['username']) || !isset($data['email']) || !isset($data['password']))
        {
            return new JsonResponse(['error' => 'Invalid data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $roleId = $data['role_id'] ?? null;

        $this->userService->createUser($data['username'], $data['email'], $data['password'], $roleId);

        return new JsonResponse(['status' => 'User created'], JsonResponse::HTTP_CREATED);
    }

    #edytuj użytkownika
    #[Route('/user/update/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if(!$user)
        {
            return new JsonResponse(['error' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $this->userService->updateUser($user, $data);

        return new JsonResponse(['status' => 'User updated'], JsonResponse::HTTP_OK);
    }

    #usuń użytkownika
    #[Route('/user/delete/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if(!$user)
        {
            return new JsonResponse(['error' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->userService->deleteUser($user);

        return new JsonResponse(['status' => 'User deleted'], JsonResponse::HTTP_OK);
    }
}
