<?php

require_once '../vendor/autoload.php'; // Jeśli używasz Composera
require_once '../config/bootstrap.php';

use App\Services\JWTService;
use App\Services\UserService;
use App\Services\RoleService;
use App\Services\CategoryService;
use App\Services\PostService;
use App\Services\CommentService;
use App\Services\SluggerService;

use App\Repositories\UsersRepository;
use App\Repositories\RolesRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\PostsRepository;
use App\Repositories\CommentRepository;

use App\Controllers\UsersController;
use App\Controllers\RolesController;
use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\PostsController;
use App\Controllers\CommentController;
use App\Controllers\RegisterController; // Dodano użycie RegisterController


// Upewnij się, że $pdo jest dostępny globalnie
global $pdo;

// Repozytoria
$usersRepository = new UsersRepository($pdo);
$rolesRepository = new RolesRepository($pdo);
$categoryRepository = new CategoryRepository($pdo);
$postsRepository = new PostsRepository($pdo);
$commentRepository = new CommentRepository($pdo);

// Serwisy (zależności)
$sluggerService = new SluggerService();
$userService = new UserService($usersRepository, $rolesRepository, $postsRepository, $commentRepository); // Upewnij się, że CommentRepository jest przekazane
$roleService = new RoleService($rolesRepository, $usersRepository); // Upewnij się, że UsersRepository jest przekazane
$jwtService = new JWTService("twoj_sekretny_klucz", "http://localhost"); // Zmień na swój klucz

$categoryService = new CategoryService($categoryRepository, $sluggerService);
$postService = new PostService($postsRepository, $usersRepository, $categoryRepository, $sluggerService, $commentRepository); // Upewnij się, że CommentRepository jest przekazane
$commentService = new CommentService($commentRepository, $postsRepository, $usersRepository);


// Kontrolery
$usersController = new UsersController($userService, $jwtService);
$rolesController = new RolesController($roleService, $jwtService); // Użyj RoleService
$authController = new AuthController($userService, $jwtService);
$categoryController = new CategoryController($categoryService, $jwtService);
$postsController = new PostsController($postService, $jwtService);
$commentController = new CommentController($commentService, $jwtService);
// Zainicjuj nowy kontroler rejestracji, wstrzykując UserService i JWTService
$registerController = new RegisterController($userService, $jwtService); // Dodano inicjalizację


// Pobierz ścieżkę i metodę
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/'); // usuń końcowy slash

// Routing
switch (true) {
    // REJESTRACJA - NOWY ROUTE
    case $path === '/api/register' && $method === 'POST':
        $registerController->register(); // Skieruj żądanie do nowego kontrolera
        break;

    // LOGOWANIE
    case $path === '/api/login' && $method === 'POST':
        $authController->login();
        break;

    // USERS
    // Usunięto obsługę POST /api/users do tworzenia użytkownika
    case $path === '/api/users' && $method === 'GET':
        $usersController->getAllUsers();
        break;

    case preg_match('#^/api/users/(\d+)$#', $path, $matches) && $method === 'GET':
        $usersController->getUserById((int)$matches[1]);
        break;

    case preg_match('#^/api/users/(\d+)$#', $path, $matches) && $method === 'PUT':
        $usersController->updateUser((int)$matches[1]);
        break;

    case preg_match('#^/api/users/(\d+)$#', $path, $matches) && $method === 'DELETE':
        $usersController->deleteUser((int)$matches[1]);
        break;

    // ROLES
    case $path === '/api/roles' && $method === 'GET':
        $rolesController->getAllRoles();
        break;

    case preg_match('#^/api/roles/(\d+)$#', $path, $matches) && $method === 'GET':
        $rolesController->getRoleById((int)$matches[1]);
        break;

    case $path === '/api/roles' && $method === 'POST':
        $rolesController->createRole();
        break;

    case preg_match('#^/api/roles/(\d+)$#', $path, $matches) && $method === 'DELETE':
        $rolesController->deleteRole((int)$matches[1]);
        break;

    // CATEGORIES
    case $path === '/api/categories' && $method === 'GET':
        $categoryController->index();
        break;

    case preg_match('#^/api/categories/(\d+)$#', $path, $matches) && $method === 'GET':
        $categoryController->show((int)$matches[1]);
        break;

    case $path === '/api/categories' && $method === 'POST':
        $categoryController->create();
        break;

     case preg_match('#^/api/categories/(\d+)$#', $path, $matches) && $method === 'PUT':
        $categoryController->update((int)$matches[1]);
        break;

    case preg_match('#^/api/categories/(\d+)$#', $path, $matches) && $method === 'DELETE':
        $categoryController->delete((int)$matches[1]);
        break;

    // POSTS
    case $path === '/api/posts' && $method === 'GET':
        $postsController->getAllPosts();
        break;

    case preg_match('#^/api/posts/(\d+)$#', $path, $matches) && $method === 'GET':
        $postsController->getPostById((int)$matches[1]);
        break;

    case $path === '/api/posts' && $method === 'POST':
        $postsController->createPost();
        break;

     case preg_match('#^/api/posts/(\d+)$#', $path, $matches) && $method === 'POST':
        $postsController->updatePost((int)$matches[1]);
        break;

    case preg_match('#^/api/posts/(\d+)$#', $path, $matches) && $method === 'DELETE':
        $postsController->deletePost((int)$matches[1]);
        break;

    // COMMENTS
    case preg_match('#^/api/posts/(\d+)/comments$#', $path, $matches) && $method === 'GET':
         $commentController->getCommentsForPost((int)$matches[1]);
         break;

    case preg_match('#^/api/posts/(\d+)/comments$#', $path, $matches) && $method === 'POST':
        $commentController->addCommentToPost((int)$matches[1]);
        break;

     case preg_match('#^/api/posts/(\d+)/comments/(\d+)$#', $path, $matches) && $method === 'DELETE':
        $commentController->deleteComment((int)$matches[1], (int)$matches[2]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
        break;
}