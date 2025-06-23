<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Enums\UserStatus;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterUserRequest $request)
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => UserStatus::Pending->value,
        ]);

        Mail::raw('Cảm ơn bạn đã đăng ký.', function ($message) use ($user) {
            $message->to($user->email)->subject('Chào bạn!');
        });

        return redirect()->route('login')->with('success', 'Đăng ký tài khoản thành công');
    }
}
