<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/api/posts/{postId}/comments')]
class CommentController extends AbstractController
{
    private CommentService $commentService;
    private Security $security;

    public function __construct(CommentService $commentService, Security $security)
    {
        $this->commentService = $commentService;
        $this->security = $security;
    }

    #[Route('', name: 'api_comments_get', methods: ['GET'])]
    public function getCommentsForPost(int $postId): JsonResponse
    {
        $comments = $this->commentService->getCommentsByPostId($postId);
        $commentData = [];
        foreach ($comments as $comment) {
            $commentData[] = [
                'id' => $comment->getId(),
                'created_at' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
                'user_id' => $comment->getUser()->getId(),
                'content' => $comment->getContent(),
            ];
        }
        return $this->json($commentData);
    }

    #[Route('', name: 'api_comments_add', methods: ['POST'])]
    public function addCommentToPost(int $postId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['content']) || empty($data['content'])) {
            return $this->json(['error' => 'Content cannot be empty'], 400);
        }

        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $comment = $this->commentService->addComment($postId, $data['content'], $user);
        return $this->json(['id' => $comment->getId(), 'message' => 'Comment added successfully'], 201);
    }

    #[Route('/{id}', name: 'api_comments_delete', methods: ['DELETE'])]
    public function deleteComment(int $postId, int $id): JsonResponse
    {
        $user = $this->security->getUser();
        try {
            $this->commentService->deleteComment($postId, $id, $user);
            return $this->json(['message' => 'Comment deleted successfully']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 403);
        }
    }
}