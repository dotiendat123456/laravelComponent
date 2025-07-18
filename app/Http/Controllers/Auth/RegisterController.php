<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserStatus;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendWelcomeEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Auth\AuthService;


class RegisterController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function getRegister()
    {
        return view('auth.register');
    }
    // protected $redirectTo = '/register';


    public function postRegister(RegisterUserRequest $request)
    {
        try {
            $this->authService->register($request->validated());

            return to_route('login')->with('success', 'Đăng ký tài khoản thành công');
        } catch (\Throwable $e) {
            return back()->withErrors(['register_error' => 'Đăng ký thất bại: ' . $e->getMessage()]);
        }
    }
}
