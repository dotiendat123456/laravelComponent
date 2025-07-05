<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Str;

class PostObserver
{
    public function created(Post $post): void
    {
        $slug = Str::slug($post->title) . '-' . substr(md5($post->id), 0, 6);
        $post->update(['slug' => $slug]);
    }

    public function updating(Post $post): void
    {
        $post->slug = Str::slug($post->title) . '-' . substr(md5($post->id), 0, 6);
    }
}
