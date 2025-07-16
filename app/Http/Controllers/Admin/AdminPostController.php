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
use App\Http\Resources\Admin\PostResource;


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
        // Kiểm tra xem request có phải là Ajax không. Nếu không phải thì trả về lỗi 403.
        if (! $request->ajax()) {
            abort(403, 'Không hợp lệ.');
        }

        // Định nghĩa các cột ánh xạ theo thứ tự cột bên client DataTables gửi lên
        $columns = [
            0 => 'posts.id',          // ID bài viết
            1 => 'posts.title',       // Tiêu đề bài viết
            2 => 'users.email',       // Email người tạo bài viết (cần join bảng users)
            3 => 'posts.status',      // Trạng thái bài viết
            4 => 'posts.created_at',  // Ngày tạo
        ];

        // Lấy thông tin sắp xếp từ request
        $orderColIndex = $request->input('order.0.column'); // Lấy chỉ số cột sắp xếp
        $orderDir = $request->input('order.0.dir', 'asc');  // Lấy kiểu sắp xếp (asc|desc), mặc định asc
        $orderColumn = $columns[$orderColIndex] ?? 'posts.id'; // Nếu không có thì sắp theo posts.id

        // Khởi tạo query cơ bản từ bảng posts
        $query = Post::query();

        // Xác định xem có cần join bảng users không
        // Nếu sắp xếp theo email hoặc có filter theo email thì bắt buộc phải join
        $needJoin = $orderColumn === 'users.email' || $request->filled('email');

        if ($needJoin) {
            // Thực hiện join bảng users để lấy thông tin email người viết bài
            $query->join('users', 'posts.user_id', '=', 'users.id')
                ->select('posts.*', 'users.email as user_email') // Lấy tất cả cột của posts và thêm users.email
                ->groupBy('posts.id'); // Tránh lỗi khi dùng join + select nhiều bảng (MySQL yêu cầu groupBy)
        } else {
            // Nếu không cần join thì eager load quan hệ user để lấy email sau này
            $query->with('user');
        }

        // Lọc theo tiêu đề nếu có input title gửi lên
        if ($request->filled('title')) {
            $query->where('posts.title', 'like', "%{$request->title}%");
        }

        // Lọc theo email nếu có input email gửi lên (chỉ dùng được khi đã join)
        if ($request->filled('email')) {
            $query->where('users.email', 'like', "%{$request->email}%");
        }

        // Thêm điều kiện sắp xếp vào query
        $query->orderBy($orderColumn, $orderDir);

        // Lấy thông tin phân trang từ request
        $length = intval($request->input('length', 10)); // Số bản ghi mỗi trang, mặc định 10
        $start = intval($request->input('start', 0));    // Offset bắt đầu lấy bản ghi
        $page = ($start / $length) + 1;                   // Tính số trang hiện tại (vì paginate của Laravel dùng page)

        // Lấy dữ liệu với phân trang
        $posts = $query->paginate($length, ['*'], 'page', $page);

        // Trả về dữ liệu dưới dạng JSON đúng chuẩn DataTables yêu cầu
        return response()->json([
            'draw' => intval($request->input('draw')),               // Biến draw giúp client đồng bộ các request liên tiếp
            'recordsTotal' => Post::count(),                         // Tổng số bản ghi không filter
            'recordsFiltered' => (clone $query)->getCountForPagination(), // Tổng số bản ghi sau khi filter
            'data' => PostResource::collection($posts)->resolve(),   // Dữ liệu bài viết, qua PostResource để format lại
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
