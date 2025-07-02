<?php


namespace App\Enums;

enum PostStatus: int
{
    case PENDING = 0;
    case APPROVED = 1;
    case DENY = 2;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Chờ duyệt',
            self::APPROVED => 'Đã duyệt',
            self::DENY => 'Từ chối',
        };
    }
}
