<?php

namespace App\Services\Like;

use App\Models\PostLike;
use Illuminate\Support\Facades\Auth;

class LikeService
{
    //Xử lý like/dislike
    public function react(array $data): array
    {
        $user = Auth::user();
        $model = $data['post']; // Bất kỳ model nào hỗ trợ like
        // $isLike = $data['type'] === 'like';
        $isLike = $data['type'];

        $reaction = PostLike::where('user_id', $user->id)
            ->where('likeable_id', $model->id)
            ->where('likeable_type', get_class($model))
            ->first();

        // $currentReaction = null;

        if ($reaction) {
            if ($reaction->type === $isLike) { // So sánh đúng kiểu boolean
                $reaction->delete(); // Xóa phản hồi nếu cùng loại
                $currentReaction = null;
            } else {
                $reaction->update(['type' => $isLike]); // Chuyển đổi type
                $currentReaction = $isLike->action();
            }
        } else {
            PostLike::create([
                'user_id' => $user->id,
                'likeable_id' => $model->id,
                'likeable_type' => get_class($model),
                'type' => $isLike,
            ]);
            $currentReaction = $isLike->action();
        }

        return [
            'like_count' => $model->likes()->get()->count(),        // hoặc collect xử lý
            'dislike_count' => $model->dislikes()->get()->count(),
            'current_reaction' => $currentReaction,
        ];
    }
}
