<?php

namespace App\Enums;

enum UserRole: string
{
    case USER   = 'user';
    case ADMIN  = 'admin';
    case EDITOR = 'editor';
    case GUEST  = 'guest';

    public function label(): string
    {
        return match ($this) {
            self::USER   => 'Người dùng',
            self::ADMIN => 'Quản trị viên',
            self::EDITOR => 'Biên tập viên',
            self::GUEST  => 'Khách truy cập',
        };
    }
}
