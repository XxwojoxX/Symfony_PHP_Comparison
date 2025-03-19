<?php

namespace App\Service;

use App\Repository\UsersRepository;
use App\Entity\Users;

class UserService
{
    private $usersRepository;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
    }

    // Pobierz wszystkich użytkowników
    public function getAllUsers(): array
    {
        return $this->usersRepository->findAllUsers();
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
    public function createUser(string $username, string $email, string $password): Users
    {
        return $this->usersRepository->createUser($username, $email, $password);
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
