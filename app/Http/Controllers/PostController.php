<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected $postService;

    /**
     * Inject PostService qua constructor.
     */
    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Hiển thị danh sách bài viết (user).
     */
    public function index()
    {
        return view('posts.index');
    }

    /**
     * Lấy dữ liệu bài viết của user (dạng JSON cho DataTables).
     */
    public function data(Request $request)
    {
        // Kiểm tra xem request có phải là AJAX không
        if (! $request->ajax()) {
            abort(403, 'Không hợp lệ.'); // Trả về lỗi 403 nếu request không hợp lệ
        }

        // Gọi service để lấy danh sách bài viết của user hiện tại (đã phân trang, tìm kiếm, sắp xếp)
        $posts = $this->postService->getUserPostsData($request);

        // Trả về dữ liệu theo format chuẩn của DataTables
        return response()->json([
            'draw' => intval($request->input('draw')), // Số lần gọi AJAX, dùng để phân biệt các lần request
            'recordsTotal' => $posts->total(), // Tổng số bài viết (trước khi filter)
            'recordsFiltered' => $posts->total(), // Số bản ghi sau khi filter (ở đây bằng luôn total do đã filter trong Service)
            'data' => PostResource::collection($posts)->resolve(), // Chuyển dữ liệu bài viết thành resource JSON
        ]);
    }

    /**
     * Hiển thị form tạo bài viết.
     */
    public function create()
    {
        $this->authorize('create', Post::class);

        return view('posts.create');
    }

    /**
     * Lưu bài viết mới.
     */
    public function store(StorePostRequest $request)
    {
        $this->authorize('create', Post::class);

        try {
            $this->postService->createPost(
                $request->validated(),
                $request->file('thumbnail')
            );

            return to_route('posts.index')->with('success', 'Tạo bài viết thành công');
        } catch (\Throwable $e) {
            Log::error('Lỗi tạo bài viết: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Không thể tạo bài viết, vui lòng thử lại.']);
        }
    }

    /**
     * Hiển thị form chỉnh sửa bài viết.
     */
    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return view('posts.edit', compact('post'));
    }

    /**
     * Cập nhật bài viết.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        try {
            $data = $request->validated();

            if (Auth::user()->isAdmin()) {
                $data['status'] = $request->validated('status');
            }

            $this->postService->updatePost($post, $data, $request->file('thumbnail'));

            return to_route('posts.index')->with('success', 'Cập nhật bài viết thành công!');
        } catch (\Throwable $e) {
            Log::error('Lỗi cập nhật bài viết: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Đã xảy ra lỗi, vui lòng thử lại!']);
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

            return back()->with('success', 'Xóa bài viết thành công');
        } catch (\Throwable $e) {
            Log::error('Xoá bài viết thất bại: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Xoá bài viết thất bại']);
        }
    }

    /**
     * Xoá tất cả bài viết của user.
     */
    public function destroyAll()
    {
        $user = Auth::user();

        if ($user->posts->count() === 0) {
            return back()->withErrors(['error' => 'Không có bài viết nào để xoá']);
        }

        try {
            $this->postService->deleteAllUserPosts();

            return back()->with('success', 'Đã xoá tất cả bài viết của bạn');
        } catch (\Throwable $e) {
            Log::error('Xoá tất cả bài viết thất bại: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Xoá tất cả bài viết thất bại']);
        }
    }

    /**
     * Trang danh sách bài viết công khai.
     */
    public function publicIndex()
    {
        $posts = $this->postService->getPublicPosts();

        return view('news.index', compact('posts'));
    }

    /**
     * Trang chi tiết bài viết công khai.
     */
    public function publicShow(Post $post)
    {
        if (! $this->postService->isPublicPost($post)) {
            abort(404);
        }

        return view('news.show', compact('post'));
    }
}
