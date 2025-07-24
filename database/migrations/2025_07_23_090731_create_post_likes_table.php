<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Schema::create('post_likes', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        //     $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
        //     $table->boolean('type')->default(true); // true = Like, false = Dislike
        //     $table->timestamps();

        //     // 1 user chỉ like hoặc dislike 1 lần duy nhất cho 1 bài viết
        //     $table->unique(['user_id', 'post_id']);
        // });
        Schema::create('post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('likeable'); // likeable_id & likeable_type
            $table->boolean('type')->default(true); // true = Like, false = Dislike
            $table->timestamps();

            // 1 user chỉ like hoặc dislike 1 đối tượng 1 lần
            $table->unique(['user_id', 'likeable_id', 'likeable_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_likes');
    }
};
