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
use App\Jobs\NotifyUserPostStatusJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class AdminPostController extends Controller
{
    public function dashboard()
    {
        return view('admin.posts.dashboard');
    }
    public function index(Request $request)
    {
        $query = Post::query()->with('user');

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('email')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->email . '%');
            });
        }

        $posts = $query->latest()->paginate(5);

        return view('admin.posts.index', compact('posts'));
    }


    public function create()
    {
        return view('admin.posts.create');
    }



    public function store(StorePostRequest $request)
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
        $user = Auth::user();

        if ($post->user_id !== $user->id && !$user->isAdmin()) {
            abort(404); // Không phải chủ, không phải admin → chặn
        }

        return view('admin.posts.edit', compact('post'));
    }



    public function update(UpdatePostRequest $request, Post $post)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            if ($post->user_id !== $user->id && !$user->isAdmin()) {
                abort(404);
            }

            $post->title = $request->title;
            $post->description = $request->description;
            $post->content = $request->content;
            $post->publish_date = $request->publish_date;

            // Nếu title đổi → đổi slug
            if ($post->isDirty('title')) {
                $slug = Str::slug($request->title);
                $originalSlug = $slug;
                $count = 1;
                while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $post->slug = $slug;
            }

            // Lưu status cũ
            $oldStatus = $post->status;

            if ($user->isAdmin()) {
                $post->status = $request->validated('status');
            }

            $post->save();

            // Nếu status thay đổi → gửi mail
            if ($oldStatus != $post->status) {
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
        if (!$post) {
            return back()->withErrors(['error' => 'Bài viết không tồn tại']);
        }

        $post->delete();

        return back()->with('success', 'Admin đã xoá bài viết thành công.');
    }


    public function destroyAll()
    {
        Post::query()->delete();  // Xoá tất cả bài viết, không cần theo user

        return back()->with('success', 'Admin đã xoá tất cả bài viết.');
    }
}
