<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\PostStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->nullable();
            $table->string('title', 100)->nullable();
            $table->string('slug', 100)->unique()->nullable();
            $table->string('description', 200)->nullable();
            $table->text('content')->nullable();
            $table->timestamp('publish_date')->nullable();

            // Trạng thái bài viết: 0 = PENDING, 1 = APPROVED, 2 = DENY
            $table->tinyInteger('status')->default(PostStatus::PENDING->value);

            $table->timestamps();
            $table->softDeletes();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
