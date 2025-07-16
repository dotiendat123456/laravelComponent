<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUpdateProfileRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminUserController extends Controller
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
    public function index()
    {
        return view('admin.users.index');
    }

    /**
     * Lấy dữ liệu user trả về cho DataTables (Ajax).
     */
    public function data(Request $request)
    {
        if (! $request->ajax()) {
            abort(403, 'Không hợp lệ.');
        }

        $users = $this->userService->getUsersData($request);

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $users->total(),
            'recordsFiltered' => $users->total(),
            'data' => UserResource::collection($users)->resolve(),
        ]);
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
            $this->userService->updateUser($user, $request->validated());

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
            $newStatus = $this->userService->toggleUserStatus($user, $request->action);

            return response()->json(['success' => true, 'status' => $newStatus]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        }
    }
}
