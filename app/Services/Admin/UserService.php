<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Enums\UserStatus;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UserService
{
    /**
     * Lấy danh sách user có filter, sắp xếp, phân trang cho DataTables.
     */
    public function getUsersData(Request $request)
    {
        $query = User::query();

        // Lọc theo tên (first_name + last_name)
        if ($request->filled('name')) {
            $query->whereRaw(
                "CONCAT(first_name, ' ', last_name) LIKE ?",
                ["%{$request->name}%"]
            );
        }

        // Lọc theo email
        if ($request->filled('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        // Cấu hình sắp xếp cột
        $columns = [
            0 => 'first_name',
            1 => 'email',
            2 => 'address',
            3 => 'status',
        ];

        $orderColIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc');

        if ($orderColIndex === null || !isset($columns[$orderColIndex])) {
            $query->orderByDesc('id');
        } else {
            $query->orderBy($columns[$orderColIndex], $orderDir);
        }

        // Phân trang
        $length = intval($request->input('length', 10));
        $start = intval($request->input('start', 0));
        $page = ($start / $length) + 1;

        return $query->paginate($length, ['*'], 'page', $page);
    }

    /**
     * Cập nhật thông tin người dùng.
     */
    public function updateUser(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            $result = $user->update([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'address'    => $data['address'] ?? null,
                'status'     => $data['status'],
            ]);

            if (!$result) {
                throw new \Exception('Không thể cập nhật user.');
            }

            return $user;
        });
    }

    /**
     * Khoá hoặc mở khoá user.
     */
    public function toggleUserStatus(User $user, string $action)
    {
        if (Auth::id() === $user->id) {
            throw new \Exception('Bạn không thể tự thay đổi trạng thái của chính mình!');
        }

        if ($user->role === UserRole::ADMIN && $action === 'lock') {
            throw new \Exception('Không thể khoá tài khoản ADMIN.');
        }

        $newStatus = match ($action) {
            'lock'   => UserStatus::LOCKED,
            'unlock' => UserStatus::PENDING,
            default  => $user->status,
        };

        $user->update(['status' => $newStatus]);

        return $newStatus;
    }
}
