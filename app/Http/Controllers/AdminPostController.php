<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\AdminStorePostRequest;
use App\Http\Requests\AdminUpdatePostRequest;
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

    public function index()
    {
        return view('admin.posts.index');
    }

    public function data(Request $request)
    {
        $query = Post::query()->with('user');

        if ($request->filled('title')) {
            $query->where('title', 'like', "%{$request->title}%");
        }

        if ($request->filled('email')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('email', 'like', "%{$request->email}%");
            });
        }

        $totalData = Post::count();
        $totalFiltered = $query->count();

        $limit = intval($request->input('length'));
        $start = intval($request->input('start'));

        $posts = $query
            ->offset($start)
            ->limit($limit)
            ->latest('id')
            ->get();

        $data = [];
        foreach ($posts as $post) {
            $data[] = [
                'id' => $post->id,
                'title' => $post->title,
                'email' => $post->user->email,
                'status' => $post->status->label(),
                'created_at' => $post->created_at->format('d/m/Y'),
                'slug' => $post->slug,
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }






    public function create()
    {
        return view('admin.posts.create');
    }



    public function store(AdminStorePostRequest $request)
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
