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
use App\Http\Resources\PostResource;



class PostController extends Controller
{

    public function index()
    {
        return view('posts.index');
    }



    public function data(Request $request)
    {
        // Nếu request không phải là Ajax thì trả về lỗi 403 (bảo vệ API khỏi truy cập không hợp lệ)
        if (! $request->ajax()) {
            abort(403, 'Không hợp lệ.');
        }

        // Lấy thông tin user hiện đang đăng nhập
        $user = Auth::user();

        // Lấy danh sách bài viết của user đó, eager load thêm 'media' (nếu dùng Spatie Media Library)
        $query = $user->posts()->with('media');

        // Nếu có tham số tìm kiếm (search box DataTables), lọc theo title hoặc description
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');

            // Thêm điều kiện tìm kiếm bằng cách gộp nhiều where bằng orWhere
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Ánh xạ cột bên DataTables sang cột trong CSDL
        // Chú ý: 'thumbnail' là cột hiển thị ảnh, không sắp xếp được
        $columns = [
            0 => 'id',
            1 => 'thumbnail',       // Không thực sự tồn tại trong DB, chỉ dùng hiển thị
            2 => 'title',
            3 => 'description',
            4 => 'publish_date',
            5 => 'status',
        ];

        // Lấy thông tin cột cần sắp xếp từ request gửi lên (order[0][column], order[0][dir])
        $orderColIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc'); // Mặc định là ASC nếu không có

        // Kiểm tra nếu không có cột sắp xếp hợp lệ hoặc đang sắp xếp theo 'thumbnail' → sắp theo id DESC mặc định
        if (
            $orderColIndex === null ||
            !isset($columns[$orderColIndex]) ||
            $columns[$orderColIndex] === 'thumbnail'
        ) {
            $query->orderByDesc('id');
        } else {
            // Ngược lại thì sắp xếp theo cột tương ứng do client gửi lên
            $query->orderBy($columns[$orderColIndex], $orderDir);
        }

        // Phân trang:

        // Số bản ghi mỗi trang (mặc định 10 nếu client không gửi lên)
        $length = intval($request->input('length', 10));

        // Vị trí bắt đầu (start) theo DataTables
        $start = intval($request->input('start', 0));

        // Tính số trang hiện tại
        // Do paginate trong Laravel nhận số trang chứ không phải offset nên phải tính
        $page = ($start / $length) + 1;

        // Lấy dữ liệu phân trang với thông tin page hiện tại và perPage = length
        $posts = $query->paginate($length, ['*'], 'page', $page);

        // Trả về JSON đúng chuẩn định dạng DataTables expects
        return response()->json([
            'draw' => intval($request->input('draw')),           // Tham số dùng để đồng bộ với client
            'recordsTotal' => $posts->total(),                    // Tổng số bản ghi (không phân trang)
            'recordsFiltered' => $posts->total(),                 // Số bản ghi sau khi filter (ở đây bằng luôn vì không tách riêng total với filtered, nếu có filter khác cần tách)
            'data' => PostResource::collection($posts)->resolve(), // Dữ liệu dạng mảng (nhờ PostResource)
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
