<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendResetPasswordLink;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\Auth\AuthService;



class ForgotPasswordController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
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
        try {
            $this->authService->sendResetLink($request->validated());

            return back()->with('status', 'Đã gửi liên kết đặt lại mật khẩu vào email của bạn.');
        } catch (\Throwable $e) {
            return back()->withErrors([
                'email' => 'Không thể gửi email đặt lại mật khẩu: ' . $e->getMessage(),
            ]);
        }
    }
}
