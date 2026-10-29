<?php

namespace App\Livewire\Admin\Review;

use App\Helpers\Alert;
use App\Helpers\PermissionHelper;
use App\Models\Client\Client;
use App\Models\UpodSubmission;
use App\Models\UpodSurveyResponse;
use App\Models\User;
use App\Repositories\Account\UserRepository;
use App\Repositories\Client\ClientRepository;
use App\Traits\Livewire\WithDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class Datatable extends Component
{
    use WithDatatable;

    public $isCanUpdate;

    public $isCanDelete;

    public $isCanUpdateBookingTime;

    public $isCanUpdateDetail;

    public $status;

    public $selectedBatch = 'all';

    public $filter_claim_type;

    public $batches = [];

    // Delete Dialog
    public $targetDeleteId;

    // UPOD Control Panel fields
    public bool $upodMode = false;

    public $bulkPic;

    public $bulkUpodType;

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
        $this->batches = Client::select('source_file_name as name', DB::raw('count(*) as total'))
            ->whereNotNull('source_file_name')
            ->groupBy('source_file_name')
            ->orderByDesc(DB::raw('MAX(imported_at)'))
            ->get()
            ->toArray();
    }

    public function onMount()
    {
        $authUser = UserRepository::authenticatedUser();
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_IMPORT_DATA_JEPANG, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_IMPORT_DATA_JEPANG, PermissionHelper::TYPE_DELETE));
        $this->loadBatches();
    }

    public function changeClientOverride($clientId, $type)
    {
        $client = Client::findOrFail($clientId);
        $client->upod_type = $type === 'default' ? null : $type;
        $client->save();

        Alert::success($this, 'Berhasil', 'Override UPOD klien berhasil diperbarui.');
    }

    public function applyBulkUpodType()
    {
        if (empty($this->bulkPic) || empty($this->bulkUpodType)) {
            Alert::fail($this, 'Gagal', 'Silakan pilih PIC dan tipe UPOD terlebih dahulu.');

            return;
        }

        $pic = strtolower($this->bulkPic);
        $users = Client::where('pic', $pic)
            ->when($this->selectedBatch != 'all', function ($q) {
                $q->where('source_file_name', $this->selectedBatch);
            })
            ->when($this->bulkPic, function ($q) {
                $q->where('pic', strtolower($this->bulkPic));
            })
            ->when($this->filter_claim_type, function ($q) {
                $q->where('clients.claim_type', $this->filter_claim_type);
            })
            ->get();

        if ($users->isEmpty()) {
            Alert::fail($this, 'Gagal', 'Tidak ada user dengan PIC: '.$this->bulkPic);

            return;
        }

        foreach ($users as $user) {
            $user->upod_type = $this->bulkUpodType;
            $user->save(); // maintains track history
        }

        Alert::success($this, 'Berhasil', 'Default UPOD untuk PIC '.$this->bulkPic.' berhasil diperbarui.');
    }

    public function resetUpodSubmission($clientId)
    {
        $client = Client::findOrFail($clientId);

        // Delete submission guard
        UpodSubmission::where('client_id', $clientId)->delete();

        // Delete survey response if any
        UpodSurveyResponse::where('client_id', $clientId)->delete();

        Alert::success($this, 'Berhasil', 'Status pengisian UPOD untuk klien '.$client->name.' berhasil direset.');
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (! $this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }

        ClientRepository::delete($this->targetDeleteId);
        Alert::success($this, 'Berhasil', 'Data berhasil dihapus');
    }

    #[On('on-delete-dialog-cancel')]
    public function onDialogDeleteCancel()
    {
        $this->targetDeleteId = null;
    }

    public function showDeleteDialog($id)
    {
        $this->targetDeleteId = $id;

        Alert::confirmation(
            $this,
            Alert::ICON_QUESTION,
            'Hapus Data',
            'Apakah Anda Yakin Ingin Menghapus Data Ini ?',
            'on-delete-dialog-confirm',
            'on-delete-dialog-cancel',
            'Hapus',
            'Batal',
        );
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
        if ($this->upodMode) {
            return [
                [
                    'key' => 'no_jp',
                    'name' => 'No Input JPN',
                    'render' => function ($item) {
                        return '<span class="font-mono text-gray-900 dark:text-white">'.$item->no_jp.'</span>';
                    },
                ],
                [
                    'key' => 'name',
                    'name' => 'Nama',
                    'render' => function ($item) {
                        return '<span class="font-semibold text-gray-900 dark:text-white">'.$item->name.'</span>';
                    },
                ],
                [
                    'key' => 'pic',
                    'name' => 'PIC',
                    'render' => function ($item) {
                        return '<span class="text-gray-600 dark:text-gray-400">'.($item->pic ? ucfirst($item->pic) : '-').'</span>';
                    },
                ],
                [
                    'key' => 'upod_type',
                    'name' => 'UPOD Terpilih',
                    'searchable' => false,
                    'render' => function ($item) {
                        $resolved = $item->resolvedUpodType();
                        $style = $resolved === 'google_review' ? 'indigo' : 'purple';
                        $label = $resolved === 'google_review' ? 'Google Review' : 'Riset Kepuasan';
                        if (is_null($item->upod_type)) {
                            $label .= ' (Default)';
                        }

                        return '<span class="px-2 py-1 bg-'.$style.'-50 text-'.$style.'-700 text-[11px] font-bold rounded-md border border-'.$style.'-100 dark:bg-'.$style.'-500/15 dark:text-'.$style.'-400 dark:border-'.$style.'-500/30">'.$label.'</span>';
                    },
                ],
                [
                    'key' => 'upod_type',
                    'name' => 'Override Klien',
                    'render' => function ($item) {
                        $selectedDefault = is_null($item->upod_type) ? 'selected' : '';
                        $selectedGoogle = $item->upod_type === User::UPOD_TYPE_GOOGLE ? 'selected' : '';
                        $selectedSurvey = $item->upod_type === User::UPOD_TYPE_RISET ? 'selected' : '';

                        return '
                            <select wire:change="changeClientOverride('.$item->id.', $event.target.value)" class="text-[12px] border-slate-200 rounded py-1 pl-2 pr-6 bg-transparent focus:ring-primary focus:border-primary dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="default" '.$selectedDefault.'>Default</option>
                                <option value="'.User::UPOD_TYPE_GOOGLE.'" '.$selectedGoogle.'>Google Review</option>
                                <option value="'.User::UPOD_TYPE_RISET.'" '.$selectedSurvey.'>Riset Kepuasan</option>
                            </select>
                        ';
                    },
                ],
                [
                    'key' => 'upod_submitted',
                    'name' => 'Status',
                    'searchable' => false,
                    'class' => 'text-center',
                    'render' => function ($item) {
                        if ($item->upod_submitted) {
                            return '
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    COMPLETED
                                </span>
                            ';
                        }

                        return '
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-100 dark:bg-amber-500/15 dark:text-amber-400 dark:border-amber-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                PENDING
                            </span>
                        ';
                    },
                ],
                [
                    'key' => 'action',
                    'name' => 'Aksi',
                    'searchable' => false,
                    'class' => 'text-right',
                    'render' => function ($item) {
                        if ($item->upod_submitted) {
                            return '
                                <button wire:click="resetUpodSubmission('.$item->id.')" class="text-rose-500 hover:text-rose-700 font-bold text-label-md transition-colors flex items-center gap-1 ml-auto">
                                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                                    Reset UPOD
                                </button>
                            ';
                        }

                        return '<span class="text-slate-300 text-label-md dark:text-gray-600">None</span>';
                    },
                ],
            ];
        }

        return [
            // [
            //     'name' => 'Action',
            //     'sortable' => false,
            //     'searchable' => false,
            //     'render' => function ($item) {
            //         $editHtml = "";

            //         $id = Crypt::encrypt($item->id);
            //         if ($this->isCanUpdate) {
            //             // $editUrl = route('gensen_form_link.edit', $id);
            //             $editUrl = '';
            //             $editHtml = "<div class='col-auto'>
            //                 <a type='button' href='$editUrl' class='inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-4 py-3 text-sm bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600 disabled:bg-brand-300'>
            //                     <span class='material-symbols-outlined text-lg' data-icon='edit'>edit</span>
            //                 </a>
            //             </div>";
            //         }

            //         $destroyHtml = "";
            //         $destroyHtml = "";
            //         if ($this->isCanDelete) {
            //             $destroyHtml = "<div class='col-auto'>
            //                 <button type='button' class='inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-4 py-3 text-sm bg-red-500 text-white shadow-theme-xs hover:bg-red-600 disabled:bg-brand-300' wire:click=\"showDeleteDialog($item->id)\">
            //                     <span class='material-symbols-outlined text-lg' data-icon='delete'>delete</span>
            //                 </button>
            //             </div>";
            //         }
            //         $html = "<div class='row p-0 m-0 d-flex justify-content-start flex-nowrap'>
            //             $editHtml $destroyHtml
            //         </div>";

            //         return $html;
            //     },
            // ],
            [
                'key' => 'no_jp',
                'name' => 'No Input JPN',
                'render' => function ($item) {
                    return $item->no_jp;
                },
            ],
            [
                'key' => 'name',
                'name' => 'Nama',
            ],
            [
                'key' => 'dob',
                'name' => 'Tgl Lahir',
            ],
            [
                'key' => 'facebook',
                'name' => 'Nama FB',
            ],
            [
                'key' => 'instagram',
                'name' => 'Nama IG',
            ],
            [
                'key' => 'phone',
                'name' => 'No Telp',
            ],
            [
                'key' => 'pic',
                'name' => 'PIC',
            ],
            [
                'key' => 'lpk',
                'name' => 'LPK',
            ],
            [
                'key' => 'note',
                'name' => 'Note',
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
                            // code...
                            break;
                    }

                    return '<span class="bg-'.$style.'-50 dark:bg-'.$style.'-500/15 text-'.$style.'-600 dark:text-'.$style.'-500 text-theme-xs rounded-full px-2 py-0.5 font-medium">'.strtoupper($item->claim_type).'</span>';
                },
            ],
            [
                'searchable' => false,
                'key' => 'user_exists',
                'name' => 'Buat Akun',
                'render' => function ($item) {
                    $style = 'success';
                    $text = 'belum';
                    switch ($item->user_exists) {
                        case true:
                            $style = 'success';
                            $text = 'sudah';
                            break;
                        case false:
                            $style = 'warning';
                            $text = 'belum';
                            break;

                        default:
                            // code...
                            break;
                    }

                    return '<span class="bg-'.$style.'-50 dark:bg-'.$style.'-500/15 text-'.$style.'-700 dark:text-'.$style.'-500 text-theme-xs rounded-full px-2 py-0.5 font-medium">'.strtoupper($text).'</span>';
                },
            ],

        ];
    }

    public function updatedFilterClaimType($value)
    {
        $this->dispatch('modal-filter', [
            'filter_claim_type' => $value,
        ]);
    }

    public function getQuery(): Builder
    {
        return Client::query()
            ->select('clients.*')
            ->selectRaw('
                EXISTS (
                    SELECT 1
                    FROM users
                    WHERE users.client_id = clients.id
                ) as user_exists,
                EXISTS (
                    SELECT 1
                    FROM upod_submissions
                    WHERE upod_submissions.client_id = clients.id
                ) as upod_submitted
            ')
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
        return 'livewire.admin.review.datatable';
    }
}
