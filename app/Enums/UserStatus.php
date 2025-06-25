<?php

namespace App\Enums;

enum UserStatus: int
{
    case PENDING = 0;
    case APPROVED = 1;
    case REJECTED = 2;
    case LOCKED = 3;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Chờ phê duyệt',
            self::APPROVED => 'Được phê duyệt',
            self::REJECTED => 'Bị từ chối',
            self::LOCKED => 'Bị khóa',
        };
    }
}
