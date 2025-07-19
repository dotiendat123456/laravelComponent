<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class SlugHelper
{
    public static function generateSlug(string $title, int $id): string
    {
        return Str::slug($title) . '-' . substr(md5($id), 0, 6);
    }
}
