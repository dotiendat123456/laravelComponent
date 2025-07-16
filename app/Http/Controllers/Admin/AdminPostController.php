<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;                                // Model bài viết
use App\Http\Controllers\Controller;                // Kế thừa Controller gốc của Laravel
use App\Http\Requests\Admin\AdminStorePostRequest;  // Form Request validate khi tạo bài viết
use App\Http\Requests\Admin\AdminUpdatePostRequest; // Form Request validate khi update bài viết
use App\Http\Resources\Admin\PostResource;          // Resource định dạng data cho DataTables
use App\Services\Admin\PostService;                 // Service xử lý nghiệp vụ bài viết admin
use Illuminate\Http\Request;                        // Request HTTP
use Illuminate\Support\Facades\Auth;                // Lấy user đang đăng nhập
use Illuminate\Support\Facades\Log;                 // Ghi log

class AdminPostController extends Controller
{
    protected $postService;

    /**
     * Inject PostService vào Controller bằng DI (Dependency Injection).
     */
    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Hiển thị dashboard quản lý bài viết.
     */
    public function dashboard()
    {
        return view('admin.posts.dashboard');
    }

    /**
     * Hiển thị danh sách bài viết admin.
     * Data sẽ load qua Ajax (dataTable).
     */
    public function index()
    {
        return view('admin.posts.index');
    }

    /**
     * Trả về dữ liệu bài viết cho DataTables (Ajax).
     * Có phân trang, lọc, sắp xếp.
     */
    public function data(Request $request)
    {
        // Check nếu không phải Ajax thì trả về lỗi 403
        if (! $request->ajax()) {
            abort(403, 'Không hợp lệ.');
        }

        // Gọi Service để lấy data bài viết
        $posts = $this->postService->getPostsData($request);

        // Trả về JSON chuẩn cho DataTables
        return response()->json([
            'draw' => intval($request->input('draw')), // Biến giúp DataTables phân biệt request
            'recordsTotal' => Post::count(),            // Tổng số bài viết
            'recordsFiltered' => $posts->total(),       // Tổng số sau khi filter
            'data' => PostResource::collection($posts)->resolve(), // Format data qua Resource
        ]);
    }

    /**
     * Hiển thị form tạo bài viết.
     */
    public function create()
    {
        $this->authorize('create', Post::class); // Kiểm tra quyền

        return view('admin.posts.create');
    }

    /**
     * Lưu bài viết mới từ form.
     */
    public function store(AdminStorePostRequest $request)
    {
        $this->authorize('create', Post::class);

        try {
            // Gọi Service để tạo bài viết
            $this->postService->createPost(
                $request->validated(),                 // Dữ liệu hợp lệ
                $request->file('thumbnail')            // File ảnh
            );

            return to_route('admin.posts.index')->with('success', 'Tạo bài viết thành công');
        } catch (\Throwable $e) {
            Log::error('Lỗi tạo bài viết: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Đã xảy ra lỗi khi tạo bài viết']);
        }
    }

    /**
     * Hiển thị form chỉnh sửa bài viết.
     */
    public function edit(Post $post)
    {
        $this->authorize('update', $post); // Check quyền

        return view('admin.posts.edit', compact('post'));
    }

    /**
     * Cập nhật bài viết.
     */
    public function update(AdminUpdatePostRequest $request, Post $post)
    {
        $this->authorize('updateStatus', $post); // Check quyền đổi trạng thái

        try {
            $data = $request->validated();

            // Nếu là Admin thì được sửa status
            if (Auth::user()->isAdmin()) {
                $data['status'] = $request->validated('status');
            }

            // Gọi service để update
            $this->postService->updatePost(
                $post,
                $data,
                $request->file('thumbnail')
            );

            return to_route('admin.posts.index')->with('success', 'Cập nhật bài viết thành công!');
        } catch (\Throwable $e) {
            Log::error('Cập nhật bài viết thất bại: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Cập nhật bài viết thất bại']);
        }
    }

    /**
     * Xoá 1 bài viết.
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        try {
            $this->postService->deletePost($post);

            return back()->with('success', 'Đã xoá bài viết thành công');
        } catch (\Throwable $e) {
            Log::error('Xoá bài viết thất bại: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Xoá bài viết thất bại']);
        }
    }

    /**
     * Xoá tất cả bài viết.
     */
    public function destroyAll()
    {
        try {
            $this->postService->deleteAllPosts();

            return back()->with('success', 'Đã xoá tất cả bài viết');
        } catch (\Throwable $e) {
            Log::error('Xoá tất cả bài viết thất bại: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Xoá tất cả bài viết thất bại']);
        }
    }
}
