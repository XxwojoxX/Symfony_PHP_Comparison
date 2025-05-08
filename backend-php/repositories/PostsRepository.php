<?php

namespace App\Repositories;

use App\Entities\Posts;
use App\Entities\Users;
use App\Entities\Category;
use PDO;
use PDOStatement;
use DateTime;

class PostsRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllPosts(?int $limit = null): array
    {
        $sql = "SELECT p.*, u.id as user_id, u.username, u.email, c.id as category_id, c.name as category_name, c.slug as category_slug
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN category c ON p.category_id = c.id
                ORDER BY p.created_at DESC";

        if($limit !== null)
        {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $posts = [];
        foreach($rows as $row)
        {
            $posts[] = $this->mapRowToPost($row);
        }

        return $posts;
    }

    public function findPostById(int $id): ?Posts
    {
        $stmt = $this->pdo->prepare
            (
                "SELECT p.*, u.id as user_id, u.username, u.email, c.id as category_id, c.name as category_name, c.slug as category_slug
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN category c ON p.category_id = c.id
                WHERE p.id = :id"
            );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToPost($row) : null;
    }

    public function findPostsByUserId(int $userId): array
    {
        $sql = "SELECT p.*, u.id as user_id, u.username, u.email, c.id as category_id, c.name as category_name, c.slug as category_slug
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN category c ON p.category_id = c.id
                WHERE p.user_id = :userId
                ORDER BY p.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $posts = [];
        foreach($rows as $row)
        {
            $posts[] = $this->mapRowToPost($row);
        }

        return $posts;
    }

    public function savePost(Posts $post): void
    {
        if ($post->id === null) {
            // Insert
            $stmt = $this->pdo->prepare(
                "INSERT INTO posts (title, user_id, category_id, slug, content, created_at, updated_at, published_at, image_name)
                VALUES (:title, :user_id, :category_id, :slug, :content, :created_at, :updated_at, :published_at, :image_name)"
            );
            $stmt->execute([
                'title' => $post->title,
                'user_id' => $post->user->id,
                'category_id' => $post->category->id,
                'slug' => $post->slug,
                'content' => $post->content,
                'created_at' => $post->created_at ? $post->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $post->updated_at ? $post->updated_at->format('Y-m-d H:i:s') : null,
                'published_at' => $post->published_at ? $post->published_at->format('Y-m-d H:i:s') : null,
                'image_name' => $post->image_name,
            ]);
            $post->id = $this->pdo->lastInsertId();
        } else {
            // Update
             $stmt = $this->pdo->prepare(
                "UPDATE posts SET title = :title, category_id = :category_id, slug = :slug, content = :content, updated_at = :updated_at, published_at = :published_at, image_name = :image_name WHERE id = :id"
            );
            $stmt->execute([
                'id' => $post->id,
                'title' => $post->title,
                'category_id' => $post->category->id,
                'slug' => $post->slug,
                'content' => $post->content,
                'updated_at' => $post->updated_at ? $post->updated_at->format('Y-m-d H:i:s') : null,
                'published_at' => $post->published_at ? $post->published_at->format('Y-m-d H:i:s') : null,
                'image_name' => $post->image_name,
            ]);
        }
    }


    public function deletePost(Posts $post): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $post->id]);
    }

    public function deletePostsByIds(array $postIds): void
    {
        if (empty($postIds)) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id IN ($placeholders)");
        $stmt->execute($postIds);
    }

    private function mapRowToPost(array $row): Posts
    {
        $post = new Posts();
        $post->id = $row['id'];
        $post->title = $row['title'];
        $post->content = $row['content'];
        $post->slug = $row['slug'];
        $post->image_name = $row['image_name'];

        $post->created_at = $row['created_at'] ? new DateTime($row['created_at']) : null;
        $post->updated_at = $row['updated_at'] ? new DateTime($row['updated_at']) : null;
        $post->published_at = $row['published_at'] ? new DateTime($row['published_at']) : null;

        // Mapowanie użytkownika
        if(isset($row['user_id'])) {
            $user = new Users();
            $user->id = $row['user_id'];
            $user->username = $row['username'] ?? null;
            $user->email = $row['email'] ?? null;
            $post->user = $user;
        }

        // Mapowanie kategorii
         if(isset($row['category_id'])) {
            $category = new Category();
            $category->id = $row['category_id'];
            $category->name = $row['category_name'] ?? null;
            $category->slug = $row['category_slug'] ?? null;
            $post->category = $category;
        }

        return $post;
    }
}