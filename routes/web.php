<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\User\PostController as UserPostController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\UserController as AdminUserController;


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
    // Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    // Route::get('/posts/data', [PostController::class, 'data'])->name('posts.data');
    // Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    // Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    // Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    // Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    // Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    // Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    // Route::delete('/posts-destroy-all', [PostController::class, 'destroyAll'])->name('posts.destroyAll');
    // CRUD posts
    // Route phụ ngoài resource
    // Route::get('/posts/data', [PostController::class, 'data'])->name('posts.data');
    Route::delete('/posts/destroy-all', [UserPostController::class, 'destroyAll'])->name('posts.destroy_all')->middleware('user');

    Route::resource('posts', UserPostController::class)
        ->except(['show'])
        ->names('posts')
        ->parameters(['posts' => 'post'])
        ->middleware('user');
});


// //admin
// Route::prefix('admin')->middleware(['auth', 'admin', 'check.user.status'])->group(function () {

//     // Admin quản lý bài viết
//     Route::get('/', [AdminPostController::class, 'dashboard'])->name('admin.posts.dashboard');
//     Route::get('/posts', [AdminPostController::class, 'index'])->name('admin.posts.index');
//     Route::get('/posts/data', [AdminPostController::class, 'data'])->name('admin.posts.data');

//     Route::get('/posts/create', [AdminPostController::class, 'create'])->name('admin.posts.create');
//     Route::post('/posts', [AdminPostController::class, 'store'])->name('admin.posts.store');
//     Route::get('/posts/{post}', [AdminPostController::class, 'show'])->name('admin.posts.show');
//     Route::get('/posts/{post}/edit', [AdminPostController::class, 'edit'])->name('admin.posts.edit');
//     Route::put('/posts/{post}', [AdminPostController::class, 'update'])->name('admin.posts.update');
//     Route::delete('/posts/{post}', [AdminPostController::class, 'destroy'])->name('admin.posts.destroy');
//     Route::delete('/posts-destroy-all', [AdminPostController::class, 'destroyAll'])->name('admin.posts.destroyAll');

//     //Admin quản lý User
//     Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
//     Route::get('/users/data', [AdminUserController::class, 'data'])->name('admin.users.data');
//     Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
//     Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
//     Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggleStatus');
// });
//admin
Route::prefix('admin')->middleware(['auth', 'admin', 'check.user.status'])->group(function () {
    // Dashboard
    Route::get('/', [AdminPostController::class, 'dashboard'])->name('admin.posts.dashboard');

    // Posts phụ
    Route::get('/posts/data', [AdminPostController::class, 'data'])->name('admin.posts.data');
    Route::delete('/posts/destroy-all', [AdminPostController::class, 'destroyAll'])->name('admin.posts.destroy_all');

    // Posts CRUD
    Route::resource('posts', AdminPostController::class)
        ->except(['show'])
        ->names('admin.posts')
        ->parameters(['posts' => 'post']);


    // Users CRUD (chỉ index, edit, update)
    Route::resource('users', AdminUserController::class)
        ->only(['index', 'edit', 'update'])
        ->names('admin.users')
        ->parameters(['users' => 'user']);

    // Users phụ
    // Route::get('/users/data', [AdminUserController::class, 'data'])->name('admin.users.data');
    Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggleStatus');
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



Route::get('/news', [UserPostController::class, 'publicIndex'])->name('news.index');
Route::get('/news/{post:slug}', [UserPostController::class, 'publicShow'])->name('news.show');
