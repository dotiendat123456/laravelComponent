<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Str;
// use App\Helpers\SlugHelper;

class PostObserver
{
    public function created(Post $post): void
    {
        $slug = generateSlug($post->title, $post->id);
        $post->update(['slug' => $slug]);
    }

    public function updating(Post $post): void
    {
        $post->slug = generateSlug($post->title, $post->id);
    }
}
