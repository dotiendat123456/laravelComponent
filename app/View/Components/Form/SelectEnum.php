<?php

namespace App\View\Components\Form;

use Illuminate\View\Component;

class SelectEnum extends Component
{
    public string $name;
    public string $label;
    public string $enum;
    public mixed $selected;
    public bool $required;

    public function __construct(string $name, string $label, string $enum, $selected = null, bool $required = false)
    {
        $this->name = $name;
        $this->label = $label;
        $this->enum = $enum;
        $this->selected = $selected;
        $this->required = $required;
    }

    public function options(): array
    {
        return ($this->enum)::cases();
    }

    public function render()
    {
        return view('components.form.select-enum');
    }
}

