<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NotifyUserPostStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function handle()
    {
        if ($this->post->status == 1) {
            $statusText = 'được PHÊ DUYỆT';
        } elseif ($this->post->status == 2) {
            $statusText = ' bị TỪ CHỐI';
        } else {
            $statusText = 'được CẬP NHẬT';
        }

        Mail::raw(
            "Xin chào {$this->post->user->name}, bài viết \"{$this->post->title}\" của bạn đã {$statusText}.",
            function ($message) {
                $message->to($this->post->user->email)
                    ->subject('Thông báo trạng thái bài viết');
            }
        );
    }
}
