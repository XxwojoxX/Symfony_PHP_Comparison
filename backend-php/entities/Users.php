<?php

class Users
{
    public ?int $id = null;
    public ?string $username = null;
    public ?string $email = null;
    public ?string $password = null;
    public ?DateTime $created_at = null;
    public ?Roles $role = null;
}