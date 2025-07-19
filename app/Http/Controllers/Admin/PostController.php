<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;                                // Model bài viết
use App\Http\Controllers\Controller;                // Kế thừa Controller gốc của Laravel
use App\Http\Requests\Admin\StorePostRequest;  // Form Request validate khi tạo bài viết
use App\Http\Requests\Admin\UpdatePostRequest; // Form Request validate khi update bài viết
use App\Http\Resources\Admin\PostResource;          // Resource định dạng data cho DataTables
use App\Services\Admin\PostService;                 // Service xử lý nghiệp vụ bài viết admin
use Illuminate\Http\Request;                        // Request HTTP
use Illuminate\Support\Facades\Auth;                // Lấy user đang đăng nhập
use Illuminate\Support\Facades\Log;                 // Ghi log

class PostController extends Controller
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
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = [
                'draw' => (int) $request->input('draw', 0),
                'title' => $request->input('title'),
                'email' => $request->input('email'),
                'status' => $request->input('status'),
                'order_column' => $request->input('order.0.column'),
                'order_dir' => $request->input('order.0.dir', 'desc'),
                'length' => (int) $request->input('length', 10),
                'search' => $request->input('search.value'),
                'columns' => $request->input('columns'),
            ];

            $posts = $this->postService->getPostsData($data);

            return response()->json([
                'draw' => $data['draw'],
                'recordsTotal' => $posts->total(),
                'recordsFiltered' => $posts->total(),
                'data' => PostResource::collection($posts)->resolve(),
            ]);
        }

        return view('admin.posts.index');
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
    public function store(StorePostRequest $request)
    {
        $this->authorize('create', Post::class);

        try {
            $data = $request->validated();
            $data['thumbnail'] = $request->file('thumbnail'); // Đưa thumbnail vào mảng $data

            $this->postService->createPost($data);

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
    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('updateStatus', $post);

        try {
            $data = $request->validated();

            if (Auth::user()->isAdmin()) {
                $data['status'] = $request->validated('status');
            }

            // Truyền ID vào mảng data để Service biết post nào
            $data['id'] = $post->id;

            // Đưa thumbnail vào data
            $data['thumbnail'] = $request->file('thumbnail');

            $this->postService->updatePost($data);

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
