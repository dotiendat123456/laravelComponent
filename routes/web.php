<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PostController;

// Trang mặc định
Route::get('/', fn() => view('welcome'));
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Chỉ cho khách truy cập được login/register
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});


// Sau khi đăng nhập (auth + kiểm tra trạng thái)
Route::middleware(['auth', 'check.user.status'])->group(function () {
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    // Logout vẫn cho phép truy cập
    Route::get('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
