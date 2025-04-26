<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Posts;
use App\Entity\Users;
use App\Repository\CommentRepository;
use App\Repository\PostsRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CommentService
{
    private CommentRepository $commentRepository;
    private PostsRepository $postsRepository;
    private AuthorizationCheckerInterface $authChecker;

    public function __construct(
        CommentRepository $commentRepository,
        PostsRepository $postsRepository,
        AuthorizationCheckerInterface $authChecker
    ) {
        $this->commentRepository = $commentRepository;
        $this->postsRepository = $postsRepository;
        $this->authChecker = $authChecker;
    }

    public function getCommentsByPostId(int $postId): array
    {
        return $this->commentRepository->findCommentsByPostId($postId);
    }

    public function addComment(int $postId, string $content, UserInterface $user): Comment
    {
        $post = $this->postsRepository->findPostById($postId);
        if (!$post) {
            throw new NotFoundHttpException(sprintf('Post with id "%s" not found', $postId));
        }

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setPost($post);
        $comment->setUser($user);
        $comment->setCreatedAt(new \DateTimeImmutable());

        $this->commentRepository->save($comment);

        return $comment;
    }

    public function deleteComment(int $postId, int $commentId, ?UserInterface $user): void
    {
        $comment = $this->commentRepository->findCommentByIdAndPostId($commentId, $postId);

        if (!$comment) {
            throw new NotFoundHttpException(sprintf('Comment with id "%s" not found for post "%s"', $commentId, $postId));
        }

        if ($user !== $comment->getUser() && !$this->authChecker->isGranted('ROLE_ADMIN')) {
            throw new \Exception('You do not have permission to delete this comment');
        }

        $this->commentRepository->remove($comment);
    }
}