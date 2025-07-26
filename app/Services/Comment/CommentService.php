<?php

namespace App\Services\Comment;

use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class CommentService
{
    //Thêm comment
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


    //Xóa comment
    public function deleteComment(PostComment $comment): void
    {
        $user = Auth::user();

        if ($user->id !== $comment->user_id && !$user->isAdmin()) {
            throw new AuthorizationException('Không có quyền xóa bình luận.');
        }

        $comment->delete();
    }
}
