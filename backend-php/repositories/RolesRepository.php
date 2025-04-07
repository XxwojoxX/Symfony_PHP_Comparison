<?php

require_once 'entities/Roles.php';

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

        while($row = $stmt->fetch())
        {
            $role = new Roels();
            $role->id = $row['id'];
            $role->name = $row['name'];
            $roles[] = $role;
        }

        return $roles;
    }

    public function findRoleById(int $id): ?Roles
    {
        $stmt = $this->pdo->prepare("SELECT * FROM roles WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        
        if(!$row)
        {
            return null;
        }

        $role = new Roles();
        $role->id = $row['id'];
        $role->name = $row['name'];

        return $role;
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

    public function deleteRole(Roles $role): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM roles WHERE id = :id");
        $stmt->execute(['id' => $role->id]);
    }
}