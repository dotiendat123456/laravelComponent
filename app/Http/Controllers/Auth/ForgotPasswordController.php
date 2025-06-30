<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendResetPasswordLink;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class ForgotPasswordController extends Controller
{
    /**
     * Hiển thị form nhập email để gửi link reset mật khẩu
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Xử lý gửi email chứa link reset
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        DB::beginTransaction();

        try {
            // Tìm user
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                throw new \Exception('Email không tồn tại trong hệ thống.');
            }

            // Tạo token
            $token = Str::random(60);

            // Cập nhật hoặc thêm token mới
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            // Gửi email bằng Job
            SendResetPasswordLink::dispatch($user, $token);

            DB::commit();

            return back()->with('status', 'Đã gửi liên kết đặt lại mật khẩu vào email của bạn.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reset password failed: ' . $e->getMessage());

            return back()->withErrors([
                'email' => 'Không thể gửi email đặt lại mật khẩu: ' . $e->getMessage(),
            ]);
        }
    }
}
