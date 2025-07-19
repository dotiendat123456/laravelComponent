<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class QuillEditor extends Component
{
    public string $name;
    public ?string $value;
    public int $height;

    public function __construct(
        string $name = 'content',
        string $value = null,
        int $height = 300
    ) {
        $this->name   = $name;
        $this->value  = $value;
        $this->height = $height;
    }

    public function render()
    {
        return view('components.quill-editor');
    }
}
