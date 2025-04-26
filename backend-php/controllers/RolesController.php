<?php

namespace App\Controllers;

use App\Services\RoleService;
use Exception;

class RolesController
{
    private RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function getAllRoles(): void
    {
        $role = $this->roleService->getAllRoles();

        if(count($role) > 0)
        {
            $response = array_map(function($role)
            {
                return
                [
                    'id' => $role->id,
                    'name' => $role->name
                ];
            }, $role);

            echo json_encode($response);
        }
        else
        {
            http_response_code(404);
            echo json_encode(['error' => 'No roles found']);
        }
    }

    public function getRoleById($id): void
    {
        $role = $this->roleService->getRoleById($id);

        if($role)
        {
            echo json_encode([
                'id' => $role->id,
                'name' => $role->name
            ]);
        }
        else
        {
            http_response_code(404);
            echo json_encode(['error' => 'Role not found']);
        }
    }

    public function createRole(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if(!isset($data['name']))
        {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input']);
            return;
        }

        $this->roleService->createRole($data['name']);

        http_response_code(201);
        echo json_encode(['message' => 'Role created successfully']);
    }

    public function deleteRole($id): void
    {
        try
        {
            $this->rolesService->deleteRole($id);
            echo json_encode(['message' => 'Role deleted successfully']);
        }
        catch (Exception $e)
        {
            http_response_code(404);
            echo json_encode(['error' => 'Role not found']);
        }
    }
}