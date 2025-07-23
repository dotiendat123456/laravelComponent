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

    public function getPostsData(array $data)
    {
        $columns = [
            0 => 'id',
            1 => 'title',
            2 => 'email',
            3 => 'status',
            4 => 'created_at',
        ];

        $orderColIndex = $data['order_column'] ?? 0;
        $orderDir = $data['order_dir'] ?? 'asc';
        $orderColumn = $columns[$orderColIndex] ?? 'id';

        $query = Post::query();

        $needJoin = $orderColumn === 'users.email' || !empty($data['email']);

        if ($needJoin) {
            $query->join('users', 'posts.user_id', '=', 'users.id')
                ->select('posts.*', 'users.email as user_email')
                ->distinct(); //Loại bỏ các bản ghi trùng lặp trong kết quả truy vấn
        } else {
            $query->with('user');
        }

        if (!empty($data['title'])) {
            $query->where('posts.title', 'like', '%' . $data['title'] . '%');
        }

        if (!empty($data['email'])) {
            if ($needJoin) {
                $query->where('users.email', 'like', '%' . $data['email'] . '%');
            } else {
                $query->whereHas('user', function ($q) use ($data) {
                    $q->where('email', 'like', '%' . $data['email'] . '%');
                });
            }
        }

        if (isset($data['status']) && $data['status'] !== '') {
            $query->where('status', $data['status']);
        }

        $query->orderBy($orderColumn, $orderDir);

        $length = $data['length'];

        return $query->paginate($length);
    }



    /**
     * Tạo bài viết mới.
     * @param array $data : dữ liệu bài viết
     * @param $thumbnail : file ảnh thumbnail
     */
    public function createPost(array $data)
    {
        DB::beginTransaction();

        try {
            $data['user_id'] = Auth::id();
            $data['status'] = PostStatus::APPROVED;

            // Lấy thumbnail ra khỏi mảng $data trước khi lưu DB
            $thumbnail = $data['thumbnail'] ?? null;
            unset($data['thumbnail']);

            $post = Post::create($data);

            if ($thumbnail) {
                $post->addMedia($thumbnail)->toMediaCollection('thumbnails');
            }

            DB::commit();

            return $post;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Cập nhật bài viết.
     * @param Post $post : đối tượng bài viết cần cập nhật
     * @param array $data : dữ liệu cập nhật
     * @param $thumbnail : file thumbnail mới (nếu có)
     */
    public function updatePost(array $data)
    {
        DB::beginTransaction();

        try {
            // Lấy Post từ ID
            $post = Post::findOrFail($data['id']);

            $oldStatus = $post->status;

            // Tách thumbnail ra
            $thumbnail = $data['thumbnail'] ?? null;
            unset($data['thumbnail'], $data['id']);

            $post->update($data);

            // Gửi notify nếu status thay đổi
            if (($data['status'] ?? $oldStatus) != $oldStatus) {
                NotifyUserPostStatusJob::dispatch($post);
            }

            // Xử lý thumbnail
            if ($thumbnail) {
                $post->clearMediaCollection('thumbnails');
                $post->addMedia($thumbnail)->toMediaCollection('thumbnails');
            }

            DB::commit();
            return $post;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Xoá bài viết.
     */
    public function deletePost(Post $post)
    {
        $post->delete();
    }

    /**
     * Xoá tất cả bài viết.
     */
    public function deleteAllPosts()
    {
        Post::query()->delete();
    }
}
