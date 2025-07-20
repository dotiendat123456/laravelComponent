<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class QuillEditor extends Component
{
    public string $label;
    public string $name;
    public ?string $value;
    public int $height;
    public bool $required;

    public function __construct(
        string $label,
        string $name = 'content',
        string $value = null,
        int $height = 300,
        bool $required = false
    ) {
        $this->label = $label;
        $this->name   = $name;
        $this->value  = $value;
        $this->height = $height;
        $this->required = $required;
    }

    public function render()
    {
        return view('components.quill-editor');
    }
}
