<?php
// generate_test_users_csv.php

// Załadowanie połączenia z bazą danych
// Upewnij się, że Twój plik config/bootstrap.php poprawnie ustawia globalną zmienną $pdo
require_once __DIR__ . '/config/bootstrap.php';

global $pdo; // Dostęp do obiektu PDO

$limit = 1000; // Liczba użytkowników (emali) do pobrania z bazy
$fixedPassword = 'zaq1@WSX'; // Ustalone hasło, które zostanie dopisane w CSV
$outputFile = __DIR__ . '/test_users_for_jmeter.csv'; // Nazwa pliku wyjściowego CSV

echo "Przygotowanie do generowania pliku CSV dla testów JMeter...\n";

try {
    // Zapytanie SQL do pobrania adresów email z tabeli 'users'
    // Pamiętaj o poprawnej nazwie kolumny email w Twojej tabeli (zazwyczaj 'email')
    $sql = "SELECT email FROM users LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $emails = $stmt->fetchAll(PDO::FETCH_COLUMN); // Pobieramy tylko wartości z pierwszej kolumny (email)

    if (empty($emails)) {
        echo "Nie znaleziono adresów email użytkowników w bazie danych (LIMIT $limit).\n";
        exit;
    }

    echo "Pobrano " . count($emails) . " adresów email z bazy danych.\n";
    echo "Otwieranie pliku CSV do zapisu: " . $outputFile . "...\n";

    // Otwarcie pliku CSV do zapisu
    // Używamy 'w' do zapisu (nadpisze istniejący plik)
    $fileHandle = fopen($outputFile, 'w');

    if ($fileHandle === false) {
        throw new Exception("Nie można otworzyć pliku CSV do zapisu: " . $outputFile);
    }

    // Zapisanie danych użytkowników do pliku CSV
    echo "Zapisywanie danych (email, hasło) do pliku CSV...\n";
    foreach ($emails as $email) {
        // Zapisujemy wiersz: pobrany email, ustalone hasło
        if (fputcsv($fileHandle, [$email, $fixedPassword]) === false) {
            echo "Ostrzeżenie: Nie udało się zapisać wiersza dla email: " . $email . "\n";
        }
    }

    // Zamknięcie pliku
    fclose($fileHandle);

    echo "Pomyślnie wygenerowano plik CSV z danymi logowania dla " . count($emails) . " użytkowników.\n";
    echo "Plik: " . $outputFile . " gotowy do użycia w JMeterze.\n";

} catch (\PDOException $e) {
    echo "Błąd bazy danych: " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "Błąd: " . $e->getMessage() . "\n";
} finally {
    // Upewnij się, że uchwyt pliku jest zamknięty nawet w przypadku błędów
    if (isset($fileHandle) && is_resource($fileHandle)) {
        fclose($fileHandle);
    }
}

?>