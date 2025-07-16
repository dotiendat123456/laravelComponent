<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\AdminStorePostRequest;
use App\Http\Requests\Admin\AdminUpdatePostRequest;
use Illuminate\Support\Str;
use App\Enums\UserRole;
use App\Jobs\NotifyUserPostStatusJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\PostStatus;
use App\Http\Controllers\Controller;


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
        // Tạo query gốc lấy tất cả bài viết, eager load quan hệ user (tác giả)
        $query = Post::with('user');

        // Nếu có tham số 'title', thêm điều kiện tìm kiếm theo tiêu đề
        if ($request->filled('title')) {
            $query->where('title', 'like', "%{$request->title}%");
        }

        // Nếu có tham số 'email', lọc theo email user
        if ($request->filled('email')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('email', 'like', "%{$request->email}%");
            });
        }

        // Định nghĩa ánh xạ index cột của DataTables sang tên cột DB
        $columns = [
            0 => 'id',
            1 => 'title',
            2 => 'users.email',  // Email nằm ở bảng users
            3 => 'status',
            4 => 'created_at',
        ];

        // Lấy index cột sắp xếp và chiều sắp xếp
        $orderColIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc');

        // Xác định tên cột sẽ sort
        $orderColumn = $columns[$orderColIndex] ?? 'id';

        // Nếu sort theo email cần join bảng users
        if ($orderColumn === 'users.email') {
            $query->join('users', 'posts.user_id', '=', 'users.id')
                ->orderBy('users.email', $orderDir)
                ->select('posts.*'); // Tránh lỗi khi join
        } else {
            $query->orderBy($orderColumn, $orderDir);
        }

        // Tính số trang từ DataTables (start + length)
        $length = intval($request->input('length', 10));
        $start = intval($request->input('start', 0));
        $page = ($start / $length) + 1;

        // Dùng paginate + through để chuẩn hoá dữ liệu trả ra
        $posts = $query->paginate($length, ['*'], 'page', $page)
            ->through(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'email' => $post->user->email ?? '-', // Nếu chưa join thì đã eager load user
                    'status' => $post->status->label(),
                    'created_at' => $post->created_at->format('d/m/Y'),
                    'slug' => $post->slug,
                ];
            });

        // Trả JSON đúng chuẩn DataTables yêu cầu
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => Post::count(),      // Tổng bản ghi gốc (chưa filter)
            'recordsFiltered' => $posts->total(), // Tổng bản ghi sau filter
            'data' => $posts->items(),            // Dữ liệu trang hiện tại đã chuẩn hoá
        ]);
    }





    public function create()
    {
        $this->authorize('create', Post::class);
        return view('admin.posts.create');
    }



    public function store(AdminStorePostRequest $request)
    {
        $this->authorize('create', Post::class);

        DB::beginTransaction();

        try {
            // Tạo bài viết
            $post = Post::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
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
