<?php

namespace App\Services\Admin;

use App\Models\Post; // Model bài viết
use App\Enums\PostStatus; // Enum trạng thái bài viết
use Illuminate\Support\Facades\Auth; // Lấy user hiện tại
use Illuminate\Support\Facades\DB; // Transaction database
use Illuminate\Support\Facades\Log; // Log lỗi
use App\Jobs\NotifyUserPostStatusJob; // Job thông báo khi thay đổi trạng thái
use Illuminate\Http\Request;

class PostService
{
    /**
     * Lấy danh sách bài viết cho Admin
     * Có join user để lấy email nếu cần, có phân trang, lọc, sắp xếp.
     */
    public function getPostsData(Request $request)
    {
        $columns = [
            0 => 'posts.id',
            1 => 'posts.title',
            2 => 'users.email',
            3 => 'posts.status',
            4 => 'posts.created_at',
        ];

        $orderColIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc');
        $orderColumn = $columns[$orderColIndex] ?? 'posts.id';

        $query = Post::query();

        $needJoin = $orderColumn === 'users.email' || $request->filled('email');

        if ($needJoin) {
            $query->join('users', 'posts.user_id', '=', 'users.id')
                ->select('posts.*', 'users.email as user_email')
                ->groupBy('posts.id');
        } else {
            $query->with('user');
        }

        if ($request->filled('title')) {
            $query->where('posts.title', 'like', "%{$request->title}%");
        }

        if ($request->filled('email')) {
            $query->where('users.email', 'like', "%{$request->email}%");
        }

        $query->orderBy($orderColumn, $orderDir);

        $length = intval($request->input('length', 10));
        $start = intval($request->input('start', 0));
        $page = ($start / $length) + 1;

        return $query->paginate($length, ['*'], 'page', $page);
    }

    /**
     * Tạo bài viết mới.
     * @param array $data : dữ liệu bài viết
     * @param $thumbnail : file ảnh thumbnail
     */
    public function createPost(array $data, $thumbnail = null)
    {
        return DB::transaction(function () use ($data, $thumbnail) {
            $data['user_id'] = Auth::id(); // Gán user_id cho bài viết
            $data['status'] = PostStatus::APPROVED; // Admin tạo mặc định là APPROVED

            $post = Post::create($data);

            if ($thumbnail) {
                $post->addMedia($thumbnail)->toMediaCollection('thumbnails');
            }

            return $post;
        });
    }

    /**
     * Cập nhật bài viết.
     * @param Post $post : đối tượng bài viết cần cập nhật
     * @param array $data : dữ liệu cập nhật
     * @param $thumbnail : file thumbnail mới (nếu có)
     */
    public function updatePost(Post $post, array $data, $thumbnail = null)
    {
        return DB::transaction(function () use ($post, $data, $thumbnail) {
            $oldStatus = $post->status;

            $post->update($data);

            // Nếu status thay đổi thì dispatch Job gửi notify cho user
            if (($data['status'] ?? $oldStatus) != $oldStatus) {
                NotifyUserPostStatusJob::dispatch($post);
            }

            if ($thumbnail) {
                $post->clearMediaCollection('thumbnails');
                $post->addMedia($thumbnail)->toMediaCollection('thumbnails');
            }

            return $post;
        });
    }

    /**
     * Xoá bài viết.
     */
    public function deletePost(Post $post)
    {
        return DB::transaction(function () use ($post) {
            $post->delete();
        });
    }

    /**
     * Xoá tất cả bài viết.
     */
    public function deleteAllPosts()
    {
        return DB::transaction(function () {
            Post::query()->delete();
        });
    }
}
