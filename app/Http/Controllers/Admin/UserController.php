<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUpdateProfileRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $userService;

    /**
     * Inject UserService bằng Dependency Injection.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Hiển thị giao diện danh sách user admin.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Chuẩn bị mảng $data truyền vào service
            $data = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'status' => $request->input('status'),
                'order_column' => $request->input('order.0.column'),
                'order_dir' => $request->input('order.0.dir', 'asc'),
                'length' => intval($request->input('length', 10)),
            ];

            // Gọi service với mảng $data
            $users = $this->userService->getUsersData($data);

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $users->total(),
                'recordsFiltered' => $users->total(),
                'data' => UserResource::collection($users)->resolve(),
            ]);
        }

        return view('admin.users.index');
    }



    /**
     * Hiển thị form chỉnh sửa user.
     */
    public function edit(User $user)
    {
        $this->authorize('updateStatus', $user);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Cập nhật thông tin user.
     */
    public function update(AdminUpdateProfileRequest $request, User $user)
    {
        $this->authorize('updateStatus', $user);

        try {
            $data = $request->validated();
            $data['id'] = $user->id; // Thêm id vào mảng data

            $this->userService->updateUser($data);

            return to_route('admin.users.index')->with('success', 'Cập nhật user thành công.');
        } catch (\Throwable $e) {
            Log::error('Lỗi cập nhật user: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Đã xảy ra lỗi khi cập nhật user']);
        }
    }


    /**
     * Khoá / Mở khoá user.
     */
    public function toggleStatus(User $user, Request $request)
    {
        try {
            $data = [
                'id' => $user->id,
                'action' => $request->input('action'),
            ];

            $newStatus = $this->userService->toggleUserStatus($data);

            return response()->json(['success' => true, 'status' => $newStatus]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        }
    }
}
