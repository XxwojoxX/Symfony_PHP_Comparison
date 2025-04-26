<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\PostService;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Posts;
use Symfony\Component\HttpFoundation\File\File;

#[Route('/api/posts')]
final class PostsController extends AbstractController
{
    private PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    # Zwróć wszystkie posty
    #[Route('', name: 'app_posts_index', methods: ['GET'])]
    public function getAllPosts(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 0);

        $posts = $this->postService->getAllPosts($limit > 0 ? $limit : null);

        if(count($posts) > 0)
        {
            $postData = [];

            foreach($posts as $post)
            {
                $postData[] = 
                [
                    'id' => $post->getId(),
                    'title' => $post->getTitle(),
                    'content' => $post->getContent(),
                    'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $post->getUpdatedAt() ? $post->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                ];
            }

            return new JsonResponse($postData, JsonResponse::HTTP_OK);
        }

        return new JsonResponse(['error' => 'No posts found'], JsonResponse::HTTP_NOT_FOUND);
    }

    # Zwróć post po id
    #[Route('/{id}', name: 'get_post_show', methods: ['GET'])]
    public function getPostById(int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id);

        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        if($post)
        {
            $postData = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $post->getUpdatedAt() ? $post->getUpdatedAt()->format('Y-m-d H:i:s') : null,
                'image' => $post->getImageName(),
            ];

            return new JsonResponse($postData, JsonResponse::HTTP_OK);
        }

        return new JsonResponse(['error' => 'Post not found'], JsonResponse::HTTP_NOT_FOUND);
    }

    # Stwórz nowy post
    #[Route('/create', name: 'create_post_create', methods: ['POST'])]
    public function createPost(Request $request, SluggerInterface $slugger): JsonResponse
{
    $data = $request->request->all();
    $imageFile = $request->files->get('image');

    if (!isset($data['title']) || !isset($data['content']) || !isset($data['category_id'])) {
        return new JsonResponse(['error' => 'Invalid data: title, content and category_id are required'], JsonResponse::HTTP_BAD_REQUEST);
    }

    $user = $this->getUser();
    if (!$user) {
        return new JsonResponse(['error' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    $slug = $slugger->slug($data['title'])->lower();
    $post = new Posts();
    $post->setTitle($data['title']);
    $post->setContent($data['content']);
    $post->setSlug($slug);
    $post->setCreatedAt(new \DateTimeImmutable());
    $post->setUser($user);

    if ($imageFile) {
        $post->setImageFile($imageFile);
    }

    $this->postService->createPost($post, $data['category_id']);

    return $this->json(['status' => 'Post created', 'postId' => $post->getId()], JsonResponse::HTTP_CREATED);
}

    # Edytuj post
    #[Route('/update/{id}', name: 'update_post', methods: ['POST', 'PUT'])]
    public function updatePost(int $id, Request $request): JsonResponse
    {
        $post = $this->postService->getPostById($id);

        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = $request->request->all();
        $imageFile = $request->files->get('image');

        if (isset($data['title'])) {
            $post->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $post->setContent($data['content']);
        }

        if ($imageFile) {
            $post->setImageFile($imageFile);
        }

        $this->postService->updatePost($post);

        return new JsonResponse(['status' => 'Post updated'], JsonResponse::HTTP_OK);
    }

    # Usuń post
    #[Route('/delete/{id}', name: 'delete_post', methods: ['DELETE'])]
    public function deletePost(int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id);

        if(!$post)
        {
            return new JsonResponse(['error' => 'Post not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->postService->deletePost($post);

        return new JsonResponse(['status' => 'Post deleted'], JsonResponse::HTTP_OK);
    }
}
