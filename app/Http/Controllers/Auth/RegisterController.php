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

class RegisterController extends Controller
{

    public function getRegister()
    {
        return view('auth.register');
    }
    // protected $redirectTo = '/register';


    public function postRegister(RegisterUserRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'password'   => $request->password, // đã hash tự động nếu dùng casts
                'status'     => UserStatus::PENDING, // hoặc bỏ nếu đã default
                'role'       => UserRole::USER, // hoặc bỏ nếu đã default
            ]);

            // Gửi mail nếu cần
            SendWelcomeEmail::dispatch($user);

            DB::commit();

            return to_route('login')->with('success', 'Đăng ký tài khoản thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Register failed: ' . $e->getMessage());
            return back()->withErrors(['register_error' => 'Đăng ký thất bại: ' . $e->getMessage()]);
        }
    }
}
