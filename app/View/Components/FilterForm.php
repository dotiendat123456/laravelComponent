<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FilterForm extends Component
{
    public string $action;
    public array $fields;
    public array $statuses;

    public function __construct(string $action, array $fields, array $statuses = [])
    {
        $this->action = $action;
        $this->fields = $fields;
        $this->statuses = $statuses;
    }

    public function render()
    {
        return view('components.filter-form');
    }
}
