<?php

namespace App\Repositories;

use App\Entities\Comment;
use App\Entities\Users;
use App\Entities\Posts;
use PDO;
use PDOStatement;
use DateTime;

class CommentRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findCommentsByPostId(int $postId): array
    {
        $sql = "SELECT c.*, u.id as user_id, u.username, u.email
                FROM comment c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.post_id = :postId
                ORDER BY c.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['postId' => $postId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $comments = [];
        foreach($rows as $row)
        {
            $comments[] = $this->mapRowToComment($row);
        }

        return $comments;
    }

    public function findCommentByIdAndPostId(int $commentId, int $postId): ?Comment
    {
        $sql = "SELECT c.*, u.id as user_id, u.username, u.email
                FROM comment c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.id = :commentId AND c.post_id = :postId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['commentId' => $commentId, 'postId' => $postId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToComment($row) : null;
    }

     public function findCommentById(int $commentId): ?Comment
    {
        $sql = "SELECT c.*, u.id as user_id, u.username, u.email, p.id as post_id, p.title as post_title
                FROM comment c
                LEFT JOIN users u ON c.user_id = u.id
                LEFT JOIN posts p ON c.post_id = p.id
                WHERE c.id = :commentId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['commentId' => $commentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToComment($row) : null;
    }

    public function findCommentsByUserId(int $userId): array
    {
        $sql = "SELECT c.*, u.id as user_id, u.username, u.email
                FROM comment c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.user_id = :userId
                ORDER BY c.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $comments = [];
        foreach($rows as $row)
        {
            $comments[] = $this->mapRowToComment($row);
        }

        return $comments;
    }

    public function saveComment(Comment $comment): void
    {
        if ($comment->id === null) {
            // Insert
            $stmt = $this->pdo->prepare(
                "INSERT INTO comment (post_id, user_id, content, created_at, updated_at)
                VALUES (:post_id, :user_id, :content, :created_at, :updated_at)"
            );
            $stmt->execute([
                'post_id' => $comment->post->id,
                'user_id' => $comment->user->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at ? $comment->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $comment->updated_at ? $comment->updated_at->format('Y-m-d H:i:s') : null,
            ]);
            $comment->id = $this->pdo->lastInsertId();
        } else {
             // Update
            $stmt = $this->pdo->prepare(
                "UPDATE comment SET content = :content, updated_at = :updated_at WHERE id = :id"
            );
            $stmt->execute([
                'id' => $comment->id,
                'content' => $comment->content,
                'updated_at' => $comment->updated_at ? $comment->updated_at->format('Y-m-d H:i:s') : null,
            ]);
        }
    }

    public function deleteComment(Comment $comment): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM comment WHERE id = :id");
        $stmt->execute(['id' => $comment->id]);
    }

    public function deleteCommentsByIds(array $commentIds): void
     {
         if (empty($commentIds)) {
             return;
         }
         $placeholders = implode(',', array_fill(0, count($commentIds), '?'));
         $stmt = $this->pdo->prepare("DELETE FROM comment WHERE id IN ($placeholders)");
         $stmt->execute($commentIds);
     }

    private function mapRowToComment(array $row): Comment
    {
        $comment = new Comment();
        $comment->id = $row['id'];
        $comment->content = $row['content'];
        $comment->created_at = $row['created_at'] ? new DateTime($row['created_at']) : null;
        $comment->updated_at = $row['updated_at'] ? new DateTime($row['updated_at']) : null;

        // Mapowanie użytkownika
        if(isset($row['user_id'])) {
            $user = new Users();
            $user->id = $row['user_id'];
            $user->username = $row['username'] ?? null;
             $user->email = $row['email'] ?? null;
            $comment->user = $user;
        }

         // Mapowanie posta (tylko jeśli potrzebne w komentarzu)
        if(isset($row['post_id'])) {
            $post = new Posts();
            $post->id = $row['post_id'];
            $post->title = $row['post_title'] ?? null;
            $comment->post = $post;
        }

        return $comment;
    }
}