<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AlertSuccess extends Component
{
    public string|null $message;

    public function __construct(string $message = null)
    {
        $this->message = $message;
    }

    public function render(): View|Closure|string
    {
        return view('components.alert-success');
    }
}
