<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Post;
use App\Models\PostComment;
use App\Services\Comment\CommentService;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class PostCommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function store(StoreCommentRequest $request, Post $post)
    {
        $validated = $request->validated();
        $validated['post_id'] = $post->id;

        try {
            $comment = $this->commentService->addComment($validated);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }

        $parentComment = null;
        if (!empty($validated['parent_id'])) {
            $parentComment = PostComment::find($validated['parent_id']);
        }

        $view = view('news.single_comment', [
            'comment' => $comment,
            'post' => $post,
            'level' => $request->input('level', 1),
        ])->render();

        return response()->json([
            'html' => $view,
            'message' => 'Đã thêm bình luận',
            'parent_id' => $parentComment ? $parentComment->id : null,
            'reply_count' => $parentComment ? $parentComment->replies()->count() : null,
        ]);
    }


    public function destroy(PostComment $comment)
    {
        try {
            $this->commentService->deleteComment($comment);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        return response()->json(['message' => 'Đã xoá bình luận']);
    }
}
