<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    // Hiển thị form đăng nhập
    public function create()
    {
        return view('auth.login');
    }

    // Xử lý đăng nhập
    public function store(LoginUserRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Middleware sẽ kiểm tra UserStatus sau khi đăng nhập
            return redirect()->route('posts.index')->with('success', 'Đăng nhập thành công');
        }

        return back()->withErrors([
            'login_error' => 'Email hoặc mật khẩu không đúng.',
        ])->withInput();
    }

    // Xử lý đăng xuất
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
