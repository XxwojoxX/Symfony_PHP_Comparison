<?php

namespace App\Entities;

use DateTime;
use App\Entities\Users;
use App\Entities\Category;

class Posts
{
    public ?int $id = null;
    public ?string $title = null;
    public ?Users $user = null; // Obiekt użytkownika, który dodał post
    public ?Category $category = null; // Obiekt kategorii, do której należy post
    public ?string $slug = null;
    public ?string $content = null;
    public ?DateTime $created_at = null;
    public ?DateTime $updated_at = null;
    public ?DateTime $published_at = null;
    public ?string $image_name = null;
    // W czystym PHP nie będziemy odwzorowywać relacji Collection w encjach tak jak w Doctrine,
    // powiązane komentarze i media będziemy pobierać przez Repository/Service.
    // Obsługa plików (imageFile) w czystym PHP wymagałaby osobnej logiki.
}