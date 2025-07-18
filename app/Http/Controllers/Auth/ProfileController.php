<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\Auth\AuthService;


class ProfileController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function showProfileForm()
    {
        return view('auth.profiles.edit');
    }
    // public function update(UpdateProfileRequest $request)
    // {
    //     $user = Auth::user(); // hoặc User::find(Auth::id());
    //     $user->update($request->validated()); //sử dụng validated
    //     return back()->with('success', 'Cập nhật hồ sơ thành công');
    // }
    public function update(UpdateProfileRequest $request)
    {
        $this->authService->updateProfile($request->validated());

        return back()->with('success', 'Cập nhật hồ sơ thành công');
    }
}
