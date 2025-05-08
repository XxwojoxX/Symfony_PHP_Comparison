<?php

namespace App\Entities;

class Category
{
    public ?int $id = null;
    public ?string $name = null;
    public ?string $slug = null;
    // W czystym PHP nie będziemy odwzorowywać relacji Collection w encjach tak jak w Doctrine,
    // powiązane posty będziemy pobierać przez Repository/Service.
}