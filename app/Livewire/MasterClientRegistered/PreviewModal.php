<?php

namespace App\Livewire\MasterClientRegistered;

use App\Helpers\Alert;
use App\Models\Client\Client;
use App\Models\Client\ClientClosingHistory;
use App\Models\Client\ClientConfirmation;
use App\Repositories\Client\ClientClosingHistoryRepository;
use App\Repositories\Client\ClientRepository;
use App\Traits\Livewire\WithDatatable;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;

class PreviewModal extends Component
{
    use WithDatatable;
    public $collection;
    #[Url(except: '')]
    public $selectedBatch;
    public $closingHistories = [];
    public $bulkPic;
    public $filter_claim_type;
    public $totals = [];

    public $type;
    public $name;
    public function mount()
    {
        if ($this->selectedBatch) {
            $this->closingHistories = ClientClosingHistoryRepository::getBy([
                ['source_file_name', $this->selectedBatch]
            ]);
        }
    }

    #[On('refresh-table')]
    public function refreshTable()
    {
        $this->resetPage();
    }

    public function closeModal() {}

    #[On('modal-filter')]
    public function modalFilter($filter)
    {
        foreach ($filter as $key => $value) {
            $this->$key = $value;
            if ($key === 'selectedBatch') {
                $this->closingHistories = ClientClosingHistoryRepository::getBy([
                    ['source_file_name', $this->selectedBatch]
                ]);
            }
        }
    }

    #[On('validate-modal')]
    public function validateModal()
    {
        if (blank($this->selectedBatch) || $this->selectedBatch === 'all') {
            Alert::fail($this, "Gagal", 'Silakan pilih batch terlebih dahulu');
            return;
        }

        $this->dispatch('open-regular-modal');

        $this->name = 'registered';
        $this->type = 'fix';
    }

    public function getData($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getColumns(): array
    {
        return [
            [
                'key' => 'name',
            ],
        ];
    }

    // public function render()
    // {
    //     return view('livewire.master-client-registered.preview-modal');
    // }

    public function getQuery(): Builder
    {
        $query = ClientClosingHistory::query();
        if ($this->type == 'fix') {
            if ($this->name == 'registered') {
                $query = ClientRepository::getClientRegistered(
                    $this->selectedBatch,
                    $this->filter_claim_type,
                    $this->bulkPic
                );
            }
            if ($this->name === 'unclosed') {
                $query = ClientRepository::getClientUnclosing(
                    $this->selectedBatch,
                    $this->filter_claim_type,
                    $this->bulkPic
                );
            }
            if ($this->name === 'unconfirmed') {
                $query = ClientRepository::getClientUnregistered(
                    $this->selectedBatch,
                    $this->filter_claim_type,
                    $this->bulkPic
                );
            }
            if ($this->name === 'kokumin') {
                $query = ClientRepository::getClientKokumin(
                    $this->selectedBatch,
                    $this->filter_claim_type,
                    $this->bulkPic
                );
            }
        } else {
            $query = ClientRepository::getClientRegistered(
                $this->selectedBatch,
                $this->filter_claim_type,
                $this->bulkPic,
                $this->name,
                true
            );
        }

        $totalsQuery = clone $query;

        // 2. FIX: Strip out conflicting clauses from the original query
        $totalsQuery->setEagerLoads([]); // Remove relationships to save memory
        $totalsQuery->reorder();         // Clears the "ORDER BY clients.is_exported"
        $totalsQuery->limit(null)->offset(null); // Clears any pagination limits

        $this->totals = [];

        if ($this->selectedBatch) {
            $result = $totalsQuery->select([

                // AI
                DB::raw("SUM(CASE WHEN clients.pic = 'ainul' THEN 1 ELSE 0 END) as total_ai"),
                // MT
                DB::raw("SUM(CASE WHEN clients.pic = 'kim' THEN 1 ELSE 0 END) as total_mt"),
                // SN
                DB::raw("SUM(CASE WHEN clients.pic = 'slamet' THEN 1 ELSE 0 END) as total_sn"),

                // KOKUMIN
                DB::raw("SUM(CASE WHEN clients.claim_type = '" . Client::CLAIM_TYPE_KOKUMIN . "' THEN 1 ELSE 0 END) as total_kokumin"),

                // PENDING
                DB::raw("SUM(CASE WHEN client_confirmations.claim_type IN ('" . Client::CLAIM_TYPE_SPEED . "', '" . Client::CLAIM_TYPE_ADJUSTMENT . "') AND NOT EXISTS (
                SELECT 1
                FROM client_esigns ce
                WHERE ce.client_id = clients.id
                AND ce.deleted_at IS NULL
            )
             THEN 1 ELSE 0 END) as total_pending"),

                // SPEED
                DB::raw("SUM(CASE WHEN client_confirmations.claim_type = '" . Client::CLAIM_TYPE_SPEED . "' 
            AND EXISTS (
                SELECT 1
                FROM client_esigns ce
                WHERE ce.client_id = clients.id
                AND ce.deleted_at IS NULL
            )
             THEN 1 ELSE 0 END) as total_speed"),

                // ADJUSTMENT
                DB::raw("SUM(CASE WHEN client_confirmations.claim_type = '" . Client::CLAIM_TYPE_ADJUSTMENT . "' 
            AND EXISTS (
                SELECT 1
                FROM client_esigns ce
                WHERE ce.client_id = clients.id
                AND ce.deleted_at IS NULL
            )
             THEN 1 ELSE 0 END) as total_adjustment"),

                // RE
                DB::raw("SUM(CASE WHEN client_confirmations.claim_type = '" . Client::CLAIM_TYPE_NORMAL . "' AND client_confirmations.current_position = '" . ClientConfirmation::CURRENT_POSITION_INDONESIA . "' THEN 1 ELSE 0 END) as total_re"),

                // JPN
                DB::raw("SUM(CASE WHEN client_confirmations.claim_type = '" . Client::CLAIM_TYPE_NORMAL . "' AND client_confirmations.current_position = '" . ClientConfirmation::CURRENT_POSITION_JEPANG . "' THEN 1 ELSE 0 END) as total_jpn"),
            ])->first();

            $this->totals = $result?->getAttributes() ?? [];
        }
        return $query ?? ClientClosingHistory::query();
    }

    public function getView(): string
    {
        return 'livewire.master-client-registered.preview-modal';
    }
}
