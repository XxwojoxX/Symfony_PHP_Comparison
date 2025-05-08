<?php

namespace App\Controllers;

use App\Services\CategoryService;
use App\Services\JWTService; // Dodano użycie JWTService do metody authenticate

class CategoryController
{
    private CategoryService $categoryService;
    private JWTService $jwtService; // Dodano JWTService

    public function __construct(CategoryService $categoryService, JWTService $jwtService) // Dodano JWTService do konstruktora
    {
        $this->categoryService = $categoryService;
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


    public function index(): void
    {
        // Opcjonalnie: Sprawdź uwierzytelnienie, jeśli ta metoda ma być chroniona
        // $userToken = $this->authenticate();
        // if (!$userToken) {
        //     return; // Odpowiedź 401 została już wysłana
        // }

        $categories = $this->categoryService->getAllCategories();

        if (count($categories) > 0) {
            $response = array_map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ];
            }, $categories);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'No categories found']);
        }
    }

    public function show(int $id): void
    {
         // Opcjonalnie: Sprawdź uwierzytelnienie, jeśli ta metoda ma być chroniona
        // $userToken = $this->authenticate();
        // if (!$userToken) {
        //     return; // Odpowiedź 401 została już wysłana
        // }

        $category = $this->categoryService->getCategoryById($id);

        if ($category) {
            header('Content-Type: application/json');
            echo json_encode([
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Category not found']);
        }
    }

    public function create(): void
    {
        // Ta metoda powinna być chroniona
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['name']) || empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Category name is required']);
            return;
        }

        try {
            $category = $this->categoryService->createCategory($data['name']);
            header('Content-Type: application/json');
            http_response_code(201);
             echo json_encode([
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'message' => 'Category created successfully'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred while creating the category: ' . $e->getMessage()]);
        }
    }

     public function update(int $id): void
    {
        // Ta metoda powinna być chroniona
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['name']) || empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Category name is required']);
            return;
        }

        try {
            $category = $this->categoryService->updateCategory($id, $data['name']);
             if ($category) {
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode([
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'message' => 'Category updated successfully'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Category not found']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred while updating the category: ' . $e->getMessage()]);
        }
    }

    public function delete(int $id): void
    {
        // Ta metoda powinna być chroniona
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }

        try {
            $deleted = $this->categoryService->deleteCategory($id);
            if ($deleted) {
                http_response_code(204); // No Content
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Category not found']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred while deleting the category: ' . $e->getMessage()]);
        }
    }
}