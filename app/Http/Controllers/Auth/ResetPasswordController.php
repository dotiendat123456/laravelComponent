<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\Request;
use App\Services\Auth\AuthService;


class ResetPasswordController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    // public function reset(ResetPasswordRequest $request)
    // {
    //     $status = Password::reset(
    //         $request->only('email', 'password', 'password_confirmation', 'token'),
    //         function ($user, $password) {
    //             $user->password = $password;
    //             $user->save();
    //         }
    //     );

    //     return $status === Password::PASSWORD_RESET
    //         ? to_route('login')->with('success', 'Mật khẩu của bạn đã được đặt lại.')
    //         : back()->withErrors(['error_reset' => 'Mã thông báo đặt lại mật khẩu này không hợp lệ.']);
    // }
    public function reset(ResetPasswordRequest $request)
    {
        try {
            $this->authService->resetPassword($request->only('email', 'password', 'password_confirmation', 'token'));

            return to_route('login')->with('success', 'Mật khẩu của bạn đã được đặt lại.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error_reset' => $e->getMessage()]);
        }
    }
}
