<?php

namespace App\Services;

class SluggerService
{
    public function slug(string $string): string
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9-]+/', '-', $string);
        $string = trim($string, '-');
        return $string;
    }
}