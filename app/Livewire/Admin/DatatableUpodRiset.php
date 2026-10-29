<?php

namespace App\Livewire\Admin;

use App\Helpers\Alert;
use App\Helpers\ExportHelper;
use App\Helpers\PermissionHelper;
use App\Models\Client\Client;
use App\Models\User;
use App\Repositories\Account\UserRepository;
use App\Repositories\Admin\UpodSurveyResponseRepository;
use App\Repositories\Client\ClientRepository;
use App\Traits\Livewire\WithDatatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\On;
use Livewire\Component;

class DatatableUpodRiset extends Component
{
    use WithDatatable;

    public $status;
    public $selectedBatch = 'all';
    public $batches = [];
    public $bulkPic;

    public $batch_name;
    public $filter_claim_type;

    // Delete Dialog
    public $targetDeleteId;


    public function selectBatch($batchName)
    {
        $this->selectedBatch = $batchName;
        $this->resetPage();

        $this->dispatch('modal-filter', [
            'batch_name' => $batchName,
        ]);

        if ($batchName !== 'all') {
            $this->dispatch('openBatchModal', batchName: $batchName);
        }
    }

    public function loadBatches()
    {
        $this->batches = \App\Models\Client\Client::select('source_file_name as name', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->whereNotNull('source_file_name')
            ->groupBy('source_file_name')
            ->orderByDesc(\Illuminate\Support\Facades\DB::raw('MAX(imported_at)'))
            ->get()
            ->toArray();
    }

    public function onMount()
    {
        $this->loadBatches();
        $this->bulkPic = auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN) ? null : auth()->user()->pic;
    }

    #[On('refresh-table')]
    public function refreshTable()
    {
        $this->loadBatches();
        $this->resetPage();
    }

    #[On('datatable-refresh')]
    public function datatableRefresh()
    {
        $this->loadBatches();
        $this->resetPage();
    }

    public function getColumns(): array
    {
        $columns = [
            [
                'key' => 'name',
                'name' => 'Nama Klien',
                'render' => function ($item) {
                    return $item->name;
                }
            ],
            [
                'key' => 'pic',
                'name' => 'PIC',
                'render' => function ($item) {
                    return $item->pic;
                }
            ],
            [
                'key' => 'claim_type',
                'name' => 'Jenis Pencairan',
                'render' => function ($item) {
                    $style = 'primary';
                    switch ($item->claim_type) {
                        case Client::CLAIM_TYPE_ADJUSTMENT:
                            $style = 'amber';
                            break;
                        case Client::CLAIM_TYPE_NORMAL:
                            $style = 'blue';
                            break;
                        case Client::CLAIM_TYPE_SPEED:
                            $style = 'success';
                            break;

                        default:
                            # code...
                            break;
                    }
                    return '<span class="bg-' . $style . '-50 dark:bg-' . $style . '-500/15 text-' . $style . '-600 dark:text-' . $style . '-500 text-theme-xs rounded-full px-2 py-0.5 font-medium">' . strtoupper($item->claim_type) . '</span>';
                }
            ],
            [
                'searchable' => false,
                'sortable' => false,
                'name' => '1. Seberapa puas Anda dengan kecepatan layanan kami?',
                'render' => function ($item) {
                    return $item->upodSurveyResponse->responses['q1'];
                }
            ],
            [
                'searchable' => false,
                'sortable' => false,
                'name' => '2. Seberapa puas Anda dengan keramahan dan responsifitas staff kami?',
                'render' => function ($item) {
                    return $item->upodSurveyResponse->responses['q2'];
                }
            ],
            [
                'searchable' => false,
                'sortable' => false,
                'name' => '3. Apakah informasi yang kami berikan cukup jelas dan mudah dipahami?',
                'render' => function ($item) {
                    return $item->upodSurveyResponse->responses['q3'];
                }
            ],
            [
                'searchable' => false,
                'sortable' => false,
                'name' => '4. Apakah Anda bersedia merekomendasikan layanan kami kepada rekan atau keluarga Anda?',
                'render' => function ($item) {
                    return $item->upodSurveyResponse->responses['q4'];
                }
            ],
            [
                'searchable' => false,
                'sortable' => false,
                'name' => '5. Apa alasan utama Anda bersedia merekomendasikan kami?',
                'render' => function ($item) {
                    return $item->upodSurveyResponse->responses['q5'];
                }
            ],
            [
                'searchable' => false,
                'sortable' => false,
                'name' => '6. Apakah ada saran agar layanan kami bisa lebih baik lagi?',
                'render' => function ($item) {
                    return $item->upodSurveyResponse->responses['q6'];
                }
            ],
            [
                'searchable' => false,
                'sortable' => false,
                'name' => '7. Bagaimana pengalaman Anda menggunakan Portal Online Nenkin ini?',
                'render' => function ($item) {
                    return $item->upodSurveyResponse->responses['q7'];
                }
            ],
            [
                'searchable' => false,
                'sortable' => false,
                'name' => '8. Apakah Anda merasa proses pencairan dana Nenkin kami transparan?',
                'render' => function ($item) {
                    return $item->upodSurveyResponse->responses['q8'];
                }
            ],
            [
                'searchable' => false,
                'sortable' => false,
                'name' => '9. Seberapa besar tingkat kepercayaan Anda terhadap PT Sumber Rezeki Exata Indonesia?',
                'render' => function ($item) {
                    return $item->upodSurveyResponse->responses['q9'];
                }
            ],
            [
                'searchable' => false,
                'sortable' => false,
                'name' => '10. Secara keseluruhan, seberapa puas Anda dengan seluruh rangkaian layanan kami?',
                'render' => function ($item) {
                    return $item->upodSurveyResponse->responses['q10'];
                }
            ],
        ];

        return $columns;
    }


    #[On('export')]
    public function export($type)
    {
        $fileName = "Data Upod Riset Client " . Carbon::now()->format('Y-m-d H:i:s');

        return ExportHelper::export(
            $type,
            $fileName,
            ClientRepository::getClientUpodSurvey($this->batch_name, $this->filter_claim_type, $this->bulkPic),
            'admin.reviews.export',
            [
                'title' => 'Data Ulasan',
                'type' => $type,
            ],
            [
                'size' => 'legal',
                'orientation' => 'landscape',
            ]
        );
    }

    public function getQuery(): Builder
    {
        return \App\Models\Client\Client::query()
            ->select(
                'clients.*',
            )
            ->whereHas('upodSurveyResponse')
            ->when($this->selectedBatch != 'all', function ($q) {
                $q->where('source_file_name', $this->selectedBatch);
            })
            ->when($this->bulkPic, function ($q) {
                $q->where('pic', strtolower($this->bulkPic));
            })
            ->when($this->filter_claim_type, function ($q) {
                $q->where('clients.claim_type', $this->filter_claim_type);
            });
    }

    public function getView(): string
    {
        return 'livewire.admin.datatable-upod-riset';
    }
}
