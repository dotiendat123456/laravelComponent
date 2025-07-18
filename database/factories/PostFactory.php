<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence,
            'slug' => Str::slug($this->faker->sentence) . '-' . uniqid(),
            'description' => $this->faker->text(200),
            'content' => $this->faker->paragraph,
            'publish_date' => now(),
            'status' => PostStatus::PENDING->value,
        ];
    }

    // public function configure()
    // {
    //     return $this->afterCreating(function (Post $post) {
    //         // Gán ảnh mặc định khi seed
    //         $post->addMedia(storage_path('app/public/default/default-thumbnail.png'))
    //             ->preservingOriginal()
    //             ->toMediaCollection('thumbnails');
    //     });
    // }
}
