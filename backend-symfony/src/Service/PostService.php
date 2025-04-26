<?php

namespace App\Service;

use App\Entity\Posts;
use App\Entity\Users;
use App\Repository\CategoryRepository;
use App\Repository\PostsRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;

class PostService
{
    private PostsRepository $postsRepository;
    private CategoryService $categoryService;
    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(PostsRepository $postsRepository, CategoryService $categoryService, Security $security, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->postsRepository = $postsRepository;
        $this->categoryService = $categoryService;
        $this->security = $security;
    }

    public function getAllPosts(?int $limit = null): array
    {
        return $this->postsRepository->findAllPosts($limit);
    }

    public function getPostById(int $id): ?Posts
    {
        return $this->postsRepository->findPostById($id);
    }

    public function createPost(Posts $post, int $categoryId): Posts
    {
        $category = $this->categoryService->getCategoryById($categoryId);

        if (!$category) {
            throw new NotFoundHttpException(sprintf('Category with id "%s" not found', $categoryId));
        }

        $post->setCategory($category);

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $post;
    }

    public function updatePost(Posts $post): void
    {
        $this->postsRepository->save($post);
    }

    public function deletePost(Posts $post): void
    {
        $this->postsRepository->remove($post);
    }
}