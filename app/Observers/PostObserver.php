<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Str;

class PostObserver
{
    /**
     * Trước khi tạo mới ➜ gen slug & đảm bảo unique ngay.
     */
    public function creating(Post $post): void
    {
        //Sau khi insert mới có id nên ở create không truyền $post->id vào 
        $post->slug = $this->generateUniqueSlug($post->title);
    }

    /**
     * Trước khi update ➜ nếu đổi title ➜ gen slug mới & đảm bảo unique.
     */
    public function updating(Post $post): void
    {
        if ($post->isDirty('title')) {
            $post->slug = $this->generateUniqueSlug($post->title, $post->id);
        }
    }

    /**
     * Hàm xử lý gen slug unique.
     */
    protected function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $attempt = 0;

        do {
            $exists = Post::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists();

            if ($exists) {
                $hash = substr(md5(Str::uuid() . $attempt), 0, 6);
                $slug = $baseSlug . '-' . $hash;
                $attempt++;
            }
        } while ($exists);

        return $slug;
    }
}
