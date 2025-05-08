<?php
// Ustawienie strefy czasowej (unikamy problemów związanych z czasem)
date_default_timezone_set('Europe/Warsaw');

// Sprawdzenie, czy używamy Composer'a (autoloader)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';  // Ładowanie autoloadera Composer
} else {
    // Jeśli nie używamy Composer, ręczne dołączanie plików klas i funkcji
    require_once __DIR__ . '/../entities/Users.php';  // Załadowanie encji użytkownika
    require_once __DIR__ . '/../entities/Roles.php'; // Załadowanie encji roli
    require_once __DIR__ . '/../entities/Category.php'; // Załadowanie encji kategorii
    require_once __DIR__ . '/../entities/Posts.php'; // Załadowanie encji posta
    require_once __DIR__ . '/../entities/Comment.php'; // Załadowanie encji komentarza


    require_once __DIR__ . '/../repositories/UsersRepository.php'; // Załadowanie repozytorium użytkownika
    require_once __DIR__ . '/../repositories/RolesRepository.php'; // Załadowanie repozytorium roli
    require_once __DIR__ . '/../repositories/CategoryRepository.php'; // Załadowanie repozytorium kategorii
    require_once __DIR__ . '/../repositories/PostsRepository.php'; // Załadowanie repozytorium postów
    require_once __DIR__ . '/../repositories/CommentRepository.php'; // Załadowanie repozytorium komentarzy

    require_once __DIR__ . '/../services/UserService.php'; // Załadowanie serwisu użytkownika
    require_once __DIR__ . '/../services/RoleService.php'; // Załadowanie serwisu roli
    require_once __DIR__ . '/../services/JWTService.php'; // Załadowanie serwisu JWT
    require_once __DIR__ . '/../services/CategoryService.php'; // Załadowanie serwisu kategorii
    require_once __DIR__ . '/../services/PostService.php'; // Załadowanie serwisu postów
    require_once __DIR__ . '/../services/CommentService.php'; // Załadowanie serwisu komentarzy
    require_once __DIR__ . '/../services/SluggerService.php'; // Załadowanie serwisu sluggera


    require_once __DIR__ . '/../controllers/UsersController.php'; // Załadowanie kontrolera użytkownika
    require_once __DIR__ . '/../controllers/RolesController.php'; // Załadowanie kontrolera ról
    require_once __DIR__ . '/../controllers/AuthController.php'; // Załadowanie kontrolera autentykacji
    require_once __DIR__ . '/../controllers/CategoryController.php'; // Załadowanie kontrolera kategorii
    require_once __DIR__ . '/../controllers/PostsController.php'; // Załadowanie kontrolera postów
    require_once __DIR__ . '/../controllers/CommentController.php'; // Załadowanie kontrolera komentarzy
    require_once __DIR__ . '/../controllers/RegisterController.php'; // Dodano kontroler rejestracji


}

// Ładowanie pliku konfiguracyjnego bazy danych
require_once __DIR__ . '/../db.php';  // Połączenie z bazą danych

// Ładowanie pliku z funkcjami pomocniczymi (jeśli masz takie pliki)
// require_once __DIR__ . '/../config/functions.php';  // Możliwe funkcje pomocnicze

// Możesz również załadować inne pliki konfiguracyjne, np. sesje, logowanie, itp.
// require_once __DIR__ . '/../config/session.php'; // Przykład załadowania sesji