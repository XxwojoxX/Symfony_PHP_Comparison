<?php

namespace App\Controllers;

use App\Services\PostService;
use App\Services\JWTService;
use Exception;

class PostsController
{
    private PostService $postService;
    private JWTService $jwtService;

    public function __construct(PostService $postService, JWTService $jwtService)
    {
        $this->postService = $postService;
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

    public function getAllPosts(): void
    {
        // Ta metoda powinna być chroniona
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
        $posts = $this->postService->getAllPosts($limit);

        if (count($posts) > 0) {
            $response = array_map(function($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'content' => $post->content,
                    'created_at' => $post->created_at ? $post->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $post->updated_at ? $post->updated_at->format('Y-m-d H:i:s') : null,
                    'slug' => $post->slug,
                    'image' => $post->image_name,
                    'user_id' => $post->user ? $post->user->id : null,
                    'username' => $post->user ? $post->user->username : null,
                    'category_id' => $post->category ? $post->category->id : null,
                    'category_name' => $post->category ? $post->category->name : null,
                ];
            }, $posts);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'No posts found']);
        }
    }

    public function getPostById(int $id): void
    {
        // Ta metoda powinna być chroniona
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }

        $post = $this->postService->getPostById($id);

        if ($post) {
             header('Content-Type: application/json');
             echo json_encode([
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'created_at' => $post->created_at ? $post->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $post->updated_at ? $post->updated_at->format('Y-m-d H:i:s') : null,
                'published_at' => $post->published_at ? $post->published_at->format('Y-m-d H:i:s') : null,
                'slug' => $post->slug,
                'image' => $post->image_name,
                 'user_id' => $post->user ? $post->user->id : null,
                'username' => $post->user ? $post->user->username : null,
                'category_id' => $post->category ? $post->category->id : null,
                'category_name' => $post->category ? $post->category->name : null,
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Post not found']);
        }
    }

    public function createPost(): void
    {
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }

        // Zmodyfikowana logika odczytu danych: obsługujemy form-data (dane tekstowe w $_POST)
        // Obsługa plików ($FILES) jest bardziej złożona i wymaga przeniesienia pliku

        $title = $_POST['title'] ?? null;
        $content = $_POST['content'] ?? null;
        $categoryId = $_POST['category_id'] ?? null;

        // Podstawowa walidacja pól tekstowych
        if (!isset($title, $content, $categoryId)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data: title, content, and category_id are required']);
            return;
        }

        // Walidacja category_id - upewnij się, że to liczba całkowita
         if (!filter_var($categoryId, FILTER_VALIDATE_INT)) {
             http_response_code(400);
             echo json_encode(['error' => 'Invalid data: category_id must be an integer']);
             return;
         }
         $categoryId = (int)$categoryId; // Rzutowanie na int


        // ----- Obsługa przesyłania plików -----
        $image_name = null; // Domyślnie brak obrazu

        // Sprawdź, czy plik został przesłany pod kluczem 'image_name'
        if (isset($_FILES['image_name']) && $_FILES['image_name']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image_name']['tmp_name'];
            $fileName = $_FILES['image_name']['name'];
            $fileSize = $_FILES['image_name']['size'];
            $fileType = $_FILES['image_name']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // Określ docelowy katalog do zapisu (stwórz go, jeśli nie istnieje)
            $uploadDir = __DIR__ . '/../public/uploads/posts/'; // Przykład ścieżki
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Wygeneruj unikalną nazwę pliku, aby uniknąć kolizji
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            // Przenieś plik z katalogu tymczasowego na docelowy
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $image_name = $newFileName; // Zapisz nazwę pliku do bazy
                // Opcjonalnie: Zapisz też ścieżkę relatywną, np. '/uploads/posts/' . $newFileName
            } else {
                // Błąd przenoszenia pliku - możesz zwrócić odpowiedni komunikat
                // http_response_code(500);
                // echo json_encode(['error' => 'Failed to move uploaded file']);
                // return;
                 // Na razie pozwalamy na kontynuację bez obrazu
                 error_log("Failed to move uploaded file: " . $_FILES['image_name']['error']); // Zaloguj błąd przenoszenia
            }
        }
        // ----- Koniec obsługi przesyłania plików -----


        try {
            // Zakładamy, że token JWT zawiera id użytkownika w 'sub'
            $userId = $userToken->sub ?? null;

            if (!$userId) {
                http_response_code(401);
                echo json_encode(['error' => 'User ID not found in token']);
                return;
            }

            $post = $this->postService->createPost(
                $title,
                $content,
                $userId,
                $categoryId,
                $image_name // Przekaż wygenerowaną nazwę pliku
            );
            header('Content-Type: application/json');
            http_response_code(201);
            echo json_encode(['message' => 'Post created successfully', 'postId' => $post->id, 'image_name' => $image_name]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function updatePost(int $id): void
    {
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }

        // Odczyt danych tekstowych z $_POST dla form-data
        $data = $_POST; // Pobierz wszystkie dane tekstowe z $_POST

        // ----- Obsługa przesyłania plików (skopiowane z createPost) -----
        $image_name = $data['image_name'] ?? null; // Pobierz ewentualną istniejącą nazwę obrazu z danych tekstowych

        // Sprawdź, czy przesłano nowy plik obrazu pod kluczem 'imageFile' (lub jakimś innym, jeśli zmienisz nazwę pola w Postmanie)
        if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) { // Zmieniono klucz na 'imageFile'
            $fileTmpPath = $_FILES['imageFile']['tmp_name'];
            $fileName = $_FILES['imageFile']['name'];
            $fileSize = $_FILES['imageFile']['size'];
            $fileType = $_FILES['imageFile']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // Określ docelowy katalog do zapisu
            $uploadDir = __DIR__ . '/../public/uploads/posts/'; // Przykład ścieżki
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Wygeneruj unikalną nazwę pliku
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            // Przenieś plik
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $image_name = $newFileName; // Zapisz nową nazwę pliku
                // Opcjonalnie: Usuń stary plik obrazu, jeśli post go miał
                 // $postToUpdate = $this->postService->getPostById($id);
                 // if ($postToUpdate && $postToUpdate->image_name && file_exists($uploadDir . $postToUpdate->image_name)) {
                 //     unlink($uploadDir . $postToUpdate->image_name);
                 // }

            } else {
                 // Błąd przenoszenia pliku - możesz logować lub zwrócić błąd
                 error_log("Failed to move uploaded file for post update: " . $_FILES['imageFile']['error']);
                 // Możesz chcieć przerwać i zwrócić 500 lub 400, jeśli obraz jest wymagany do aktualizacji
            }
        }
        // ----- Koniec obsługi przesyłania plików -----

        // Dodaj nazwę obrazu (nową lub istniejącą z danych tekstowych) do tablicy danych do aktualizacji
         if ($image_name !== null) {
             $data['image_name'] = $image_name;
         } else {
             // Jeśli nie przesłano nowego pliku i nie podano image_name w danych,
             // możesz zadecydować, czy usunąć istniejący obraz, czy pozostawić go bez zmian.
             // Obecnie, jeśli brakuje 'imageFile' i 'image_name', stare image_name pozostanie w bazie (jeśli już było).
             // Jeśli chcesz wymusić usunięcie obrazu gdy nie podano image_name, ustaw $data['image_name'] na null.
             // if (!isset($data['image_name']) && !isset($_FILES['imageFile'])) {
             //     $data['image_name'] = null; // Ustaw null, aby usunąć obraz
             // }
         }


        // Tutaj możesz dodać walidację, czy tablica $data (po dodaniu image_name) nie jest pusta, jeśli wymaga tego serwis
         if (empty($data)) {
             http_response_code(400);
             echo json_encode(['error' => 'No update data provided']);
             return;
         }


        try {
            // Zakładamy, że token JWT zawiera id użytkownika w 'sub'
             $userToken = $this->authenticate(); // Ponowna autentykacja (opcjonalnie, jeśli już na początku metody)
             if (!$userToken) { return; } // Upewnij się, że authenticate() było na początku

            // Tutaj możesz dodać logikę sprawdzania uprawnień, np. czy użytkownik z tokenu jest autorem posta
             $userId = $userToken->sub ?? null;
             $postToUpdate = $this->postService->getPostById($id);
             if (!$postToUpdate) {
                 http_response_code(404);
                 echo json_encode(['error' => 'Post not found']);
                 return;
             }
             // if ($postToUpdate->user->id !== $userId /*&& user is not admin*/) {
             //      http_response_code(403); // Forbidden
             //      echo json_encode(['error' => 'You are not allowed to edit this post']);
             //      return;
             // }


            // Przekaż ID posta i tablicę danych do aktualizacji do serwisu
            $post = $this->postService->updatePost($id, $data); // Przekazujemy $data z $_POST + ewentualnie nowa nazwa pliku

            // Logika odpowiedzi - post znaleziony i zaktualizowany
             if ($post) { // Serwis updatePost powinien zwracać zaktualizowany post lub null
                 header('Content-Type: application/json');
                 http_response_code(200);
                 echo json_encode(['message' => 'Post updated successfully', 'postId' => $post->id, 'image_name' => $post->image_name]);
            } else {
                 // To powinno być obsłużone wcześniej przez sprawdzenie $postToUpdate
                 http_response_code(500); // Nieoczekiwany błąd w serwisie?
                 echo json_encode(['error' => 'Failed to update post']);
            }

        } catch (\InvalidArgumentException $e) {
             http_response_code(400);
             echo json_encode(['error' => $e->getMessage()]);
        }
        catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred while updating the post: ' . $e->getMessage()]);
        }
    }

    public function deletePost(int $id): void
    {
         $userToken = $this->authenticate();
         if (!$userToken) {
             return;
         }

         // Opcjonalnie: Sprawdź uprawnienia użytkownika
         // $userId = $userToken->sub ?? null;
         // $postToDelete = $this->postService->getPostById($id);
         // if (!$postToDelete || ($postToDelete->user->id !== $userId /*&& user is not admin*/)) {
         //      http_response_code(403); // Forbidden
         //      echo json_encode(['error' => 'You are not allowed to delete this post']);
         //      return;
         // }

        try {
            $deleted = $this->postService->deletePost($id);
            if ($deleted) {
                http_response_code(200);
                echo json_encode(['message' => 'Post deleted successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Post not found']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred while deleting the post']);
        }
    }
}