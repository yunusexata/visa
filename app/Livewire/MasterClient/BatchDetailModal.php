<?php

namespace App\Livewire\MasterClient;

use App\Models\Client\Client;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class BatchDetailModal extends Component
{
    use WithPagination;

    public $isOpen = false;

    public $batchName;

    public $search = '';

    public $claimType = '';

    public $status = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'claimType' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage('batchPage');
    }

    public function updatingClaimType()
    {
        $this->resetPage('batchPage');
    }

    public function updatingStatus()
    {
        $this->resetPage('batchPage');
    }

    #[On('openBatchModal')]
    public function openModal(string $batchName): void
    {
        $this->batchName = $batchName;
        $this->isOpen = true;
        $this->resetPage('batchPage');
        $this->reset(['search', 'claimType', 'status']);
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
    }

    public function render()
    {
        $query = Client::query();

        if ($this->batchName) {
            $query->where('source_file_name', $this->batchName);
        } else {
            $query->whereRaw('1=0');
        }

        if ($this->search) {
            $query->where('name', 'ILIKE', '%'.$this->search.'%');
        }

        if ($this->claimType) {
            $query->where('claim_type', $this->claimType);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $totalCount = $this->batchName ? Client::where('source_file_name', $this->batchName)->count() : 0;
        $clients = $query->paginate(50, ['*'], 'batchPage');

        return view('livewire.master-client.batch-detail-modal', [
            'clients' => $clients,
            'totalCount' => $totalCount,
        ]);
    }
}
