<?php

namespace App\Service;

use App\Repository\UsersRepository;
use App\Entity\Users;
use App\Entity\Roles;
use App\Repository\RolesRepository;

class UserService
{
    private $usersRepository;
    private $rolesRepository;

    public function __construct(UsersRepository $usersRepository, RolesRepository $rolesRepository)
    {
        $this->rolesRepository = $rolesRepository;
        $this->usersRepository = $usersRepository;
    }

    // Pobierz wszystkich użytkowników
    public function getAllUsers(?int $limit = null): array
    {
        return $this->usersRepository->findAllUsers($limit);
    }

    // Pobierz użytkownika po id
    public function getUserById(int $id): ?Users
    {
        return $this->usersRepository->findUserById($id);
    }

    // Pobierz użytkownika po nazwie użytkownika
    public function getUserByUsername(string $username): ?Users
    {
        return $this->usersRepository->findUserByUsername($username);
    }

    // Stwórz nowego użytkownika
    public function createUser(string $username, string $email, string $password, ?int $roleId = null): Users
    {
        if(!$roleId)
        {
            $roleId = 2;
        }

        $role = $this->rolesRepository->findRoleById($roleId);

        if(!$role)
        {
            throw new \Exception('Role not found.');
        }

        $user = new Users();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setRole($role);

        $this->usersRepository->saveUser($user);

        return $user;
    }

    // Edytuj użytkownika
    public function updateUser(Users $user, array $data): void
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('No data provided.');
        }

        if (isset($data['username']) && !empty($data['username'])) {
            $user->setUsername($data['username']);
        }

        if (isset($data['email']) && !empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Invalid email format.');
            }
            $user->setEmail($data['email']);
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT)); // Haszowanie hasła
        }

        $this->usersRepository->updateUser($user);
    }

    // Usuń użytkownika
    public function deleteUser(Users $user): void
    {
        $this->usersRepository->deleteUser($user);
    }
}
