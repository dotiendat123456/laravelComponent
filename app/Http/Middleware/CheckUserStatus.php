<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        // Trả về đối tượng người dùng (User model) đang đăng nhập hiện tại.
        $user = Auth::user();

        if (!$user) return $next($request);

        // Dùng enum để kiểm tra trạng thái tài khoản
        switch ($user->status) {
            case UserStatus::LOCKED:
                Auth::logout();
                return to_route('login')->withErrors([
                    'account_status' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.'
                ]);

            case UserStatus::PENDING:
                Auth::logout();
                return to_route('login')->withErrors([
                    'account_status' => 'Tài khoản của bạn đang chờ phê duyệt.'
                ]);

            case UserStatus::REJECTED:
                Auth::logout();
                return to_route('login')->withErrors([
                    'account_status' => 'Tài khoản của bạn đã bị từ chối.'
                ]);

            case UserStatus::APPROVED:
                // Cho phép tiếp tục
                return $next($request);
        }

        // Phòng trường hợp không đúng enum
        Auth::logout();
        return to_route('login')->withErrors([
            'account_status' => 'Tài khoản không hợp lệ.'
        ]);
    }
}
