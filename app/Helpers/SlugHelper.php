<?php

// namespace App\Helpers;

use Illuminate\Support\Str;


if (!function_exists('generateSlug')) {
    function generateSlug(string $title, int $id): string
    {
        return Str::slug($title) . '-' . substr(md5($id), 0, 6);
    }
}
// if (!function_exists('format_date')) {
//     function format_date($date)
//     {
//         return $date->format('d/m/Y');
//     }
// }
if (!function_exists('format_date')) {
    function format_date($date, $type )
    {
        if (!$date) {
            return '';
        }

        if ($type === 'datetime') {
            return $date->format('d/m/Y H:i');
        }

        return $date->format('d/m/Y');
    }
}
