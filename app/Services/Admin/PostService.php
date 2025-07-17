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
        // Khai báo các cột có thể sắp xếp, tương ứng với chỉ số cột DataTables
        $columns = [
            0 => 'posts.id',         // Cột ID bài viết
            1 => 'posts.title',      // Cột tiêu đề bài viết
            2 => 'users.email',      // Cột email người dùng
            3 => 'posts.status',     // Cột trạng thái bài viết
            4 => 'posts.created_at', // Cột ngày tạo bài viết
        ];

        // Lấy cột sắp xếp và chiều sắp xếp từ request
        $orderColIndex = $request->input('order.0.column'); // Chỉ số cột cần sắp xếp
        $orderDir = $request->input('order.0.dir', 'asc');  // Chiều sắp xếp (asc/desc)
        $orderColumn = $columns[$orderColIndex] ?? 'posts.id'; // Nếu không có thì mặc định là sắp xếp theo ID

        // Khởi tạo query cơ bản từ bảng posts
        $query = Post::query();

        /**
         * Khi nào cần JOIN bảng users?
         * - Nếu sắp xếp theo email (users.email).
         * - Hoặc đang tìm kiếm theo email người dùng.
         */
        $needJoin = $orderColumn === 'users.email' || $request->filled('email');

        if ($needJoin) {
            // Join bảng users để truy vấn email và group by theo posts.id để tránh lỗi MySQL
            $query->join('users', 'posts.user_id', '=', 'users.id')
                ->select('posts.*', 'users.email as user_email') // Lấy cả email để hiển thị
                ->groupBy('posts.id');
        } else {
            // Nếu không cần join thì eager load user để tránh N+1 query khi hiển thị user
            $query->with('user');
        }

        // Lọc theo tiêu đề bài viết
        if ($request->filled('title')) {
            $query->where('posts.title', 'like', "%{$request->title}%");
        }

        // Lọc theo email người dùng (chỉ khi có join)
        if ($request->filled('email')) {
            $query->where('users.email', 'like', "%{$request->email}%");
        }

        // Sắp xếp theo cột được yêu cầu
        $query->orderBy($orderColumn, $orderDir);

        // Phân trang theo chuẩn DataTables
        $length = intval($request->input('length', 10)); // Số bản ghi mỗi trang
        $start = intval($request->input('start', 0));    // Offset bắt đầu
        $page = ($start / $length) + 1;                  // Tính số trang

        // Trả về dữ liệu đã phân trang
        return $query->paginate($length, ['*'], 'page', $page);
    }

    /**
     * Tạo bài viết mới.
     * @param array $data : dữ liệu bài viết
     * @param $thumbnail : file ảnh thumbnail
     */
    public function createPost(array $data, $thumbnail = null)
    {
        DB::beginTransaction();

        try {
            $data['user_id'] = Auth::id(); // Gán user_id cho bài viết
            $data['status'] = PostStatus::APPROVED; // Admin tạo mặc định là APPROVED

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
    public function updatePost(Post $post, array $data, $thumbnail = null)
    {
        DB::beginTransaction();

        try {
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
     * Xoá tất cả bài viết.
     */
    public function deleteAllPosts()
    {
        DB::beginTransaction();

        try {
            Post::query()->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
