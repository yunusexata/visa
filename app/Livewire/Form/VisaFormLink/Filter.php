<?php

namespace App\Livewire\GensenForm\GensenFormLink;

use App\Models\GensenForm\GensenFormLink;
use Livewire\Component;

class Filter extends Component
{
    public $status_choice = [];

    public $status;

    public function mount()
    {
        $this->status_choice = GensenFormLink::STATUS_CHOICE;
    }

    public function updated()
    {
        $this->dispatch('datatable-add-filter', [
            'status' => $this->status,
        ]);
    }

    public function render()
    {
        return view('livewire.gensen-form.gensen-form-link.filter');
    }
}
