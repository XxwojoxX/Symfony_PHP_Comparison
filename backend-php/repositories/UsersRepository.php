<?php

namespace App\Repositories;

use App\Entities\Users;
use App\Entities\Roles;
use PDO;
use DateTime;
use Exception;

class UsersRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllUsers(?int $limit = null): array
    {
        $sql = "SELECT u.*, r.id as role_id, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                ORDER BY u.id ASC";

        if($limit !== null)
        {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach($rows as $row)
        {
            $users[] = $this->mapRowToUser($row);
        }

        return $users;
    }

    public function findUserById(int $id): ?Users
    {
        $stmt = $this->pdo->prepare(
                "SELECT u.*, r.id as role_id, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = :id"
            );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findUserByUsername(string $username): ?Users
    {
         $stmt = $this->pdo->prepare(
            "SELECT u.*, r.id as role_id, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.username = :username"
        );
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }

     public function findUserByEmail(string $email): ?Users
    {
        $stmt = $this->pdo->prepare(
            "SELECT u.*, r.id as role_id, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.email = :email"
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToUser($row) : null;
    }


    public function findUsersByRole(Roles $role): array // Zmieniono nazwę na findUsersByRole
    {
        $stmt = $this->pdo->prepare(
            "SELECT u.*, r.id as role_id, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.role_id = :role_id"
        );
        $stmt->execute(['role_id' => $role->id]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
        foreach($rows as $row)
        {
            $users[] = $this->mapRowToUser($row);
        }

        return $users;
    }

    public function saveUser(Users $user): void
    {
         if ($user->id === null) {
            // Insert
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (username, email, password, created_at, role_id)
                VALUES (:username, :email, :password, :created_at, :role_id)"
            );
            $stmt->execute([
                'username' => $user->username,
                'email' => $user->email,
                'password' => $user->password, // Usunięto podwójne haszowanie
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'role_id' => $user->role->id
            ]);
             $user->id = $this->pdo->lastInsertId();
         } else {
             // Update
             $stmt = $this->pdo->prepare(
                "UPDATE users SET username = :username, email = :email, password = :password, role_id = :role_id WHERE id = :id"
            );
            $stmt->execute([
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'password' => $user->password, // Usunięto podwójne haszowanie
                'role_id' => $user->role->id
            ]);
         }
    }


    public function deleteUser(Users $user): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $user->id]);
    }

    private function mapRowToUser(array $row): Users
    {
        $user = new Users();
        $user->id = $row['id'];
        $user->username = $row['username'];
        $user->email = $row['email'];
        $user->password = $row['password']; // Pobieramy zahashowane hasło
        $user->created_at = new DateTime($row['created_at']);
        if(isset($row['role_id']))
        {
            $role = new Roles();
            $role->id = $row['role_id'];
            $role->name = $row['role_name'] ?? null; // Teraz role_name powinno być dostępne z JOINa
            $user->role = $role;
        }

        return $user;
    }
}