<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function showProfileForm()
    {
        return view('auth.profiles.edit');
    }
    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user(); // hoặc User::find(Auth::id());
        $user->update($request->only(['first_name', 'last_name', 'address']));

        return back()->with('success', 'Cập nhật hồ sơ thành công');
    }
}
