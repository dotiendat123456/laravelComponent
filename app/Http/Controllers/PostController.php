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
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $query = Post::query()->with('user');

            if ($request->filled('title')) {
                $query->where('title', 'like', "%{$request->title}%");
            }
        } else {
            $query = $user->posts()->with('user');

            if ($request->filled('title')) {
                $query->where('title', 'like', "%{$request->title}%");
            }
        }

        $posts = $query->latest()->paginate(5)->withQueryString();

        if ($request->ajax()) {
            return view('posts._table', compact('posts'))->render(); //.render() sẽ biến View thành chuỗi HTML, chứ không trả về Response HTTP trực tiếp.
        }

        return view('posts.index', compact('posts'));
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
        $posts = Post::where('status', PostStatus::APPROVED->value)
            ->where('publish_date', '<=', now())
            ->latest('publish_date')
            ->paginate(10);

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

        if (!$user) {
            return back()->withErrors(['error' => 'Bạn chưa đăng nhập']);
        }

        if ($user->posts()->count() === 0) {
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
