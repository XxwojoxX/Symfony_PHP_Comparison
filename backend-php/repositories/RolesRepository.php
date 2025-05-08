<?php

namespace App\Repositories;

use App\Entities\Roles;
use App\Entities\Users; // Dodano użycie Users do metody findUserByRole
use PDO;
use PDOStatement;

class RolesRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllRoles(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM roles");
        $roles = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $roles[] = $this->mapRowToRole($row);
        }

        return $roles;
    }

    public function findRoleById(int $id): ?Roles
    {
        $stmt = $this->pdo->prepare("SELECT * FROM roles WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$row)
        {
            return null;
        }

        return $this->mapRowToRole($row);
    }

    public function createRole(string $name): Roles
    {
        $stmt = $this->pdo->prepare("INSERT INTO roles (name) VALUES (:name)");
        $stmt->execute(['name' => $name]);

        $role = new Roles();
        $role->id = $this->pdo->lastInsertId();
        $role->name = $name;

        return $role;
    }

    // Metoda findUserByRole została usunięta, ponieważ powinna być w UsersRepository lub UserService

     public function deleteRole(Roles $role): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM roles WHERE id = :id");
        $stmt->execute(['id' => $role->id]);
    }


    private function mapRowToRole(array $row): Roles
    {
        $role = new Roles();
        $role->id = $row['id'];
        $role->name = $row['name'];
        return $role;
    }
}