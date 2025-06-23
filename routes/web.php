<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;


/*
 Route / (Trang gốc) – Xử lý redirect theo trạng thái đăng nhập
*/

Route::get('/', function () {
    return Auth::check() ? redirect()->route('posts.index') : redirect()->route('home.index'); // file: resources/views/home.blade.php
});
Route::get('/home', function () {
    return Auth::check() ? redirect()->route('posts.index') : redirect()->route('home.index'); // file: resources/views/home.blade.php
});
/*
 Các route dành cho người chưa đăng nhập (guest)
*/
Route::middleware('guest')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name("home.index");
    // Form đăng ký
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    // Form đăng nhập
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

/*
 Các route dành cho người đã đăng nhập (auth + kiểm tra trạng thái)
*/
Route::middleware(['auth', 'check.user.status'])->group(function () {
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
});

/*

 Logout chỉ dành cho người đã đăng nhập

*/
Route::middleware('auth')->post('/logout', [LoginController::class, 'destroy'])->name('logout');
