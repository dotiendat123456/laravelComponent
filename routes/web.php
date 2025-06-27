<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\PostController;


// Trang mặc định
// Route::get('/', fn() => view('welcome'));
// Route::get('/home', [HomeController::class, 'index'])->name('home');


// Sau khi đăng nhập (auth + kiểm tra trạng thái)
Route::middleware(['auth', 'check.user.status'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    // Logout vẫn cho phép truy cập
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    //Profile
    Route::get('/profile', [ProfileController::class, 'showProfileForm'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // CRUD Posts - TRUYỀN THỐNG, tránh lỗi name
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::delete('/posts-destroy-all', [PostController::class, 'destroyAll'])->name('posts.destroyAll');
});


// Chỉ cho khách truy cập được login/register
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'getRegister'])->name('register');
    Route::post('/register', [RegisterController::class, 'postRegister']);

    Route::get('/', [LoginController::class, 'getLogin']);
    Route::get('/login', [LoginController::class, 'getLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'postLogin']);

    Route::prefix('passwords')->group(function () {
        // Gửi form nhập email
        Route::get('reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('passwords.request');
        // Submit email để gửi link đặt lại mật khẩu
        Route::post('email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('passwords.email');

        // Giao diện nhập mật khẩu mới (sau khi click link trong email)
        Route::get('reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('passwords.reset');
        // Submit mật khẩu mới
        Route::post('reset', [ResetPasswordController::class, 'reset'])->name('passwords.update');
    });
});
