<?php

namespace App\Controllers;

use App\Services\CommentService;
use App\Services\JWTService;

class CommentController
{
    private CommentService $commentService;
    private JWTService $jwtService;

    public function __construct(CommentService $commentService, JWTService $jwtService)
    {
        $this->commentService = $commentService;
        $this->jwtService = $jwtService;
    }

    // Metoda do uwierzytelniania - skopiowana z innych kontrolerów
    private function authenticate(): ?object
    {
        $headers = getallheaders();
        if(!isset($headers['Authorization']))
        {
            http_response_code(401);
            echo json_encode(['error' => 'No authorization header']);

            return null;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $decoded = $this->jwtService->decodeToken($token);

        if(!$decoded)
        {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);

            return null;
        }

        return $decoded;
    }

    public function getCommentsForPost(int $postId): void
    {
         // Opcjonalnie: Sprawdź uwierzytelnienie, jeśli ta metoda ma być chroniona
        // $userToken = $this->authenticate();
        // if (!$userToken) {
        //     return; // Odpowiedź 401 została już wysłana
        // }

        $comments = $this->commentService->getCommentsByPostId($postId);

        $commentData = [];
        foreach ($comments as $comment) {
            $commentData[] = [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at ? $comment->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $comment->updated_at ? $comment->updated_at->format('Y-m-d H:i:s') : null,
                'user_id' => $comment->user ? $comment->user->id : null,
                'username' => $comment->user ? $comment->user->username : null,
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($commentData);
    }

    public function addCommentToPost(int $postId): void
    {
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['content']) || empty($data['content'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Content cannot be empty']);
            return;
        }

        try {
             // Zakładamy, że token JWT zawiera id użytkownika w 'sub'
            $userId = $userToken->sub ?? null;

             if (!$userId) {
                http_response_code(401); // lub 500 jeśli problem z tokenem po uwierzytelnieniu
                echo json_encode(['error' => 'User ID not found in token']);
                return;
            }

            $comment = $this->commentService->addComment($postId, $data['content'], $userId);

            header('Content-Type: application/json');
            http_response_code(201);
            echo json_encode(['id' => $comment->id, 'message' => 'Comment added successfully']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function deleteComment(int $postId, int $id): void
    {
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }

         // Opcjonalnie: Sprawdź uprawnienia użytkownika (czy to jego komentarz lub czy jest adminem)
         // Możesz pobrać komentarz i porównać user_id z $userToken->sub

        try {
            $deleted = $this->commentService->deleteComment($id);

            if ($deleted) {
                 http_response_code(200);
                 echo json_encode(['message' => 'Comment deleted successfully']);
            } else {
                 http_response_code(404);
                 echo json_encode(['error' => 'Comment not found']);
            }

        } catch (\Exception $e) {
            http_response_code(403); // Forbidden (np. brak uprawnień) lub 500
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}