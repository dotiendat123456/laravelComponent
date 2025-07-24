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
        $model = $data['post']; // Bất kỳ model nào hỗ trợ like
        $isLike = $data['type'] === 'like';

        $reaction = PostLike::where('user_id', $user->id)
            ->where('likeable_id', $model->id)
            ->where('likeable_type', get_class($model))
            ->first();

        $currentReaction = null;

        // if ($reaction) {
        //     if ($reaction->type === $isLike) {
        //         $reaction->delete(); // toggle off
        //     } else {
        //         $reaction->update(['type' => $isLike]);
        //         $currentReaction = $isLike ? 'like' : 'dislike';
        //     }
        // } else {
        //     PostLike::create([
        //         'user_id' => $user->id,
        //         'likeable_id' => $model->id,
        //         'likeable_type' => get_class($model),
        //         'type' => $isLike,
        //     ]);
        //     $currentReaction = $isLike ? 'like' : 'dislike';
        // }
        if ($reaction) {
            if ($reaction->type === $isLike) { // So sánh đúng kiểu boolean
                $reaction->delete(); // Xóa phản hồi nếu cùng loại
                $currentReaction = null;
            } else {
                $reaction->update(['type' => $isLike]); // Chuyển đổi type
                $currentReaction = $isLike ? 'like' : 'dislike';
            }
        } else {
            PostLike::create([
                'user_id' => $user->id,
                'likeable_id' => $model->id,
                'likeable_type' => get_class($model),
                'type' => $isLike,
            ]);
            $currentReaction = $isLike ? 'like' : 'dislike';
        }

        return [
            'like_count' => $model->likes()->get()->count(),        // hoặc collect xử lý
            'dislike_count' => $model->dislikes()->get()->count(),
            'current_reaction' => $currentReaction,
        ];
    }



    // public function addComment(array $data): PostComment
    // {
    //     $post = Post::find($data['post_id']);
    //     if (!$post) {
    //         throw ValidationException::withMessages([
    //             'post_id' => 'Bài viết không tồn tại.',
    //         ]);
    //     }

    //     if (!empty($data['parent_id'])) {
    //         $parentComment = PostComment::find($data['parent_id']);
    //         if (!$parentComment || $parentComment->post_id !== $post->id) {
    //             throw ValidationException::withMessages([
    //                 'parent_id' => 'Bình luận cha không hợp lệ.',
    //             ]);
    //         }
    //     }

    //     return PostComment::create([
    //         'post_id' => $post->id,
    //         'user_id' => Auth::id(),
    //         'parent_id' => $data['parent_id'] ?? null,
    //         // 'content' => $data['content'],
    //         'content' => strip_tags($data['content']),
    //     ]);
    // }
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
            if (!$parentComment || $parentComment->commentable_id !== $post->id || $parentComment->commentable_type !== Post::class) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Bình luận cha không hợp lệ.',
                ]);
            }
        }

        return PostComment::create([
            'commentable_id' => $post->id,
            'commentable_type' => Post::class,
            'user_id' => Auth::id(),
            'parent_id' => $data['parent_id'] ?? null,
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
