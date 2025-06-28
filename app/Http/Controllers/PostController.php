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


class PostController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $posts = Post::latest()->paginate(5);
        } else {
            $posts = $user->posts()->latest()->paginate(5);
        }

        return view('posts.index', compact('posts'));
    }



    public function create()
    {
        return view('posts.create');
    }



    public function store(StorePostRequest $request)
    {
        // Tạo slug từ title
        $slug = Str::slug($request->title);

        // Kiểm tra slug có trùng không, nếu có thì thêm số tăng dần
        $originalSlug = $slug;
        $count = 1;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
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

        if (!$post) {
            return back()->withErrors(['error' => 'Không thể tạo bài viết, vui lòng thử lại.']);
        }

        return to_route('posts.index')->with('success', 'Tạo bài viết thành công');
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
        if ($post->user_id !== Auth::id()) {
            abort(404); // Chỉ owner mới vào được
        }

        return view('posts.edit', compact('post'));
    }



    public function update(UpdatePostRequest $request, Post $post)
    {
        //  Chỉ chủ bài viết được sửa
        if ($post->user_id !== Auth::id()) {
            abort(404);
        }

        //  Gán dữ liệu cơ bản
        $post->title = $request->title;
        $post->description = $request->description;
        $post->content = $request->content;
        $post->publish_date = $request->publish_date;

        //  Nếu title đổi thì sinh slug mới, đảm bảo unique
        if ($post->isDirty('title')) {
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $count = 1;
            while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $post->slug = $slug;
        }

        //  Nếu người chỉnh là Admin thì cho phép chỉnh status

        if ($request->user()->isAdmin()) {
            $post->status = $request->validated('status');
        }



        $post->save();


        //  Thumbnail mới? → Xoá cũ & gán mới
        if ($request->hasFile('thumbnail')) {
            $post->clearMediaCollection('thumbnails');
            $post->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnails');
        }

        return to_route('posts.index')->with('success', 'Cập nhật bài viết thành công!');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if (!$post) {
            return back()->withErrors(['error' => 'Bài viết không tồn tại']);
        }

        if ($post->user_id !== Auth::id()) { // Dùng Facade
            return back()->withErrors(['error' => 'Bạn không có quyền xoá bài viết này']);
        }

        $post->delete();

        return back()->with('success', 'Xóa bài viết thành công');
    }

    public function destroyAll()
    {
        $user = Auth::user();

        if (!$user) {
            return back()->withErrors(['error' => 'Bạn chưa đăng nhập']);
        }

        if ($user->posts()->count() === 0) {
            return back()->withErrors(['error' => 'Không có bài viết nào để xoá']);
        }

        $user->posts()->delete();

        return back()->with('success', 'Đã xoá tất cả bài viết của bạn');
    }
}
