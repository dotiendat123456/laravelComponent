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
        $query = User::query();

        if ($request->filled('name')) {
            $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $request->name . '%']);
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        $totalData = User::count();
        $totalFiltered = $query->count();

        $limit = intval($request->input('length'));
        $start = intval($request->input('start'));

        $users = $query
            ->offset($start)
            ->limit($limit)
            ->latest()
            ->get();

        $data = [];
        foreach ($users as $user) {
            $statusLabel = match ($user->status) {
                UserStatus::PENDING => '<span class="badge bg-secondary">' . $user->status->label() . '</span>',
                UserStatus::APPROVED => '<span class="badge bg-success">' . $user->status->label() . '</span>',
                UserStatus::REJECTED => '<span class="badge bg-danger">' . $user->status->label() . '</span>',
                UserStatus::LOCKED => '<span class="badge bg-dark">' . $user->status->label() . '</span>',
                default => '<span class="badge bg-light">Không rõ</span>',
            };

            $data[] = [
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'address' => $user->address,
                'status' => $statusLabel,
                'id' => $user->id,
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
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
