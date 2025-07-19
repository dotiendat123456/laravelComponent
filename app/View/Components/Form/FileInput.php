<?php

namespace App\View\Components\Form;

use Illuminate\View\Component;

class FileInput extends Component
{
    public string $name;
    public string $label;
    public bool $required;
    public ?string $currentFile;

    public function __construct(string $name, string $label, bool $required = false, string $currentFile = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->required = $required;
        $this->currentFile = $currentFile;
    }

    public function render()
    {
        return view('components.form.file-input');
    }
}
