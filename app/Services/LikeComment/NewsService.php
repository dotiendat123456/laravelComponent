<?php

namespace App\Services\LikeComment;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NewsService
{
    public function react(array $data): array
    {
        $user = Auth::user();
        $post = $data['post'];
        $isLike = $data['type'] === 'like';

        $reaction = PostLike::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($reaction) {
            if ($reaction->type === $isLike) {
                $reaction->delete();
            } else {
                $reaction->update(['type' => $isLike]);
            }
        } else {
            PostLike::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'type' => $isLike,
            ]);
        }

        return [
            'like_count' => $post->likes()->count(),
            'dislike_count' => $post->dislikes()->count(),
        ];
    }

    public function addComment(array $data): PostComment
    {
        $post = Post::find($data['post_id']);
        if (!$post) {
            throw ValidationException::withMessages([
                'post_id' => 'Bài viết không tồn tại.',
            ]);
        }

        if (!empty($data['parent_id'])) {
            $parentComment = PostComment::find($data['parent_id']);
            if (!$parentComment || $parentComment->post_id !== $post->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Bình luận cha không hợp lệ.',
                ]);
            }
        }

        return PostComment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'parent_id' => $data['parent_id'] ?? null,
            // 'content' => $data['content'],
            'content' => strip_tags($data['content']),
        ]);
    }

    /**
     * Xoá bình luận nếu có quyền
     */
    public function deleteComment(PostComment $comment): void
    {
        $user = Auth::user();

        if ($user->id !== $comment->user_id && !$user->isAdmin()) {
            throw new AuthorizationException('Không có quyền xóa bình luận.');
        }

        $comment->delete();
    }
}
