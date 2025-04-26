<?php

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;

class CategoryService
{
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->find($id);
    }

    public function createCategory(Category $category): Category
    {
        $this->categoryRepository->save($category);
        return $category;
    }

    public function updateCategory(Category $category): Category
    {
        $this->categoryRepository->save($category);
        return $category;
    }

    public function deleteCategory(Category $category): void
    {
        $this->categoryRepository->remove($category);
    }
}