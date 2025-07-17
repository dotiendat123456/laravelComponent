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
        // Khởi tạo query lấy danh sách tất cả người dùng
        $query = User::query();

        // Lọc theo tên đầy đủ (gộp first_name + last_name)
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

        // Các cột có thể sắp xếp được trong bảng DataTables
        $columns = [
            0 => 'first_name', // Cột tên
            1 => 'email',      // Cột email
            2 => 'address',    // Cột địa chỉ
            3 => 'status',     // Cột trạng thái
        ];

        // Lấy thông tin sắp xếp từ DataTables
        $orderColIndex = $request->input('order.0.column'); // Vị trí cột được sắp xếp
        $orderDir = $request->input('order.0.dir', 'asc');  // Chiều sắp xếp (asc/desc), mặc định là asc

        // Nếu không có cột sắp xếp hoặc không hợp lệ thì mặc định sắp xếp theo ID giảm dần (mới nhất lên đầu)
        if ($orderColIndex === null || !isset($columns[$orderColIndex])) {
            $query->orderByDesc('id');
        } else {
            $query->orderBy($columns[$orderColIndex], $orderDir);
        }

        // Xử lý phân trang theo chuẩn DataTables
        $length = intval($request->input('length', 10)); // Số lượng bản ghi mỗi trang (default 10)
        $start = intval($request->input('start', 0));    // Vị trí bắt đầu (offset)
        $page = ($start / $length) + 1;                  // Tính số trang hiện tại

        // Trả về danh sách người dùng đã phân trang
        return $query->paginate($length, ['*'], 'page', $page);
    }

    /**
     * Cập nhật thông tin người dùng.
     */
    public function updateUser(User $user, array $data)
    {
        DB::beginTransaction();

        try {
            $result = $user->update([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'address'    => $data['address'] ?? null,
                'status'     => $data['status'],
            ]);

            if (!$result) {
                throw new \Exception('Không thể cập nhật user.');
            }

            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
