<?php

namespace App\Services\User;

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
    public function getPostsData(array $data)
    {
        $user = Auth::user();

        $query = $user->posts()->with('media');

        // Filter theo tiêu đề và mô tả
        if (!empty($data['title'])) {
            $query->whereAny(['title', 'description'], 'like', '%' . $data['title'] . '%');
        }

        // Filter theo trạng thái
        // if (!empty($data['status'])) {
        //     $query->where('status', $data['status']);
        // }
        if (isset($data['status']) || $data['status'] === 0) {
            $query->where('status', $data['status']);
        }


        $columns = [
            0 => 'thumbnail',
            1 => 'title',
            2 => 'description',
            3 => 'publish_date',
            4 => 'status',

        ];

        $orderColIndex = $data['order_column'];
        $orderDir = $data['order_dir'];

        if ($orderColIndex === null || !isset($columns[$orderColIndex]) || $columns[$orderColIndex] === 'thumbnail') {
            $query->orderByDesc('id');
        } else {
            $query->orderBy($columns[$orderColIndex], $orderDir);
        }

        $length = $data['length'];

        return $query->paginate($length);
    }




    /**
     * Tạo mới bài viết.
     */
    public function createPost(array $data)
    {
        DB::beginTransaction();

        try {
            $data['user_id'] = Auth::id();

            //Lấy thumbnail ra khỏi data trước khi lưu            
            $thumbnail = $data['thumbnail'] ?? null;
            unset($data['thumbnail']);

            $post = Post::create($data);

            if ($thumbnail) {
                $post->addMedia($thumbnail)->toMediaCollection('thumbnails');
            }

            DB::commit();
            return $post;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Cập nhật bài viết.
     */
    public function updatePost(array $data)
    {
        DB::beginTransaction();

        try {
            $post = Post::findOrFail($data['id']); //Trả về 1 bản ghi duy nhất(Kết quả là 1 đối tượng Post)

            $post->update($data);

            if (!empty($data['thumbnail'])) {
                $post->clearMediaCollection('thumbnails');
                $post->addMedia($data['thumbnail'])->toMediaCollection('thumbnails');
            }

            DB::commit();
            return $post;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }



    /**
     * Xoá 1 bài viết.
     */
    public function deletePost(Post $post)
    {
        DB::beginTransaction();

        try {
            $post->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Xoá tất cả bài viết của user hiện tại.
     */
    public function deleteAllUserPosts()
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            $user->posts()->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
