<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserStatus;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        // Nếu chưa đăng nhập, không can thiệp - middleware 'auth' sẽ xử lý
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Nếu status không hợp lệ, logout và redirect về login
        if (
            $user->status === UserStatus::Locked
            || $user->status === UserStatus::Pending
            || $user->status === UserStatus::Rejected
        ) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login_error' => $user->status->label(),
            ]);
        }

        // Nếu status hợp lệ
        return $next($request);
    }
}
