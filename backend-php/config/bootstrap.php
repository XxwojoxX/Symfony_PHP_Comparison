<?php
// Ustawienie strefy czasowej (unikamy problemów związanych z czasem)
date_default_timezone_set('Europe/Warsaw');

// Sprawdzenie, czy używamy Composer'a (autoloader)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';  // Ładowanie autoloadera Composer
} else {
    // Jeśli nie używamy Composer, ręczne dołączanie plików klas i funkcji
    require_once __DIR__ . '/../entities/Users.php';  // Załadowanie encji użytkownika
    require_once __DIR__ . '/../controllers/UsersController.php';  // Załadowanie kontrolera użytkownika
    require_once __DIR__ . '/../services/UserService.php';  // Załadowanie serwisu użytkownika
    // Możesz dodać więcej plików w razie potrzeby
}

// Ładowanie pliku konfiguracyjnego bazy danych
require_once __DIR__ . '/../db.php';  // Połączenie z bazą danych

// Ładowanie pliku z funkcjami pomocniczymi (jeśli masz takie pliki)
// require_once __DIR__ . '/../config/functions.php';  // Możliwe funkcje pomocnicze

// Możesz również załadować inne pliki konfiguracyjne, np. sesje, logowanie, itp.
// require_once __DIR__ . '/../config/session.php'; // Przykład załadowania sesji
