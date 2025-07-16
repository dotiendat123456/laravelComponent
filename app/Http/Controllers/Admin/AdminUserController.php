<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AdminUpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\UserStatus;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserResource;


class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.users.index');
    }



    public function data(Request $request)
    {
        if (! $request->ajax()) {
            abort(403, 'Không hợp lệ.');
        }
        $query = User::query();

        if ($request->filled('name')) {
            $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$request->name}%"]);
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        $columns = [
            0 => 'first_name',
            1 => 'email',
            2 => 'address',
            3 => 'status',
        ];

        $orderColIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc');

        if (
            $orderColIndex === null ||
            !isset($columns[$orderColIndex])
        ) {
            $query->orderByDesc('id');
        } else {
            $query->orderBy($columns[$orderColIndex], $orderDir);
        }

        $length = intval($request->input('length', 10));
        $start = intval($request->input('start', 0));
        $page = ($start / $length) + 1;

        $users = $query->paginate($length, ['*'], 'page', $page);

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $users->total(),
            'recordsFiltered' => $users->total(),
            'data' => UserResource::collection($users)->resolve(),
        ]);
    }



    public function edit(User $user)
    {
        $this->authorize('updateStatus', $user);

        return view('admin.users.edit', compact('user'));
    }



    public function update(AdminUpdateProfileRequest $request, User $user)
    {
        $this->authorize('updateStatus', $user);

        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $result = $user->update([
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'address'    => $validated['address'] ?? null,
                'status'     => $validated['status'],
            ]);

            if (!$result) {
                // Nếu update trả về false
                throw new \Exception('Không thể cập nhật user.');
            }

            DB::commit();

            return to_route('admin.users.index')->with('success', 'Cập nhật user thành công.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Lỗi cập nhật user: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Đã xảy ra lỗi khi cập nhật user, vui lòng thử lại!']);
        }
    }
    public function toggleStatus(User $user, Request $request)
    {
        // Không cho tự khoá hoặc chỉnh trạng thái chính mình
        if (Auth::id() === $user->id) {
            return response()->json([
                'message' => 'Bạn không thể tự thay đổi trạng thái của chính mình!'
            ], 403);
        }

        // Không cho khoá user Admin
        if ($user->role === UserRole::ADMIN && $request->action === 'lock') {
            return response()->json([
                'message' => 'Không thể khoá tài khoản ADMIN.'
            ], 403);
        }

        // Xác định trạng thái mới
        $newStatus = match ($request->action) {
            'lock'   => UserStatus::LOCKED,
            'unlock' => UserStatus::PENDING,
            default  => $user->status, // fallback: giữ nguyên
        };

        // Cập nhật bằng update()
        $user->update([
            'status' => $newStatus
        ]);

        return response()->json(['success' => true]);
    }
}
