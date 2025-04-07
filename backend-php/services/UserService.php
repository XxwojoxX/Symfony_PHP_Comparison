<?php

require_once 'repositories/RolesRepository.php';
require_once 'repositories/UsersRepository.php';

class UserService
{
    private UsersRepository $usersRepository;
    private RolesRepository $rolesRepository;

    public function __construct(UsersRepository $usersRepository, RolesRepository $rolesRepository)
    {
        $this->usersRepository = $usersRepository;
        $this->rolesRepository = $rolesRepository;
    }

    public function getAllUsers(?int $limit = null): array
    {
        return $this->usersRepository->findAllUsers($limit);
    }

    public function getUserById(int $id): Users
    {
        return $this->usersRepository->findUserById($id);
    }

    public function createUser(string $username, string $email, string $password, int $roleId): void
    {
        $role = $this->rolesRepository->findRoleById($roleId);

        if(!$role) {
            throw new Exception("Role not found.");
        }

        $user = new Users();
        $user->username = $username;
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_BCRYPT);
        $user->created_at = new DateTime();
        $user->role = $role;

        $this->usersRepository->saveUser($user);
    }

    public function updateUser(int $id, string $username, string $email, string $password, int $roleId): void
    {
        $user = $this->usersRepository->findUserById($id);

        if(!$user)
        {
            throw new Exception("User not found.");
        }

        $role = $this->rolesRepository->findRoleById($roleId);

        if(!$role) {
            throw new Exception("Role not found.");
        }

        $user->username = $username;
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_BCRYPT);
        $user->role = $role;

        $this->usersRepository->updateUser($user);
    }

    public function deleteUser(int $id): void
    {
        $user = $this->usersRepository->findUserById($id);

        if($user)
        {
            $this->usersRepository->deleteUser($user);
        }
        else
        {
            throw new Exception("User not found.");
        }
    }
}