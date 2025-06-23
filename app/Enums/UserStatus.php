<?php

namespace App\Enums;

enum UserStatus: int
{
    case Pending = 0;
    case Approved = 1;
    case Rejected = 2;
    case Locked = 3;

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ phê duyệt',
            self::Approved => 'Được phê duyệt',
            self::Rejected => 'Bị từ chối',
            self::Locked => 'Bị khóa',
        };
    }
}
