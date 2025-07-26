<?php

namespace App\Http\Controllers;

use App\Enums\ReactionType;
use App\Models\PostLike;
use App\Models\Post;
use App\Services\Like\LikeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    protected $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    // public function react(Request $request, Post $post,  $type)
    // {
    //     $counts = $this->likeService->react([
    //         'post' => $post,
    //         'type' => $type,
    //     ]);

    //     if ($request->expectsJson()) {
    //         return response()->json($counts);
    //     }

    //     return back()->with('success', ucfirst($type) . ' thành công');
    // }

    public function react(Request $request, Post $post, $type)
    {
        try {
            $reactionType = ReactionType::from((int) $type);
        } catch (\ValueError $e) {
            abort(400, 'Loại phản hồi không hợp lệ');
        }

        $counts = $this->likeService->react([
            'post' => $post,
            'type' => $reactionType,
        ]);

        if ($request->expectsJson()) {
            return response()->json($counts);
        }

        return back()->with('success', $reactionType->label() . ' thành công');
    }
}
