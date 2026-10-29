<?php

namespace App\Livewire\Dashboard;

use App\Models\Client\Client;
use App\Models\Client\ClientConfirmation;
use Carbon\Carbon;
use Livewire\Component;

class Detail extends Component
{

    public function mount() {}

    public function render()
    {
        return view('livewire.dashboard.detail');
    }
}
