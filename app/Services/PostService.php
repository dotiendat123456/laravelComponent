<?php


namespace App\Services;

use App\Models\Post;
use App\Enums\PostStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PostService
{
    /**
     * Lấy danh sách bài viết của user hiện tại với tìm kiếm, sắp xếp, phân trang (dành cho DataTables).
     */
    public function getUserPostsData(Request $request)
    {
        $user = Auth::user();

        $query = $user->posts()->with('media');

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $columns = [
            0 => 'id',
            1 => 'thumbnail', // Không sắp xếp ảnh
            2 => 'title',
            3 => 'description',
            4 => 'publish_date',
            5 => 'status',
        ];

        $orderColIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc');

        if ($orderColIndex === null || !isset($columns[$orderColIndex]) || $columns[$orderColIndex] === 'thumbnail') {
            $query->orderByDesc('id');
        } else {
            $query->orderBy($columns[$orderColIndex], $orderDir);
        }

        $length = intval($request->input('length', 10));
        $start = intval($request->input('start', 0));
        $page = ($start / $length) + 1;

        return $query->paginate($length, ['*'], 'page', $page);
    }

    /**
     * Tạo mới bài viết.
     */
    public function createPost(array $data, $thumbnail = null)
    {
        return DB::transaction(function () use ($data, $thumbnail) {
            $data['user_id'] = Auth::id();

            $post = Post::create($data);

            if ($thumbnail) {
                $post->addMedia($thumbnail)->toMediaCollection('thumbnails');
            }

            return $post;
        });
    }

    /**
     * Cập nhật bài viết.
     */
    public function updatePost(Post $post, array $data, $thumbnail = null)
    {
        return DB::transaction(function () use ($post, $data, $thumbnail) {
            $post->update($data);

            if ($thumbnail) {
                $post->clearMediaCollection('thumbnails');
                $post->addMedia($thumbnail)->toMediaCollection('thumbnails');
            }

            return $post;
        });
    }

    /**
     * Xoá 1 bài viết.
     */
    public function deletePost(Post $post)
    {
        return DB::transaction(function () use ($post) {
            $post->delete();
        });
    }

    /**
     * Xoá tất cả bài viết của user hiện tại.
     */
    public function deleteAllUserPosts()
    {
        $user = Auth::user();

        return DB::transaction(function () use ($user) {
            $user->posts()->delete();
        });
    }

    /**
     * Lấy danh sách bài viết public cho trang news.
     */
    public function getPublicPosts()
    {
        return Post::status(PostStatus::APPROVED->value)
            ->where('publish_date', '<=', now())
            ->latest('publish_date')
            ->paginate(2);
    }

    /**
     * Kiểm tra bài viết có phải public không.
     */
    public function isPublicPost(Post $post): bool
    {
        return $post->status === PostStatus::APPROVED
            && $post->publish_date !== null
            && !$post->publish_date->isFuture();
    }
}
