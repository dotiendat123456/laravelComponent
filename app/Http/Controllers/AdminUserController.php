<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminUpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\UserStatus;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.users.index');
    }

    public function data(Request $request)
    {
        // Tạo query lấy tất cả user
        $query = User::query();

        // Nếu request có trường 'name', lọc theo CONCAT(first_name last_name)
        if ($request->filled('name')) {
            $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$request->name}%"]);
        }

        // Nếu request có trường 'email', lọc theo email
        if ($request->filled('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        // Đếm tổng số user trong bảng (không áp dụng filter)
        $totalData = User::count();

        // Đếm tổng số user sau khi filter (nếu có)
        $totalFiltered = $query->count();

        // Lấy limit (số dòng trên 1 trang) từ DataTables
        $limit = intval($request->input('length'));

        // Lấy offset (bắt đầu từ dòng thứ mấy)
        $start = intval($request->input('start'));

        // Ánh xạ cột nếu cần sắp xếp động sau này (hiện tại vẫn khóa latest)
        $columns = [
            0 => 'first_name',
            1 => 'email',
            2 => 'address',
            3 => 'status',
        ];

        // Nếu bạn muốn cho phép sắp xếp, bạn sẽ lấy:
        // $orderColIndex = $request->input('order.0.column');
        // $orderDir = $request->input('order.0.dir');
        // và $query->orderBy($columns[$orderColIndex], $orderDir)

        // Ở đây: ta KHÓA sắp xếp latest theo id mới nhất
        $users = $query
            ->offset($start)
            ->limit($limit)
            ->latest('id')
            ->get();

        // Tạo mảng dữ liệu trả về
        $data = [];
        foreach ($users as $user) {
            // Tạo badge status dạng HTML
            $statusLabel = match ($user->status) {
                UserStatus::PENDING => '<span class="badge bg-secondary">' . $user->status->label() . '</span>',
                UserStatus::APPROVED => '<span class="badge bg-success">' . $user->status->label() . '</span>',
                UserStatus::REJECTED => '<span class="badge bg-danger">' . $user->status->label() . '</span>',
                UserStatus::LOCKED => '<span class="badge bg-dark">' . $user->status->label() . '</span>',
                default => '<span class="badge bg-light">Không rõ</span>',
            };

            $data[] = [
                'name' => $user->first_name . ' ' . $user->last_name, // Tên đầy đủ
                'email' => $user->email,                              // Email
                'address' => $user->address,                          // Địa chỉ
                'status' => $statusLabel,                             // HTML badge trạng thái
                'id' => $user->id,                                    // ID cho nút sửa
            ];
        }

        // Trả về JSON chuẩn DataTables yêu cầu
        return response()->json([
            'draw' => intval($request->input('draw')), // Số lần request DataTables
            'recordsTotal' => $totalData,              // Tổng số user gốc
            'recordsFiltered' => $totalFiltered,       // Tổng số user sau filter
            'data' => $data,                           // Mảng dữ liệu trang hiện tại
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
}
