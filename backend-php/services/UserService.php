<?php

namespace App\Services;

use App\Repositories\UsersRepository;
use App\Repositories\RolesRepository;
use App\Repositories\PostsRepository;
use App\Repositories\CommentRepository;
use App\Entities\Users;
use App\Entities\Roles;
use DateTime;
use Exception;

class UserService
{
    private UsersRepository $usersRepository;
    private RolesRepository $rolesRepository;
    private PostsRepository $postsRepository;
    private CommentRepository $commentRepository;

    public function __construct(UsersRepository $usersRepository, RolesRepository $rolesRepository, PostsRepository $postsRepository, CommentRepository $commentRepository)
    {
        $this->postsRepository = $postsRepository;
        $this->commentRepository = $commentRepository;
        $this->usersRepository = $usersRepository;
        $this->rolesRepository = $rolesRepository;
    }

    public function getAllUsers(?int $limit = null): array
    {
        return $this->usersRepository->findAllUsers($limit);
    }

    public function getUserById(int $id): ?Users
    {
        return $this->usersRepository->findUserById($id);
    }

    public function getUserByUsername(string $username): ?Users
    {
        return $this->usersRepository->findUserByUsername($username);
    }

     public function getUserByEmail(string $email): ?Users
    {
        return $this->usersRepository->findUserByEmail($email);
    }


    public function createUser(string $username, string $email, string $password, ?int $roleId = null): Users
    {
        if(!$roleId)
        {
            $roleId = 2; // Domyślna rola
        }

        $role = $this->rolesRepository->findRoleById($roleId);
        if(!$role) {
            throw new Exception("Role not found.");
        }

        $user = new Users();
        $user->username = $username;
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_DEFAULT); // Haszowanie hasła (tylko tutaj)
        $user->created_at = new DateTime();
        $user->role = $role;

        $this->usersRepository->saveUser($user);

        return $user;
    }

    public function updateUser(Users $user, array $data): void
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('No data provided.');
        }

        if (isset($data['username']) && !empty($data['username'])) {
            $user->username = $data['username'];
        }

        if (isset($data['email']) && !empty($data['email'])) {
             if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Invalid email format.');
            }
            $user->email = $data['email'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
             $user->password = password_hash($data['password'], PASSWORD_DEFAULT); // Haszowanie hasła przy aktualizacji
        }

         if (isset($data['role_id'])) {
             $role = $this->rolesRepository->findRoleById($data['role_id']);
             if(!$role) {
                 throw new Exception("Role with ID {$data['role_id']} not found.");
             }
             $user->role = $role;
         }

        $this->usersRepository->saveUser($user);
    }

    public function deleteUser(int $id): void
    {
        $user = $this->usersRepository->findUserById($id);
         if(!$user)
        {
            // Zwróć błąd 404 z poziomu kontrolera, serwis nie rzuca tu 404
            throw new Exception("User not found.");
        }

        // ----- Logika usuwania powiązanych danych -----

        // 1. Znajdź i usuń powiązane komentarze
        $comments = $this->commentRepository->findCommentsByUserId($id);
        if (!empty($comments)) {
            $commentIds = array_map(fn($comment) => $comment->id, $comments);
             $this->commentRepository->deleteCommentsByIds($commentIds); // Użyj nowej metody usuwania wielu
            // Lub usuwaj pojedynczo:
            // foreach ($comments as $comment) {
            //     $this->commentRepository->deleteComment($comment);
            // }
        }

        // 2. Znajdź i usuń powiązane posty
        $posts = $this->postsRepository->findPostsByUserId($id);
         if (!empty($posts)) {
             $postIds = array_map(fn($post) => $post->id, $posts);
             $this->postsRepository->deletePostsByIds($postIds); // Użyj nowej metody usuwania wielu
             // Lub usuwaj pojedynczo:
            // foreach ($posts as $post) {
            //     // Jeśli posty mają własne komentarze lub inne powiązania,
            //     // usunięcie posta może wymagać najpierw usunięcia jego komentarzy itp.
            //     // Np. $this->commentRepository->deleteCommentsByPostId($post->id); // Musiałaby istnieć taka metoda
            //     $this->postsRepository->deletePost($post);
            // }
         }


        // 3. Usuń użytkownika
        $this->usersRepository->deleteUser($user);
    }

    public function authenticateUser(string $email, string $password): ?Users
    {
        // --- DEBUGOWANIE START --- // Usunięto tymczasowe logi debugujące
        // error_log("Login attempt for email: " . $email);
        // error_log("Password received (plain text): " . $password);
        // --- DEBUGOWANIE END ---

        $user = $this->usersRepository->findUserByEmail($email);

        // --- DEBUGOWANIE START --- // Usunięto tymczasowe logi debugujące
        // if ($user) {
        //     error_log("User found for email: " . $email);
        //     error_log("Hashed password from DB: " . $user->password);
        //     $passwordMatches = password_verify($password, $user->password);
        //     error_log("password_verify result: " . ($passwordMatches ? "TRUE" : "FALSE"));
        //      if ($passwordMatches) {
        //          error_log("Password verification successful!");
        //      } else {
        //          error_log("Password verification failed.");
        //      }
        // } else {
        //     error_log("User not found for email: " . $email);
        // }
        // --- DEBUGOWANIE END ---

        // Poprawiona logika weryfikacji: znajdź użytkownika i zweryfikuj hasło
        if(!$user || !password_verify($password, $user->password)) {
            return null; // Użytkownik nie znaleziony LUB hasło niepoprawne
        }

        return $user; // Zwróć użytkownika, jeśli uwierzytelnianie się powiodło
    }
}