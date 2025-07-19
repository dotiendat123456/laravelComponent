<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Table extends Component
{
    public string $id;
    public array $columns;
    public bool $fixed;

    public function __construct(string $id, array $columns, bool $fixed = false)
    {
        $this->id = $id;
        $this->columns = $columns;
        $this->fixed = $fixed;
    }

    public function render()
    {
        return view('components.table');
    }
}
