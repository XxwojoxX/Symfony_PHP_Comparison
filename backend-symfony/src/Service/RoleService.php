<?php

namespace App\Service;

use App\Repository\RolesRepository;
use App\Entity\Roles;
use App\Repository\UsersRepository;
use App\Entity\Users;

class RoleService
{
    private $rolesRepository;
    private $usersRepository;

    public function __construct(RolesRepository $rolesRepository, UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
        $this->rolesRepository = $rolesRepository;
    }

    public function getAllRoles(): array
    {
        return $this->rolesRepository->findAllRoles();
    }

    public function getRoleById(int $id): ?Roles
    {
        return $this->rolesRepository->find($id);
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
            throw new \Exception('not_found');
        }

        $usersWithRole = $this->usersRepository->findUserByRole($role);

        if (!empty($usersWithRole))
        {
            $defaultRole = $this->rolesRepository->findRoleById(2);

            if (!$defaultRole)
            {
                throw new \Exception("Default role not found!");
            }

            foreach ($usersWithRole as $user)
            {
                $user->setRole($defaultRole);
                $this->usersRepository->updateUser($user);
            }

            $this->rolesRepository->deleteRole($role);

            return 'users_updated';
        }

        $this->rolesRepository->deleteRole($role);

        return 'deleted';
    }
}