<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    // protected $queue = 'register_account';
    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->onQueue('register_account');
    }

    public function handle(): void
    {
        Mail::raw("Cảm ơn bạn đã đăng ký.", function ($message) {
            $message->to($this->user->email)
                ->subject('Chào bạn!');
        });
    }
}
