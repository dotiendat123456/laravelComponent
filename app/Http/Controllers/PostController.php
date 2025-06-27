<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StorePostRequest;
use Illuminate\Support\Str;


class PostController extends Controller
{
    public function index()
    {
        $posts = Auth::user()->posts()->latest()->paginate(5); // bạn có thể eager load quan hệ nếu cần
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
