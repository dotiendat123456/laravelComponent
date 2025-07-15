<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\CheckAdmin;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        //sử dung toàn cục 
        $middleware->append(CheckUserStatus::class);
        // sử dụng alias để đăng ký middleware với tên tùy chỉnh 
        $middleware->alias([
            'check.user.status' => CheckUserStatus::class,
            'admin' => CheckAdmin::class,
        ]);
        // // Khai báo Middleware Group
        // $middleware->group('admin.area', [
        //     CheckUserStatus::class,
        //     CheckAdmin::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
