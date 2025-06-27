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

class RegisterController extends Controller
{

    public function getRegister()
    {
        return view('auth.register');
    }
    // protected $redirectTo = '/register';


    public function postRegister(RegisterUserRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                // Tạo user
                $user = User::create([
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                    'email'      => $request->email,
                    'password'   => $request->password, // đã được hash auto nếu dùng 'hashed' cast
                    'status'     => UserStatus::PENDING, //khỏi truyền cũng được vì nó có default bên kia rồi 
                    'role'       => UserRole::USER, //khỏi truyền cũng được vì nó có default bên kia rồi 
                ]);

                SendWelcomeEmail::dispatch($user);
            });

            return to_route('login')->with('success', 'Đăng ký tài khoản thành công');
        } catch (\Exception $e) {
            return back()->withErrors(['register_error' => 'Đăng ký thất bại: ' . $e->getMessage()]);
        }
    }
}
