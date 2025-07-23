<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Post;
use App\Models\PostComment;
use App\Services\LikeComment\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class PostCommentController extends Controller
{
    // public function store(StoreCommentRequest $request, Post $post)
    // {
    //     $validated = $request->validated();

    //     if (!empty($validated['parent_id'])) {
    //         $parentComment = PostComment::find($validated['parent_id']);
    //         if (!$parentComment || $parentComment->post_id !== $post->id) {
    //             return response()->json(['error' => 'Bình luận cha không hợp lệ.'], 422);
    //         }
    //     }

    //     $comment = PostComment::create([
    //         'post_id' => $post->id,
    //         'user_id' => Auth::id(),
    //         'parent_id' => $validated['parent_id'] ?? null,
    //         'content' => $validated['content'],
    //     ]);

    //     // Trả về HTML render từ blade comment
    //     $view = view('news.single_comment', ['comment' => $comment, 'post' => $post, 'level' => $request->input('level', 1)])->render();

    //     return response()->json(['html' => $view, 'message' => 'Đã thêm bình luận']);
    // }

    protected NewsService $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }

    public function store(StoreCommentRequest $request, Post $post)
    {
        $validated = $request->validated();
        $validated['post_id'] = $post->id;

        try {
            $comment = $this->newsService->addComment($validated);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }

        $view = view('news.single_comment', [
            'comment' => $comment,
            'post' => $post,
            'level' => $request->input('level', 1),
        ])->render();

        return response()->json(['html' => $view, 'message' => 'Đã thêm bình luận']);
    }




    public function destroy(PostComment $comment)
    {
        try {
            $this->newsService->deleteComment($comment);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        return response()->json(['message' => 'Đã xoá bình luận']);
    }
}
