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
        $user = Auth::user();

        if (!$user) return $next($request); // Bỏ qua nếu chưa đăng nhập

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
                return $next($request); // OK, cho đi tiếp

            default:
                Auth::logout();
                return to_route('login')->withErrors([
                    'account_status' => 'Tài khoản không hợp lệ.'
                ]);
        }
    }
}
