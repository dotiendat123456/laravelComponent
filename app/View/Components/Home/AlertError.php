<?php

namespace App\View\Components\Home;

use Illuminate\View\Component;

class AlertError extends Component
{
    public ?string $message;

    public function __construct(?string $message = null)
    {
        $this->message = $message;
    }

    public function render()
    {
        return view('components.home.alert-error');
    }
}
