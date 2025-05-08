<?php
$host = 'localhost';
$db = 'symfony_php_comp';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Wyrzucaj wyjątki w przypadku błędów
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Domyślny tryb pobierania danych jako tablice asocjacyjne
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Wyłącz emulację prepare statements (zalecane)
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Połączenie z bazą danych MySQL nawiązane pomyślnie!"; // Opcjonalne potwierdzenie
} catch (\PDOException $e) {
    // Wyrzuć wyjątek w przypadku błędu połączenia
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}