<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Enums\UserRole;
use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\PostObserver;


#[ObservedBy([PostObserver::class])]
class Post extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'title',
        'slug',           // PHẢI CÓ!
        'description',
        'content',
        'publish_date',
        'status', // PHẢI CÓ!
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Scope trạng thái
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Accessor thumbnail
    public function getThumbnailAttribute()
    {
        return $this->getFirstMediaUrl('thumbnails')
            ?: asset('storage/default/default-thumbnail.png');
    }

    protected $casts = [
        'publish_date' => 'datetime',
        'status' => PostStatus::class,
    ];
    /**
     * The "booted" method of the model.
     */
    // protected static function booted(): void
    // {
    //     static::observe(PostObserver::class);
    // }
}
