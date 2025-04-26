<?php

require_once 'vendor/autoload.php';
require_once 'config/bootstrap.php';

use App\Services\JWTService;
use App\Services\UserService;
use App\Repositories\UsersRepository;
use App\Repositories\RolesRepository;
use App\Controllers\UsersController;
use App\Controllers\RolesController;
use App\Controllers\AuthController;

$pdo = new PDO($dsn, $user, $pass, $options);

$usersRepository = new UsersRepository($pdo);
$rolesRepository = new RolesRepository($pdo);

// Serwisy (zależności)
$userService = new UserService($usersRepository, $rolesRepository);
$roleService = new RoleService($rolesRepository);
$jwtService = new JWTService("secret_key", "http://localhost");

// Kontrolery
$usersController = new UsersController($userService);
$rolesController = new RolesController($roleService);
$authController = new AuthController($userService, $jwtService);

// Pobierz ścieżkę i metodę
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/'); // usuń końcowy slash

// Routing
switch (true) {
    // LOGOWANIE
    case $path === '/login' && $method === 'POST':
        $authControlelr->login();
        break;
    // USERS
    case $path === '/users' && $method === 'GET':
        $usersController->getAllUsers();
        break;

    case preg_match('#^/users/(\d+)$#', $path, $matches) && $method === 'GET':
        $usersController->getUserById($matches[1]);
        break;

    case $path === '/users' && $method === 'POST':
        $usersController->createUser();
        break;

    case preg_match('#^/users/(\d+)$#', $path, $matches) && $method === 'PUT':
        $usersController->updateUser($matches[1]);
        break;

    case preg_match('#^/users/(\d+)$#', $path, $matches) && $method === 'DELETE':
        $usersController->deleteUser($matches[1]);
        break;

    // ROLES
    case $path === '/roles' && $method === 'GET':
        $rolesController->getAllRoles();
        break;

    case preg_match('#^/roles/(\d+)$#', $path, $matches) && $method === 'GET':
        $rolesController->getRoleById($matches[1]);
        break;

    case $path === '/roles' && $method === 'POST':
        $rolesController->createRole();
        break;

    case preg_match('#^/roles/(\d+)$#', $path, $matches) && $method === 'DELETE':
        $rolesController->deleteRole($matches[1]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
        break;
}
