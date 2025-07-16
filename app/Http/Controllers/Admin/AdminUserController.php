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
        // Kiểm tra nếu không phải là Ajax request thì trả về lỗi 403
        if (! $request->ajax()) {
            abort(403, 'Không hợp lệ.');
        }

        // Khởi tạo query lấy dữ liệu từ bảng users
        $query = User::query();

        // Lọc theo họ và tên (kết hợp first_name và last_name)
        if ($request->filled('name')) {
            $query->whereRaw(
                "CONCAT(first_name, ' ', last_name) LIKE ?", // Ghép chuỗi first_name + last_name để tìm kiếm toàn tên
                ["%{$request->name}%"]                      // Thêm dấu % để thực hiện tìm kiếm LIKE
            );
        }

        // Lọc theo email nếu có nhập vào
        if ($request->filled('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        // Định nghĩa các cột tương ứng với thứ tự gửi từ client DataTables
        $columns = [
            0 => 'first_name',   // Họ tên (chỉ lấy first_name, không phải full name)
            1 => 'email',        // Email
            2 => 'address',      // Địa chỉ
            3 => 'status',       // Trạng thái (ví dụ: active, inactive, pending)
        ];

        // Lấy thông tin sắp xếp từ request DataTables
        $orderColIndex = $request->input('order.0.column'); // Vị trí cột sắp xếp
        $orderDir = $request->input('order.0.dir', 'asc');  // Chiều sắp xếp (asc hoặc desc)

        // Xử lý sắp xếp:
        if (
            $orderColIndex === null ||                      // Nếu không có cột sắp xếp gửi lên
            !isset($columns[$orderColIndex])                // Hoặc chỉ số cột nằm ngoài mảng $columns
        ) {
            $query->orderByDesc('id');                      // Mặc định sắp xếp theo id DESC
        } else {
            $query->orderBy($columns[$orderColIndex], $orderDir); // Sắp xếp theo cột tương ứng
        }

        // Xử lý phân trang
        $length = intval($request->input('length', 10));   // Số bản ghi mỗi trang (mặc định 10)
        $start = intval($request->input('start', 0));      // Vị trí bắt đầu lấy dữ liệu
        $page = ($start / $length) + 1;                    // Tính số trang hiện tại vì Laravel dùng paginate

        // Lấy danh sách users theo phân trang
        $users = $query->paginate($length, ['*'], 'page', $page);

        // Trả về kết quả JSON đúng chuẩn định dạng DataTables yêu cầu
        return response()->json([
            'draw' => intval($request->input('draw')),                   // Biến giúp client đồng bộ request/response
            'recordsTotal' => $users->total(),                           // Tổng số bản ghi (không filter)
            'recordsFiltered' => $users->total(),                        // Số bản ghi sau khi filter (ở đây bằng nhau vì paginate đã tính sẵn)
            'data' => UserResource::collection($users)->resolve(),       // Dữ liệu trả về dạng array nhờ UserResource
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
