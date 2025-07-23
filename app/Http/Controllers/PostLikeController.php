<?php

namespace App\Http\Controllers;

use App\Models\PostLike;
use App\Models\Post;
use App\Services\LikeComment\NewsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    // public function react(Post $post, $type)
    // {
    //     $user = Auth::user();
    //     $isLike = $type === 'like';

    //     $reaction = PostLike::where('user_id', $user->id)
    //         ->where('post_id', $post->id)
    //         ->first();

    //     if ($reaction) {
    //         if ($reaction->type === $isLike) {
    //             $reaction->delete();
    //         } else {
    //             $reaction->update(['type' => $isLike]);
    //         }
    //     } else {
    //         PostLike::create([
    //             'user_id' => $user->id,
    //             'post_id' => $post->id,
    //             'type' => $isLike,
    //         ]);
    //     }

    //     if (request()->expectsJson()) {
    //         return response()->json([
    //             'like_count' => $post->likes()->count(),
    //             'dislike_count' => $post->dislikes()->count(),
    //         ]);
    //     }

    //     return back()->with('success', ucfirst($type) . ' thành công');
    // }
    protected $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }

    public function react(Request $request, Post $post, string $type)
    {
        $counts = $this->newsService->react([
            'post' => $post,
            'type' => $type,
        ]);

        if ($request->expectsJson()) {
            return response()->json($counts);
        }

        return back()->with('success', ucfirst($type) . ' thành công');
    }
}
