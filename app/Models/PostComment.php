<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// class PostComment extends Model
// {
//     protected $fillable = ['post_id', 'user_id', 'parent_id', 'content'];

//     public function user()
//     {
//         return $this->belongsTo(User::class);
//     }

//     public function post()
//     {
//         return $this->belongsTo(Post::class);
//     }

//     //Gọi ra quan hệ cha($comment->parent)
//     public function parent()
//     {
//         return $this->belongsTo(PostComment::class, 'parent_id');
//     }



//     //Xác định quan hệ một-nhiều giữa 1 bình luận và các bình luận con. Laravel hiểu rằng các bình luận con có parent_id trỏ đến id của bình luận cha:$this->hasMany(PostComment::class, 'parent_id')
//     //Eager Loading đệ quy, nghĩa là khi lấy các comment con, Laravel sẽ tự động lấy thêm replies của chính các comment con đó (comment cháu, chắt, …): with('replies');
//     public function replies()
//     {
//         return $this->hasMany(PostComment::class, 'parent_id')->with('replies');
//     }
// }
class PostComment extends Model
{
    protected $fillable = ['user_id', 'commentable_id', 'commentable_type', 'parent_id', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function parent()
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(PostComment::class, 'parent_id')->with('replies');
    }
}
