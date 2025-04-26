<?php

namespace App\Controller;

use App\Entity\Category;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/categories')]
class CategoryController extends AbstractController
{
    private CategoryService $categoryService;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(CategoryService $categoryService, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->categoryService = $categoryService;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('', name: 'api_categories_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getAllCategories();

        return $this->json($categories, 200, [], ['groups' => 'category:read']);
    }

    #[Route('/{id}', name: 'api_categories_show', methods: ['GET'])]
    public function show(Category $category): JsonResponse
    {
        return $this->json($category, 200, [], ['groups' => 'category:read']);
    }

    #[Route('', name: 'api_categories_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $category = $this->serializer->deserialize($request->getContent(), Category::class, 'json');

        $errors = $this->validator->validate($category);

        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $createdCategory = $this->categoryService->createCategory($category->getName());

        return $this->json($createdCategory, 201, [], ['groups' => 'category:read']);
    }

    #[Route('/{id}', name: 'api_categories_update', methods: ['PUT'])]
    public function update(Request $request, Category $category): JsonResponse
    {
        $updatedCategory = $this->serializer->deserialize($request->getContent(), Category::class, 'json', ['object_to_populate' => $category]);

        $errors = $this->validator->validate($updatedCategory);

        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $this->categoryService->updateCategory($category, $updatedCategory->getName());

        return $this->json($category, 200, [], ['groups' => 'category:read']);
    }

    #[Route('/{id}', name: 'api_categories_delete', methods: ['DELETE'])]
    public function delete(Category $category): JsonResponse
    {
        $this->categoryService->deleteCategory($category);

        return $this->json(null, 204);
    }
}