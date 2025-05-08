<?php

namespace App\Services;

use App\Repositories\PostsRepository;
use App\Repositories\UsersRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CommentRepository;
use App\Entities\Posts;
use App\Entities\Users;
use App\Entities\Category;
use Exception;
use DateTime;
use App\Services\SluggerService;

class PostService
{
    private PostsRepository $postsRepository;
    private UsersRepository $usersRepository;
    private CategoryRepository $categoryRepository;
    private SluggerService $sluggerService;
    private CommentRepository $commentRepository;

    public function __construct(PostsRepository $postsRepository, UsersRepository $usersRepository, CategoryRepository $categoryRepository, SluggerService $sluggerService, CommentRepository $commentRepository)
    {
        $this->postsRepository = $postsRepository;
        $this->usersRepository = $usersRepository;
        $this->categoryRepository = $categoryRepository;
        $this->sluggerService = $sluggerService;
        $this->commentRepository = $commentRepository;
    }

    public function getAllPosts(?int $limit = null): array
    {
        return $this->postsRepository->findAllPosts($limit);
    }

    public function getPostById(int $id): ?Posts
    {
        return $this->postsRepository->findPostById($id);
    }

    public function createPost(string $title, string $content, int $userId, int $categoryId, ?string $image_name = null): Posts
    {
        $user = $this->usersRepository->findUserById($userId);
        if (!$user) {
            throw new Exception("User with ID {$userId} not found.");
        }

        $category = $this->categoryRepository->findCategoryById($categoryId);
        if (!$category) {
             throw new Exception("Category with ID {$categoryId} not found.");
        }

        $slug = $this->sluggerService->slug($title);

        $post = new Posts();
        $post->title = $title;
        $post->content = $content;
        $post->user = $user;
        $post->category = $category;
        $post->slug = $slug;
        $post->created_at = new DateTime();
        $post->updated_at = new DateTime();
        $post->image_name = $image_name;

        $this->postsRepository->savePost($post);

        return $post;
    }

    public function updatePost(int $id, array $data): ?Posts
    {
        $post = $this->postsRepository->findPostById($id);

        if (!$post) {
            return null;
        }

        if (isset($data['title'])) {
            $post->title = $data['title'];
            $post->slug = $this->sluggerService->slug($data['title']);
        }
        if (isset($data['content'])) {
            $post->content = $data['content'];
        }
        if (isset($data['category_id'])) {
            $category = $this->categoryRepository->findCategoryById($data['category_id']);
            if (!$category) {
                 throw new Exception("Category with ID {$data['category_id']} not found.");
            }
            $post->category = $category;
        }
         if (isset($data['image_name'])) {
            $post->image_name = $data['image_name'];
        }


        $post->updated_at = new DateTime();

        $this->postsRepository->savePost($post);

        return $post;
    }

    public function deletePost(int $id): bool
    {
        $post = $this->postsRepository->findPostById($id);

        if (!$post) {
            return false; // Post nie znaleziony
        }

        // ----- Logika usuwania powiązanych komentarzy -----

        // Znajdź wszystkie komentarze powiązane z tym postem
        $comments = $this->commentRepository->findCommentsByPostId($id);

        // Jeśli istnieją komentarze, usuń je
        if (!empty($comments)) {
            $commentIds = array_map(fn($comment) => $comment->id, $comments);
            $this->commentRepository->deleteCommentsByIds($commentIds); // Użyj metody usuwania wielu komentarzy
            // Lub usuwaj pojedynczo:
            // foreach ($comments as $comment) {
            //     $this->commentRepository->deleteComment($comment);
            // }
        }

        // ----- Koniec logiki usuwania powiązanych komentarzy -----

        // Teraz można bezpiecznie usunąć posta, ponieważ nie ma powiązanych komentarzy
        $this->postsRepository->deletePost($post);

        return true; // Post usunięty pomyślnie
    }
}