<?php

namespace App\Repositories;

use App\Entities\Category;
use PDO;
use PDOStatement;

class CategoryRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllCategories(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM category");
        $categories = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $categories[] = $this->mapRowToCategory($row);
        }

        return $categories;
    }

    public function findCategoryById(int $id): ?Category
    {
        $stmt = $this->pdo->prepare("SELECT * FROM category WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$row)
        {
            return null;
        }

        return $this->mapRowToCategory($row);
    }

    public function createCategory(string $name, string $slug): Category
    {
        $stmt = $this->pdo->prepare("INSERT INTO category (name, slug) VALUES (:name, :slug)");
        $stmt->execute(['name' => $name, 'slug' => $slug]);

        $category = new Category();
        $category->id = $this->pdo->lastInsertId();
        $category->name = $name;
        $category->slug = $slug;

        return $category;
    }

    public function updateCategory(Category $category): void
    {
        $stmt = $this->pdo->prepare("UPDATE category SET name = :name, slug = :slug WHERE id = :id");
        $stmt->execute([
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug
        ]);
    }

    public function deleteCategory(Category $category): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM category WHERE id = :id");
        $stmt->execute(['id' => $category->id]);
    }

    private function mapRowToCategory(array $row): Category
    {
        $category = new Category();
        $category->id = $row['id'];
        $category->name = $row['name'];
        $category->slug = $row['slug'];
        return $category;
    }
}