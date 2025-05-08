<?php

namespace App\Services;

use App\Repositories\RolesRepository;
use App\Repositories\UsersRepository;
use App\Entities\Roles;
use Exception;

class RoleService
{
    private RolesRepository $rolesRepository;
    private UsersRepository $usersRepository;

    public function __construct(RolesRepository $rolesRepository, UsersRepository $usersRepository)
    {
        $this->rolesRepository = $rolesRepository;
        $this->usersRepository = $usersRepository;
    }

    public function getAllRoles(): array
    {
        return $this->rolesRepository->findAllRoles();
    }

    public function getRoleById(int $id): ?Roles
    {
        return $this->rolesRepository->findRoleById($id);
    }

    public function createRole(string $name): Roles
    {
        return $this->rolesRepository->createRole($name);
    }

    public function deleteRole(int $roleId): string
    {
        $role = $this->rolesRepository->findRoleById($roleId);
        if (!$role)
        {
            return 'not_found';
        }

        $usersWithRole = $this->usersRepository->findUsersByRole($role); // Użyj poprawionej nazwy metody w UsersRepository
        if (!empty($usersWithRole))
        {
            $defaultRole = $this->rolesRepository->findRoleById(2); // ID 2 jako domyślna rola
            if (!$defaultRole)
            {
                throw new Exception("Default role not found!");
            }

            foreach ($usersWithRole as $user)
            {
                $user->role = $defaultRole;
                $this->usersRepository->updateUser($user);
            }

            $this->rolesRepository->deleteRole($role);

            return 'users_updated';
        }

        $this->rolesRepository->deleteRole($role);

        return 'deleted';
    }
}