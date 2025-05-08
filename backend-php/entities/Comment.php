<?php

namespace App\Entities;

use DateTime;
use App\Entities\Posts;
use App\Entities\Users;

class Comment
{
    public ?int $id = null;
    public ?Posts $post = null; // Obiekt posta, do którego należy komentarz
    public ?Users $user = null; // Obiekt użytkownika, który dodał komentarz
    public ?string $content = null;
    public ?DateTime $created_at = null;
    public ?DateTime $updated_at = null;
}