<?php

namespace App\View\Components\Form;

use Illuminate\View\Component;

class Input extends Component
{
    public string $label;
    public string $name;
    public string $type;
    public ?string $value;
    public bool $required;

    public function __construct(
        string $name,
        string $label,
        string $type = 'text',
        string $value = null,
        bool $required = false
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->type = $type;
        $this->value = $value;
        $this->required = $required;
    }

    public function render()
    {
        return view('components.form.input');
    }
}
