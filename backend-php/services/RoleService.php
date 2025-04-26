<?php

namespace App\Services;

use App\Repositories\RolesRepository;
use App\Entities\Roles;
use Exception;

class RoleService
{
    private RolesRepository $roleRepository;

    public function __construct(RolesRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function getAllRoles(): array
    {
        return $this->roleRepository->findAllRoles();
    }

    public function getRoleById(int $id): ?Roles
    {
        return $this->roleRepository->findRoleById($id);
    }

    public function createRole(string $name): Roles
    {
        return $this->roleRepository->createRole($name);
    }

    public function deleteRole(int $id): void
    {
        $role = $this->rolesRepository->findRoleById($id);

        if($role)
        {
            $this->rolesRepository->deleteRole($role);
        }
    }
}