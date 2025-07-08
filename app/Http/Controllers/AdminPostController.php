<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\AdminStorePostRequest;
use App\Http\Requests\AdminUpdatePostRequest;
use Illuminate\Support\Str;
use App\Enums\UserRole;
use App\Jobs\NotifyUserPostStatusJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\PostStatus;

class AdminPostController extends Controller
{
    public function dashboard()
    {
        return view('admin.posts.dashboard');
    }


    public function index()
    {
        return view('admin.posts.index');
    }


    public function data(Request $request)
    {
        // Tạo query gốc lấy tất cả bài viết, kèm theo quan hệ user (tác giả)
        $query = Post::query();

        // Nếu có tham số 'title' gửi lên, thêm điều kiện tìm kiếm theo tiêu đề bài viết
        if ($request->filled('title')) {
            $query->where('title', 'like', "%{$request->title}%");
        }

        // Nếu có tham số 'email' gửi lên, thêm điều kiện tìm kiếm theo email user
        if ($request->filled('email')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('email', 'like', "%{$request->email}%");
            });
        }

        // Đếm tổng số bài viết gốc (không filter)
        $totalData = Post::count();

        // Đếm tổng số bài viết sau khi áp dụng filter
        $totalFiltered = $query->count();

        // Lấy số bản ghi hiển thị mỗi trang (limit) từ request
        $limit = intval($request->input('length'));

        // Lấy vị trí offset bắt đầu lấy dữ liệu
        $start = intval($request->input('start'));

        // Định nghĩa ánh xạ cột giữa vị trí index DataTables và tên cột DB
        $columns = [
            0 => 'id',
            1 => 'title',
            2 => 'users.email', // email không nằm trực tiếp ở bảng posts, phải join users
            3 => 'status',
            4 => 'created_at',
        ];

        // Lấy index cột sắp xếp và chiều sắp xếp(asc hoặc desc) từ request DataTables
        $orderColIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir');

        // Xác định tên cột cần sắp xếp
        $orderColumn = $columns[$orderColIndex] ?? 'id';

        // Nếu sắp xếp theo email → cần join bảng users để orderBy qua quan hệ
        if ($orderColumn === 'users.email') {
            $query->join('users', 'posts.user_id', '=', 'users.id')
                ->orderBy('users.email', $orderDir)
                ->select('posts.*'); // Chọn lại cột posts.* để tránh xung đột
        } else {
            $query->orderBy($orderColumn, $orderDir);
        }

        // Áp dụng offset, limit để phân trang
        $posts = $query->offset($start)->limit($limit)->get();

        // Tạo mảng dữ liệu chuẩn trả về cho DataTables
        $data = [];
        foreach ($posts as $post) {
            $data[] = [
                'id' => $post->id, // ID bài viết
                'title' => $post->title, // Tiêu đề bài viết
                'email' => $post->user->email ?? '-', // Email tác giả
                'status' => $post->status->label(), // Trạng thái bài viết (dạng text)
                'created_at' => $post->created_at->format('d/m/Y'), // Ngày tạo
                'slug' => $post->slug, // Slug để xem chi tiết
            ];
        }

        // Trả JSON đúng chuẩn DataTables yêu cầu
        return response()->json([
            'draw' => intval($request->input('draw')), // Số lần request (bắt buộc)
            'recordsTotal' => $totalData, // Tổng bản ghi gốc (chưa filter)
            'recordsFiltered' => $totalFiltered, // Tổng bản ghi sau filter
            'data' => $data, // Mảng dữ liệu trang hiện tại
        ]);
    }




    public function create()
    {
        return view('admin.posts.create');
    }



    public function store(AdminStorePostRequest $request)
    {
        DB::beginTransaction();

        try {
            // Tạo slug từ title
            $slug = Str::slug($request->title);

            // Kiểm tra slug có trùng không, nếu có thì thêm số tăng dần
            $originalSlug = $slug;
            $count = 1;

            while (Post::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            // Tạo bài viết
            $post = Post::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'slug' => $slug,
                'description' => $request->description,
                'content' => $request->content,
                'publish_date' => $request->publish_date,
                'status' => PostStatus::APPROVED,
            ]);

            // Nếu có thumbnail thì lưu bằng Spatie Media
            if ($request->hasFile('thumbnail')) {
                $post->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnails');
            }

            DB::commit();

            return to_route('admin.posts.index')->with('success', 'Tạo bài viết thành công');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Lỗi tạo bài viết: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Đã xảy ra lỗi khi tạo bài viết, vui lòng thử lại!']);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }



    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return view('admin.posts.edit', compact('post'));
    }




    public function update(AdminUpdatePostRequest $request, Post $post)
    {
        $this->authorize('updateStatus', $post);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            $oldStatus = $post->status;

            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'content' => $request->content,
                'publish_date' => $request->publish_date,
            ];

            if ($user->isAdmin()) {
                $data['status'] = $request->validated('status');
            }

            $post->update($data);

            if (($data['status'] ?? $oldStatus) != $oldStatus) {
                NotifyUserPostStatusJob::dispatch($post);
            }

            if ($request->hasFile('thumbnail')) {
                $post->clearMediaCollection('thumbnails');
                $post->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnails');
            }

            DB::commit();

            return to_route('admin.posts.index')->with('success', 'Cập nhật bài viết thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cập nhật bài viết thất bại: ' . $e->getMessage());

            return back()->withErrors([
                'error' => 'Có lỗi xảy ra khi cập nhật bài viết: ' . $e->getMessage(),
            ]);
        }
    }






    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        DB::beginTransaction();
        try {
            $post->delete();
            DB::commit();

            return back()->with('success', 'Admin đã xoá bài viết thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Post deletion failed: ' . $e->getMessage());

            return back()->withErrors('Xoá bài viết thất bại. Vui lòng thử lại.');
        }
    }




    public function destroyAll()
    {
        DB::beginTransaction();
        try {
            Post::query()->delete();
            DB::commit();

            return back()->with('success', 'Admin đã xoá tất cả bài viết.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk post deletion failed: ' . $e->getMessage());

            return back()->withErrors('Xoá tất cả bài viết thất bại. Vui lòng thử lại.');
        }
    }
}
