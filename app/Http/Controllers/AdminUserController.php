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
        // Bước 1: Tạo query gốc lấy tất cả user
        $query = User::query();

        // Bước 2: Nếu request có trường 'name', lọc theo CONCAT(first_name, last_name)
        if ($request->filled('name')) {
            $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$request->name}%"]);
        }

        // Bước 3: Nếu request có trường 'email', lọc theo email
        if ($request->filled('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        // Bước 4: Ánh xạ cột để cho phép sort nếu cần
        $columns = [
            0 => 'first_name',
            1 => 'email',
            2 => 'address',
            3 => 'status',
        ];

        // Nếu muốn sắp xếp động theo cột, có thể mở các dòng sau:
        $orderColIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir');

        if (
            $orderColIndex === null ||
            !isset($columns[$orderColIndex])
        ) {
            $query->orderByDesc('id');
        } else {
            $query->orderBy($columns[$orderColIndex], $orderDir);
        }

        // Bước 5: Tính limit và trang hiện tại theo DataTables
        $length = intval($request->input('length', 10));
        $start = intval($request->input('start', 0));
        $page = ($start / $length) + 1;

        // Bước 6: Sử dụng paginate + through để chuẩn hoá dữ liệu trước khi trả ra
        $users = $query->paginate($length, ['*'], 'page', $page)
            ->through(function ($user) {
                $statusLabel = match ($user->status) {
                    UserStatus::PENDING => '<span class="badge bg-secondary">' . $user->status->label() . '</span>',
                    UserStatus::APPROVED => '<span class="badge bg-success">' . $user->status->label() . '</span>',
                    UserStatus::REJECTED => '<span class="badge bg-danger">' . $user->status->label() . '</span>',
                    UserStatus::LOCKED => '<span class="badge bg-dark">' . $user->status->label() . '</span>',
                    default => '<span class="badge bg-light">Không rõ</span>',
                };

                return [
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'address' => $user->address,
                    'status' => $statusLabel,
                    'id' => $user->id,
                ];
            });

        // Bước 7: Trả JSON đúng format DataTables yêu cầu
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $users->total(),      // Tổng số user gốc
            'recordsFiltered' => $users->total(),   // Tổng số user sau filter
            'data' => $users->items(),              // Mảng dữ liệu trang hiện tại
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
