<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Jobs\SendWelcomeEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\UserStatus;
use App\Enums\UserRole;
use App\Jobs\SendResetPasswordLink;



class AuthService
{
    public function register(array $data): User
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'password'   => $data['password'], // Đã hash tự động qua casts hoặc mutator
            ]);

            SendWelcomeEmail::dispatch($user);

            DB::commit();

            return $user;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Register failed: ' . $e->getMessage());
            throw $e; // Ném về controller xử lý
        }
    }


    public function login(array $data): UserRole
    {
        $credentials = [
            'email' => $data['email'],
            'password' => $data['password'],
            'status' => UserStatus::APPROVED, // Chỉ cho phép login khi đã duyệt
        ];

        if (!Auth::attempt($credentials, $data['remember'] ?? false)) {
            throw ValidationException::withMessages([
                'account_status' => 'Email hoặc mật khẩu không đúng hoặc tài khoản có thể đang chờ duyệt / từ chối / bị khóa.',
            ]);
        }

        session()->regenerate();

        // Trả về role để controller phân nhánh redirect
        return Auth::user()->isAdmin() ? UserRole::ADMIN : UserRole::USER;
    }


    public function logout(array $data): void
    {
        Auth::logout();

        $data['session']->invalidate();
        $data['session']->regenerateToken();
    }


    public function updateProfile(array $data): void
    {
        Auth::user()->update($data);
    }


    public function resetPassword(array $data): bool
    {
        $status = Password::reset(
            $data,
            function ($user, $password) {
                $user->password = $password; // Nếu đã dùng casts thì tự hash
                $user->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new \Exception('Mã thông báo đặt lại mật khẩu này không hợp lệ.');
        }

        return true;
    }


    public function sendResetLink(array $data): void
    {
        DB::beginTransaction();

        try {
            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                throw new \Exception('Email không tồn tại trong hệ thống.');
            }

            $token = Str::random(60);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            SendResetPasswordLink::dispatch($user, $token);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Reset password failed: ' . $e->getMessage());

            throw $e; // Ném ra để controller xử lý
        }
    }
}
