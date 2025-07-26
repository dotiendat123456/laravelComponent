<?php

namespace App\Models;

use App\Enums\ReactionType;
use Illuminate\Database\Eloquent\Model;

// class PostLike extends Model
// {
//     protected $fillable = ['user_id', 'post_id', 'type'];

//     protected $casts = [
//         'type' => 'boolean', // Tự động ép về true/false
//     ];

//     public function user()
//     {
//         return $this->belongsTo(User::class);
//     }

//     public function post()
//     {
//         return $this->belongsTo(Post::class);
//     }

//     // Helper function: kiểm tra là like
//     public function isLike(): bool
//     {
//         return $this->type === true;
//     }

//     // Helper function: kiểm tra là dislike
//     public function isDislike(): bool
//     {
//         return $this->type === false;
//     }
// }
class PostLike extends Model
{
    protected $fillable = ['user_id', 'likeable_id', 'likeable_type', 'type'];

    protected $casts = [
        'type' => ReactionType::class,
    ];
   

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likeable()
    {
        return $this->morphTo();
    }

    public function isLike(): bool
    {
        return $this->type === true;
    }

    public function isDislike(): bool
    {
        return $this->type === false;
    }
}
