<?php
// routes.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

require_once 'controllers/UsersController.php';
require_once 'controllers/RolesController.php';

if ($uri === '/users' && $method === 'GET')
{
    (new UsersController($pdo))->getAllUsers();
}
elseif (preg_match('/\/users\/(\d+)/', $uri, $matches) && $method === 'GET')
{
    (new UsersController($pdo))->getUserById($matches[1]);
}
elseif (preg_match('/\/user\/([a-zA-Z0-9_-]+)/', $uri, $matches) && $method === 'GET')
{
    (new UsersController($pdo))->getUserByUsername($matches[1]);
}
elseif ($uri === '/user/create' && $method === 'POST')
{
    (new UsersController($pdo))->createUser();
}
elseif (preg_match('/\/user\/update\/(\d+)/', $uri, $matches) && $method === 'PUT')
{
    (new UsersController($pdo))->updateUser($matches[1]);
}
elseif (preg_match('/\/user\/delete\/(\d+)/', $uri, $matches) && $method === 'DELETE')
{
    (new UsersController($pdo))->deleteUser($matches[1]);
}
elseif ($uri === '/roles' && $method === 'GET')
{
    (new RolesController($pdo))->getAllRoles();
}
elseif (preg_match('/\/roles\/(\d+)/', $uri, $matches) && $method === 'GET')
{
    (new RolesController($pdo))->getRoleById($matches[1]);
}
elseif ($uri === '/roles/create' && $method === 'POST')
{
    (new RolesController($pdo))->createRole();
}
elseif (preg_match('/\/roles\/delete\/(\d+)/', $uri, $matches) && $method === 'DELETE')
{
    (new RolesController($pdo))->deleteRole($matches[1]);
}
else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}
