<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\RoleService;

final class RolesController extends AbstractController
{
    private $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    #[Route('/roles', name: 'app_roles')]
    public function getAllRoles(): JsonResponse
    {
        $roles = $this->roleService->getAllRoles();

        if(count($roles) > 0)
        {
            $rolesData = [];

            foreach($roles as $role)
            {
                $rolesData[] = 
                [
                    'id' => $role->getId(),
                    'name' => $role->getName()
                ];
            }

            return new JsonResponse($rolesData, JsonResponse::HTTP_OK);
        }

        return new JsonResponse(['error' => 'No roles found'], JsonResponse::HTTP_NOT_FOUND);
    }

    #[Route('/roles/{id}', name: 'get_role', methods: ['GET'])]
    public function getRoleById(int $id): JsonResponse
    {
        $role = $this->roleService->getRoleById($id);

        if($role)
        {
            $roleData = [
                'id' => $role->getId(),
                'name' => $role->getName()
            ];

            return new JsonResponse($roleData, JsonResponse::HTTP_OK);
        }

        return new JsonResponse(['error' => 'Role not found'], JsonResponse::HTTP_NOT_FOUND);
    }

    #[Route('/roles/create', name: 'create_role', methods: ['POST'])]
    public function createRole(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if(!isset($data['name']) || empty($data['name']))
        {
            return new JsonResponse(['error' => 'Role name is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->roleService->createRole($data['name']);

        return new JsonResponse(['message' => 'Role created successfully'], JsonResponse::HTTP_CREATED);
    }

    #usun role
    #[Route('/roles/delete/{id}', name: 'delete_role', methods: ['DELETE'])]
    public function deleteRole(int $id): JsonResponse
    {
        // Wywołaj metodę z RoleService, która zwraca odpowiedni wynik
        $result = $this->roleService->deleteRole($id);

        // Sprawdzamy wynik i zwracamy odpowiedni komunikat
        switch ($result) {
            case "not_found":
                return new JsonResponse(['error' => 'Role not found'], JsonResponse::HTTP_NOT_FOUND);
            
            case "users_updated":
                return new JsonResponse(['message' => 'Role deleted, affected users were assigned a default role'], JsonResponse::HTTP_OK);
            
            case "deleted":
                return new JsonResponse(['message' => 'Role deleted successfully'], JsonResponse::HTTP_OK);

            // Jeśli wynik nie pasuje do żadnej z powyższych opcji, zwróć błąd
            default:
                return new JsonResponse(['error' => 'An error occurred while deleting the role'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
