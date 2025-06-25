<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Password;

class SendResetPasswordLink implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $token;

    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function handle()
    {

        $link = url(route('passwords.reset', [
            'token' => $this->token,
            'email' => $this->user->email,
        ], false));

        Mail::raw("Link đặt lại mật khẩu của bạn: $link", function ($message) {
            $message->to($this->user->email)
                ->subject('Đặt lại mật khẩu - Productivity App');
        });
    }
}
