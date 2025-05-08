<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use App\Entities\Category;
use Exception;
use App\Services\SluggerService;

class CategoryService
{
    private CategoryRepository $categoryRepository;
    private SluggerService $sluggerService;

    public function __construct(CategoryRepository $categoryRepository, SluggerService $sluggerService)
    {
        $this->categoryRepository = $categoryRepository;
        $this->sluggerService = $sluggerService;
    }

    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAllCategories();
    }

    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->findCategoryById($id);
    }

    public function createCategory(string $name): Category
    {
        $slug = $this->sluggerService->slug($name);

        $category = new Category();
        $category->name = $name;
        $category->slug = $slug;

        $this->categoryRepository->createCategory($name, $slug);

        return $category;
    }

    public function updateCategory(int $id, string $name): ?Category
    {
        $category = $this->categoryRepository->findCategoryById($id);

        if (!$category) {
            return null;
        }

        $slug = $this->sluggerService->slug($name);

        $category->name = $name;
        $category->slug = $slug;

        $this->categoryRepository->updateCategory($category);

        return $category;
    }

    public function deleteCategory(int $id): bool
    {
        $category = $this->categoryRepository->findCategoryById($id);

        if (!$category) {
            return false;
        }

        $this->categoryRepository->deleteCategory($category);

        return true;
    }
}