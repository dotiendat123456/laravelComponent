<?php


namespace App\Enums;

enum ReactionType: int
{
    case DISLIKE = 0;
    case LIKE = 1;

    public function label(): string
    {
        return match ($this) {
            self::DISLIKE => 'Không thích',
            self::LIKE => 'Thích',
        };
    }
    public function action(): string
    {
        return match ($this) {
            self::DISLIKE => 'dislike',
            self::LIKE => 'like',
        };
    }
}
