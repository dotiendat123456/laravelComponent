<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Support\Str;
use App\Enums\UserRole;
use App\Enums\PostStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;




class PostController extends Controller
{

    public function index()
    {
        return view('posts.index');
    }


    public function data(Request $request)
    {
        // Lấy người dùng đang đăng nhập
        $user = Auth::user();

        // Query gốc: chỉ lấy các bài viết thuộc về user đó
        $query = $user->posts()->with('media'); // Eager load media nếu dùng Spatie Media Library

        // Nếu có tìm kiếm, áp dụng điều kiện tìm kiếm theo title hoặc description
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Định nghĩa thứ tự các cột, cần khớp với mảng columns của DataTables JS
        $columns = [
            0 => 'id',
            1 => 'thumbnail',    // Cột này chỉ hiển thị, không sắp xếp
            2 => 'title',
            3 => 'description',
            4 => 'publish_date',
            5 => 'status',
        ];

        // Xác định cột được yêu cầu sắp xếp và hướng sắp xếp (asc/desc)
        $orderColIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir');

        // Nếu không có cột hoặc cột đó là thumbnail thì fallback sắp theo ID giảm dần
        if (
            $orderColIndex === null ||
            !isset($columns[$orderColIndex]) ||
            $columns[$orderColIndex] === 'thumbnail'
        ) {
            $query->orderByDesc('id');
        } else {
            $query->orderBy($columns[$orderColIndex], $orderDir);
        }

        // Lấy số bản ghi trên mỗi trang và tính toán trang hiện tại theo DataTables
        $length = intval($request->input('length', 10));
        $start = intval($request->input('start', 0));
        $page = ($start / $length) + 1;

        // Sử dụng paginate để Laravel tự xử lý limit và offset
        // Sử dụng through() để chuẩn hoá dữ liệu trước khi trả ra
        $posts = $query->paginate($length, ['*'], 'page', $page)
            ->through(function ($post) {
                return [
                    'id' => $post->id,
                    'slug' => $post->slug,
                    'thumbnail' => $post->getFirstMediaUrl('thumbnails'),
                    'title' => $post->title,
                    'description' => Str::limit($post->description, 50),
                    'publish_date' => optional($post->publish_date)->format('d/m/Y'),
                    'status' => $post->status->label(),
                ];
            });

        // Trả về JSON đúng cấu trúc mà DataTables server-side yêu cầu
        return response()->json([
            'draw' => intval($request->input('draw')),         // Tham số để DataTables đồng bộ thứ tự Ajax
            'recordsTotal' => $posts->total(),                 // Tổng số bản ghi gốc (chưa lọc)
            'recordsFiltered' => $posts->total(),              // Tổng số bản ghi sau khi áp dụng filter
            'data' => $posts->items(),                         // Dữ liệu đã được chuẩn hoá
        ]);
    }







    public function create()
    {
        $this->authorize('create', Post::class);

        return view('posts.create');
    }



    public function store(StorePostRequest $request)
    {
        $this->authorize('create', Post::class);

        DB::beginTransaction();

        try {
            // Tạo bài viết — KHÔNG cần tự tạo slug
            $post = Post::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'content' => $request->content,
                'publish_date' => $request->publish_date,
            ]);

            // Nếu có thumbnail thì lưu bằng Spatie Media
            if ($request->hasFile('thumbnail')) {
                $post->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnails');
            }

            DB::commit();

            return to_route('posts.index')->with('success', 'Tạo bài viết thành công');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi tạo bài viết: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Không thể tạo bài viết, vui lòng thử lại.']);
        }
    }




    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


    public function publicIndex()
    {
        $posts = Post::status(PostStatus::APPROVED->value)
            ->where('publish_date', '<=', now())
            ->latest('publish_date')
            ->paginate(2);

        return view('news.index', compact('posts'));
    }




    public function publicShow(Post $post)
    {
        if (
            $post->status !== PostStatus::APPROVED ||
            $post->publish_date === null ||
            $post->publish_date->isFuture()
        ) {
            abort(404);
        }

        return view('news.show', compact('post'));
    }




    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return view('posts.edit', compact('post'));
    }



    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        DB::beginTransaction();

        try {
            $user = Auth::user();

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

            if ($request->hasFile('thumbnail')) {
                $post->clearMediaCollection('thumbnails');
                $post->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnails');
            }

            DB::commit();

            return to_route('posts.index')->with('success', 'Cập nhật bài viết thành công!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật bài viết: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Đã xảy ra lỗi, vui lòng thử lại!']);
        }
    }



    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        DB::beginTransaction();
        try {
            $post->delete();
            DB::commit();

            return back()->with('success', 'Xóa bài viết thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Post deletion failed: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Xóa bài viết thất bại. Vui lòng thử lại.']);
        }
    }



    public function destroyAll()
    {
        $user = Auth::user();


        if ($user->posts->count() === 0) {
            return back()->withErrors(['error' => 'Không có bài viết nào để xoá']);
        }

        DB::beginTransaction();
        try {
            $user->posts()->delete();
            DB::commit();

            return back()->with('success', 'Đã xoá tất cả bài viết của bạn');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk post deletion failed: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Xoá tất cả bài viết thất bại. Vui lòng thử lại.']);
        }
    }
}
