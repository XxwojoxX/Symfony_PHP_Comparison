<?php

namespace App\Services;

use App\Repositories\CommentRepository;
use App\Repositories\PostsRepository;
use App\Repositories\UsersRepository;
use App\Entities\Comment;
use App\Entities\Posts;
use App\Entities\Users;
use Exception;
use DateTime;

class CommentService
{
    private CommentRepository $commentRepository;
    private PostsRepository $postsRepository;
    private UsersRepository $usersRepository;

    public function __construct(CommentRepository $commentRepository, PostsRepository $postsRepository, UsersRepository $usersRepository)
    {
        $this->commentRepository = $commentRepository;
        $this->postsRepository = $postsRepository;
        $this->usersRepository = $usersRepository;
    }

    public function getCommentsByPostId(int $postId): array
    {
        return $this->commentRepository->findCommentsByPostId($postId);
    }

    public function addComment(int $postId, string $content, int $userId): Comment
    {
        $post = $this->postsRepository->findPostById($postId);
        if (!$post) {
            throw new Exception("Post with ID {$postId} not found.");
        }

        $user = $this->usersRepository->findUserById($userId);
        if (!$user) {
            throw new Exception("User with ID {$userId} not found.");
        }

        $comment = new Comment();
        $comment->content = $content;
        $comment->post = $post;
        $comment->user = $user;
        $comment->created_at = new DateTime();
        $comment->updated_at = new DateTime();


        $this->commentRepository->saveComment($comment);

        return $comment;
    }

     public function updateComment(int $id, string $content): ?Comment
    {
        $comment = $this->commentRepository->findCommentById($id);

        if (!$comment) {
            return null;
        }

        $comment->content = $content;
        $comment->updated_at = new DateTime();

        $this->commentRepository->saveComment($comment);

        return $comment;
    }

    public function deleteComment(int $id): bool
    {
        $comment = $this->commentRepository->findCommentById($id);

        if (!$comment) {
            return false;
        }

        $this->commentRepository->deleteComment($comment);

        return true;
    }
}