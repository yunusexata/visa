<?php

namespace App\Livewire\Dashboard;

use App\Models\Form\VisaFormLink;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Vinkla\Hashids\Facades\Hashids;

class CreateMagicLink extends Component
{
    public bool $showModal = false;

    #[Validate('required|string|max:255', as: 'Nama Link')]
    public string $name = '';

    #[Validate('nullable|string|max:255', as: 'Password')]
    public string $password = '';

    #[Validate('required|integer|min:1', as: 'Maks. Penggunaan')]
    public int $max_usage = 1;

    #[Validate('required|date', as: 'Kadaluarsa')]
    public string $expired_at = '';

    public function mount(): void
    {
        $this->resetForm();
    }

    public function openModal(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->password = auth()->user()?->form_password ?? '';
        $this->max_usage = 1;
        $this->expired_at = Carbon::now()->addDays(7)->format('Y-m-d');
    }

    public function save(): void
    {
        $this->validate();

        $link = VisaFormLink::create([
            'name' => $this->name,
            'password' => $this->password ?: null,
            'max_usage' => $this->max_usage,
            'expired_at' => Carbon::parse($this->expired_at)->endOfDay(),
            'created_by' => auth()->id(),
        ]);

        $url = url('/form/'.Hashids::encode($link->id));

        $this->showModal = false;
        $this->resetValidation();
        $this->resetForm();

        // Dispatch browser event to copy URL directly and trigger Swal alert without manual onclick
        $this->dispatch('copy-to-clipboard', text: $url, message: 'Berhasil  Copy Link ke Clipboard');
        $this->dispatch('magic-link-created', url: $url);
    }

    public function render(): View
    {
        return view('livewire.dashboard.create-magic-link');
    }
}
