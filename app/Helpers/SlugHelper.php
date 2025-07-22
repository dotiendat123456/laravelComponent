<?php

// namespace App\Helpers;

use Illuminate\Support\Str;

// class SlugHelper
// {
//     public static function generateSlug(string $title, int $id): string
//     {
//         return Str::slug($title) . '-' . substr(md5($id), 0, 6);
//     }
// }


if (!function_exists('generateSlug')) {
    /**
     * Sinh slug kèm mã hash từ id để tránh trùng slug.
     *
     * @param string $title
     * @param int $id
     * @return string
     */
    function generateSlug(string $title, int $id): string
    {
        return Str::slug($title) . '-' . substr(md5($id), 0, 6);
    }
}

