<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginUserRequest;
use App\Models\User;
use App\Enums\UserStatus;

class LoginController extends Controller
{
    public function getLogin()
    {
        return view('auth.login');
    }

    public function postLogin(LoginUserRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // Ràng buộc chỉ tài khoản đã duyệt
        $credentials['status'] = UserStatus::APPROVED;

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Phân nhánh quyền
            if (Auth::user()->isAdmin()) {
                return to_route('admin.posts.dashboard')->with('success', 'Đăng nhập thành công');
            }
            return to_route('posts.index')->with('success', 'Đăng nhập thành công');
        }
        return back()->withErrors([
            'account_status' => 'Email hoặc mật khẩu không đúng hoặc tài khoản có thể đang chờ duyệt / từ chối / bị khóa.',
        ])->onlyInput('email');
    }



    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('success', 'Đăng xuất thành công');
    }
}
