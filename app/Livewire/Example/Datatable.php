<?php

namespace App\Livewire\GensenForm\GensenData;

use App\Enums\Gensen\GensenAttachmenStatus;
use App\Enums\Gensen\GensenAttachmentType;
use App\Helpers\Alert;
use App\Helpers\AppCrypt;
use App\Helpers\ExportHelper;
use App\Helpers\PermissionHelper;
use App\Models\User;
use App\Repositories\Account\UserRepository;
use App\Repositories\GensenForm\GensenFormRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Datatable extends Component
{
    use WithoutUrlPagination, WithPagination;

    public $gensenFormLinkId;

    protected $paginationTheme = 'bootstrap';

    public $targetDeleteId;

    public $targetCopyId;

    public $pageName = '';

    public $lengthOptions = [10, 25, 50, 100];

    public $page = 1;

    public $length = 50;

    public $perPage = 50;

    public $search;

    public $sortBy = '';

    public $sortDirection = 'asc';

    public $keyword_filter = true;

    public $show_filter = true;

    public $showLoadingIndicator = true;

    public $loadingIndicatorTarget = '';

    public $isCanUpdate;

    public $isCanCreate;

    public $isCanDelete;

    public $openRow = null;

    // public $editedRows = [];
    public ?string $editingRowId = null;

    public array $editingData = [];

    // FILTER
    public $filter_pic;

    public $filter_status;

    public $filter_tanggal_input_dari;

    public $filter_tanggal_input_sampai;

    public $filter_tanggal_cair_dari;

    public $filter_tanggal_cair_sampai;

    public $filter_label;

    // public int $length = 5;
    public function onMount()
    {
        $authUser = UserRepository::authenticatedUser();

        $this->isCanCreate = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_GENSEN_DATA, PermissionHelper::TYPE_CREATE));
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_GENSEN_DATA, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_GENSEN_DATA, PermissionHelper::TYPE_DELETE));

        $this->sortBy = 'created_at';
        $this->sortDirection = 'DESC';
    }

    #[On('export')]
    public function export($type)
    {
        $fileName = 'Data Gensen '.Carbon::now()->format('Y-m-d H:i:s');

        return ExportHelper::export(
            $type,
            $fileName,
            $this->datatableGetProcessedQuery()->get()->toArray(),
            'app.gensen-form.gensen-data.export',
            [
                'title' => 'Data Gensen',
                'type' => $type,
            ],
            [
                'size' => 'legal',
                'orientation' => 'landscape',
            ]
        );
    }

    #[On('export_ready_verified')]
    public function export_ready_verified($type)
    {
        $fileName = 'Data Gensen Ready Verified '.Carbon::now()->format('Y-m-d H:i:s');

        return ExportHelper::export(
            $type,
            $fileName,
            $this->datatableGetProcessedQuery()
                ->whereNotNull('tanggal_lengkap')
                ->whereNull('tanggal_verified')
                ->get()->toArray(),
            'app.gensen-form.gensen-data.export_ready_verified',
            [
                'title' => 'Data Gensen Ready Verified',
                'type' => $type,
            ],
            [
                'size' => 'legal',
                'orientation' => 'landscape',
            ]
        );
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (! $this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }
        $id = simple_decrypt($this->targetDeleteId);
        if (! $id) {
            abort(404, 'Link tidak valid atau telah dimanipulasi.');
        }
        GensenFormRepository::delete($id);
        Alert::success($this, 'Berhasil', 'Data berhasil dihapus');
    }

    #[On('on-delete-dialog-cancel')]
    public function onDialogDeleteCancel()
    {
        $this->targetDeleteId = null;
    }

    #[On('showDeleteDialog')]
    public function showDeleteDialog($id)
    {
        $this->targetDeleteId = $id;

        Alert::confirmation(
            $this,
            Alert::ICON_QUESTION,
            'Hapus Data',
            'Apakah Anda Yakin Ingin Duplikat Data Ini ?',
            'on-delete-dialog-confirm',
            'on-delete-dialog-cancel',
            'Hapus',
            'Batal',
        );
    }

    #[On('on-copy-dialog-confirm')]
    public function onDialogCopyConfirm()
    {
        if (! $this->isCanCreate || $this->targetCopyId == null) {
            return;
        }

        $id = simple_decrypt($this->targetCopyId);
        if (! $id) {
            abort(404, 'Link tidak valid atau telah dimanipulasi.');
        }
        GensenFormRepository::copy($id);
        Alert::success($this, 'Berhasil', 'Data berhasil diperbarui');
    }

    #[On('on-copy-dialog-cancel')]
    public function onDialogCopyCancel()
    {
        $this->targetCopyId = null;
    }

    #[On('showCopyDialog')]
    public function showCopyDialog($id)
    {
        $this->targetCopyId = $id;

        Alert::confirmation(
            $this,
            Alert::ICON_QUESTION,
            'Duplikat Data',
            'Apakah Anda Yakin Ingin Duplikat Data Ini ?',
            'on-copy-dialog-confirm',
            'on-copy-dialog-cancel',
            'Duplikat',
            'Batal',
        );
    }

    #[On('refresh-table')]
    public function refreshTable()
    {
        $this->resetPage();
    }

    public function getColumns(): array
    {
        return [
            [
                'key' => 'id',
                'name' => 'No',
                'sortable' => true,
                'searchable' => false,
                // 'class' => 'd-flex  border border-danger',
                'render' => function ($item, $index) {
                    $id = Crypt::encrypt($item->id);
                    $editHtml = "
                        <div class='col-auto m-0 p-0 d-flex d-inline flex-nowrap justify-content-center align-items-end' x-data=\"{ open: false }\"
                            wire:key='datatable_row_main_{$item['id']}'>

                            <button type='button'
                                @click=\"\$wire.editRow('".simple_encrypt($item['id'])."')\"
                                class='p-0 hover:bg-success/10 text-success rounded transition-colors'
                                data-bs-toggle='collapse'
                                data-bs-target='#collapse-".$item['id']."'
                                style='cursor: pointer;'
                                >

                                <span class='material-symbols-outlined text-lg'>edit_square</span>
                            </button>

                            <div x-show='open' x-collapse class='mt-2'></div>

                        </div>";

                    $html = "<div class='row p-0 m-0 d-flex d-inline flex-nowrap justify-content-center align-items-center'>
                        
                        $editHtml 
                        <h1>{$index}</h1>
                    </div>";

                    return $html;
                },
            ],
            [
                'key' => 'created_at',
                'name' => 'Tanggal Input',
                'render' => function ($item) {
                    return '<div class="text-nowrap">'.$item->created_at->format('Y-m-d H:i:s').'</div>';
                },
            ],
            [
                'searchable' => false,
                'sortable' => false,
                'name' => 'Progres Berkas',
                'render' => function ($item) {
                    // dd($item->isAttachmentCompleted());
                    $uploadedTypes = $item->attachments
                        ->where('status', '!=', GensenAttachmenStatus::STATUS_REJECTED)
                        ->pluck('type')
                        ->map(fn ($t) => $t->value)
                        ->toArray();
                    $statuses = [];

                    foreach (GensenAttachmentType::cases() as $index => $type) {

                        $completed = in_array($type->value, $uploadedTypes);

                        $statuses[] = [
                            'name' => $type->label(),
                            'status' => $completed,
                            'color' => 'surface-container-high',
                            'nomor_urut' => $index + 1,
                        ];
                    }
                    $html = '<div class="d-flex flex-nowrap gap-1 mb-2">';

                    foreach ($statuses as $proses) {

                        $color = $proses['status']
                            // ? '#50CD89'
                            ? 'primary'
                            : $proses['color'];

                        $html .= '
        <span class="w-6 h-6 flex items-center justify-center rounded-full bg-'.$color.' text-on-'.$color.' text-[10px] font-bold"
        data-bs-toggle="tooltip"
            title="'.$proses['name'].'">'.$proses['nomor_urut'].'</span>';
                    }

                    $html .= '</div>';

                    return $html;
                },
            ],
            [
                'key' => 'status',
                'name' => 'Status',
                'class' => 'text-center text-nowrap',
                'render' => function ($item) {
                    return "<p class='btn btn-sm py-1 mb-0 text-nowrap' style='background-color:".$item->statusColor()."'>".$item->status.'</p>';
                },
            ],
            [
                'key' => 'no_input_jepang',
                'name' => 'No Input Jepang',
                'class' => 'text-center',
            ],
            [
                'key' => 'nama_lengkap',
                'class' => 'text-nowrap',
                'name' => 'Nama Lengkap',
            ],
            [
                'key' => 'tanggal_lahir',
                'name' => 'Tgl Lahir',
                'class' => 'text-center',
            ],
            [
                'key' => 'tanggal_kepulangan',
                'name' => 'Tgl Kepulangan',
                'class' => 'text-center',
            ],

            [
                'key' => 'no_rekening_penerima',
                'class' => 'text-nowrap',
                'name' => 'Rek Penerima',
            ],
            [
                'key' => 'nama_bank_penerima',
                'name' => 'Nama Bank',
                'class' => 'text-center',
            ],
            [
                'key' => 'nama_penerima',
                'class' => 'text-nowrap',
                'name' => 'Nama Penerima',
            ],
            [
                'key' => 'hubungan_penerima',
                'name' => 'Hubungan Penerima',
                'class' => 'text-center',
            ],

            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nominal Gensen',
                'render' => function ($item) {
                    $tahun_gensen_details = explode(';', $item->tahun_gensen_details);
                    $nominal_gensen_details = explode(';', $item->nominal_gensen_details);
                    $html = '';
                    foreach ($tahun_gensen_details as $index => $tahun_gensen) {
                        $html .= "<div class='text-nowrap'>{$tahun_gensen}A / ".fromReiwaToYear($tahun_gensen)." - {$nominal_gensen_details[$index]}</div>";
                    }

                    return $html;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Tanggungan Keluarga',
                'render' => function ($item) {

                    $total_amounts = explode(';', $item->remittance_total_amounts);
                    $receiver_names = explode(';', $item->remittance_receiver_names);
                    $receiver_years = explode(';', $item->remittance_receiver_years);

                    $html = '';
                    foreach ($total_amounts as $index => $amount) {
                        $html .= "<div class='text-nowrap'> ".toReiwaYear($receiver_years[$index]).'/'.$receiver_years[$index].' - '.$amount." - {$receiver_names[$index]}</div>";
                    }

                    return $html;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Tarik Data ACC',
                'render' => function ($item) {

                    $tarik_data_details = explode(';', $item->tarik_data_details);
                    $html = '';
                    foreach ($tarik_data_details as $index => $data) {
                        $html .= "<div class='text-nowrap'>{$data}</div>";
                    }

                    return $html;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Tanggal Cair',
                'class' => 'text-center',
                'render' => function ($item) {
                    $cair_details = explode(';', $item->tanggal_cair_details);
                    $html = '';
                    foreach ($cair_details as $index => $gensen) {
                        $html .= "<div class='text-nowrap'>{$gensen}</div>";
                    }

                    return $html;
                },
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Nominal Cair',
                'render' => function ($item) {
                    $cair_details = explode(';', $item->nominal_cair_details);
                    $html = '';
                    foreach ($cair_details as $index => $gensen) {
                        $html .= "<div class='text-nowrap'>{$gensen}</div>";
                    }

                    return $html;
                },
            ],
            [
                'key' => 'keterangan',
                'class' => 'text-nowrap',
                'name' => 'Keterangan',
            ],
            [
                'key' => 'alamat_jepang',
                'class' => 'text-nowrap',
                'name' => 'Alamat Jepang',
            ],
            [
                'key' => 'kode_pos_jepang',
                'name' => 'Kode POS Jepang',
                'class' => 'text-center text-nowrap',
            ],

            [
                'key' => 'nama_lpk',
                'class' => 'text-nowrap',
                'name' => 'Nama LPK/SO/PT',
            ],

            [
                'searchable' => false,
                'key' => 'is_previously_processed',
                'name' => 'Urus Sendiri/Konsultan Lain',
                'class' => 'text-center',
                'render' => function ($item) {
                    return $item->is_previously_processed ? "<p class='btn btn-sm py-1 mb-0 text-nowrap bg-warning'>Sudah</p>" : "<p class='btn btn-sm py-1 mb-0 text-nowrap bg-success'>Belum</p>";
                },
            ],

            [
                'key' => 'pic_code',
                'name' => 'Kode PIC',
                'class' => 'text-center',
            ],
        ];
    }

    public function getQuery(): Builder
    {

        if ($this->gensenFormLinkId) {
            $id = AppCrypt::decrypt($this->gensenFormLinkId);
            if (! $id) {
                abort(404, 'Link tidak valid atau telah dimanipulasi.');
            }
        }

        return GensenFormRepository::datatable(

            $this->gensenFormLinkId ? $id : null,
            $this->filter_status,
            $this->filter_pic,
            $this->filter_tanggal_input_dari ?
                [
                    Carbon::parse($this->filter_tanggal_input_dari)->startOfDay(),
                    Carbon::parse($this->filter_tanggal_input_sampai)->endOfDay(),
                ] : null,
            $this->filter_tanggal_cair_dari ?
                [
                    Carbon::parse($this->filter_tanggal_cair_dari)->startOfDay(),
                    Carbon::parse($this->filter_tanggal_cair_sampai)->endOfDay(),
                ] : null,
            $this->filter_label
        );
    }

    public function getView(): string
    {
        return 'livewire.gensen-form.gensen-data.datatable';
    }

    // FLOW FUNCTION
    public function saveEditedRow()
    {
        try {
            DB::transaction(function () {});
            consoleLog($this, $this->editingData['status']);
            $validatedData = [];
            if ($this->isCanUpdate) {
                $validatedData = [
                    'status' => $this->editingData['status'],
                    'tanggal_lengkap' => $this->editingData['tanggal_lengkap'] ? $this->editingData['tanggal_lengkap'] : null,
                    'tanggal_verified' => $this->editingData['tanggal_verified'] ? $this->editingData['tanggal_verified'] : null,
                    'tanggal_pengajuan' => $this->editingData['tanggal_pengajuan'] ? $this->editingData['tanggal_pengajuan'] : null,
                    'tanggal_cancel' => $this->editingData['tanggal_cancel'] ? $this->editingData['tanggal_cancel'] : null,
                    'no_input_jepang' => $this->editingData['no_input_jepang'],
                    'nama_instagram' => $this->editingData['nama_instagram'],
                    'nama_tiktok' => $this->editingData['nama_tiktok'],
                    'nomor_whatsapp' => $this->editingData['nomor_whatsapp'],
                    'nomor_whatsapp_darurat' => $this->editingData['nomor_whatsapp_darurat'],
                    'email' => $this->editingData['email'],

                    'nama_lengkap' => $this->editingData['nama_lengkap'],
                    'tanggal_lahir' => $this->editingData['tanggal_lahir'],
                    'no_rekening_penerima' => $this->editingData['no_rekening_penerima'],
                    'nama_bank_penerima' => $this->editingData['nama_bank_penerima'],
                    'nama_penerima' => $this->editingData['nama_penerima'],
                    'hubungan_penerima' => $this->editingData['hubungan_penerima'],
                    'tanggal_kepulangan' => $this->editingData['tanggal_kepulangan'] ? $this->editingData['tanggal_kepulangan'] : null,
                    'keterangan' => $this->editingData['keterangan'],
                ];
            }
            if (
                auth()->user()->can(PermissionHelper::transform(
                    PermissionHelper::UPDATE_GENSEN_TANGGAL_LENGKAP,
                    PermissionHelper::TYPE_UPDATE
                ))
            ) {
                $validatedData['tanggal_lengkap'] =
                    $this->editingData['tanggal_lengkap'];
            }
            if (
                auth()->user()->can(PermissionHelper::transform(
                    PermissionHelper::UPDATE_GENSEN_TANGGAL_VERIFIED,
                    PermissionHelper::TYPE_UPDATE
                ))
            ) {
                $validatedData['tanggal_verified'] =
                    $this->editingData['tanggal_verified'];
            }
            if (
                auth()->user()->can(PermissionHelper::transform(
                    PermissionHelper::UPDATE_GENSEN_TANGGAL_PENGAJUAN,
                    PermissionHelper::TYPE_UPDATE
                ))
            ) {
                $validatedData['tanggal_pengajuan'] =
                    $this->editingData['tanggal_pengajuan'];
            }
            if (
                auth()->user()->can(PermissionHelper::transform(
                    PermissionHelper::UPDATE_GENSEN_NO_INPUT_JEPANG,
                    PermissionHelper::TYPE_UPDATE
                ))
            ) {
                $validatedData['no_input_jepang'] =
                    $this->editingData['no_input_jepang'];
            }
            if (
                auth()->user()->can(PermissionHelper::transform(
                    PermissionHelper::UPDATE_GENSEN_TANGGAL_CANCEL,
                    PermissionHelper::TYPE_UPDATE
                ))
            ) {
                $validatedData['tanggal_cancel'] =
                    $this->editingData['tanggal_cancel'];
            }
            GensenFormRepository::update($this->editingRowId, $validatedData);

            DB::commit();
            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                'Berhasil',
                'Data Berhasil Diperbarui',
                'on-dialog-confirm',
                'on-dialog-cancel',
                'Oke',
                'Tutup',
            );
        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, 'Gagal', $e->getMessage());
        }
    }

    // TRAITS
    public function mount()
    {
        $this->onMount();

        $columns = $this->getColumns();
        if ($this->sortBy == '' && count($columns) > 0) {
            foreach ($columns as $col) {
                if (! isset($col['sortable']) || $col['sortable']) {
                    $this->sortBy = $col['key'];
                    break;
                }
            }
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'sortBy', 'sortDirection', 'filter_status', 'filter_pic'])) {
            $this->resetPage();
        }
    }

    public function loadMore(): void
    {
        consoleLog($this, [
            $this->perPage,
        ]);
        $this->length += $this->perPage;
    }

    #[On('datatable-add-filter')]
    public function datatableAddFilter($filter)
    {
        foreach ($filter as $key => $value) {
            $this->$key = $value;
        }
    }

    #[On('datatable-refresh')]
    public function datatableRefresh() {}

    public function datatableSort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc'
                ? 'desc'
                : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortBy = $field;
    }

    public function datatableGetProcessedQuery()
    {
        $columns = $this->getColumns();
        $query = $this->getQuery();
        $search = $this->search;
        $sortBy = $this->sortBy;
        $sortDirection = $this->sortDirection;

        $query->when($search, function ($query) use ($search, $columns) {
            $query->where(function ($query) use ($columns, $search) {
                foreach ($columns as $col) {
                    if (
                        isset($col['key'])
                        && (! isset($col['searchable']) || (isset($col['searchable']) && $col['searchable']))
                    ) {
                        $query->orWhere($col['key'], 'ILIKE', "%$search%");
                    }
                }
            });
        });

        $query->when($sortBy, function ($query) use ($sortBy, $sortDirection) {
            $query->orderBy($sortBy, $sortDirection);
        });

        return $query;
    }

    public function datatableGetData()
    {
        return $this->datatableGetProcessedQuery()
            ->cursorPaginate($this->length);
    }

    public function editRow($id)
    {
        if ($this->editingRowId == simple_decrypt($id)) {
            $this->editingRowId = null;

            return;
        }
        $id = simple_decrypt($id);
        if (! $id) {
            abort(404, 'Link tidak valid atau telah dimanipulasi.');
        }
        $row = GensenFormRepository::findWithTahunGensen($id);
        $this->editingRowId = $id;
        $tahun_gensen_details = explode(';', $row->tahun_gensen_details);
        $nominal_gensen_details = explode(';', $row->nominal_gensen_details);
        $html = '';
        foreach ($tahun_gensen_details as $index => $tahun_gensen) {
            $html .= "<div class='text-nowrap'>{$tahun_gensen}A A  / ".fromReiwaToYear($tahun_gensen)." - {$nominal_gensen_details[$index]}</div>";
        }

        $this->editingData = [
            'id' => simple_encrypt($row['id']),
            'gensen_details' => $html,
            'status' => $row['status'],
            'nama_lengkap' => $row['nama_lengkap'],
            'tanggal_lahir' => $row['tanggal_lahir'],
            'email' => $row['email'],
            'tanggal_lengkap' => $row['tanggal_lengkap'] ? Carbon::parse($row['tanggal_lengkap'])->format('Y-m-d') : null,
            'tanggal_cancel' => $row['tanggal_cancel'] ? Carbon::parse($row['tanggal_cancel'])->format('Y-m-d') : null,
            'tanggal_verified' => $row['tanggal_verified'] ? Carbon::parse($row['tanggal_verified'])->format('Y-m-d') : null,
            'no_input_jepang' => $row['no_input_jepang'],
            'tanggal_pengajuan' => $row['tanggal_pengajuan'] ? Carbon::parse($row['tanggal_pengajuan'])->format('Y-m-d') : null,
            'tanggal_cair' => $row['tanggal_cair'] ? Carbon::parse($row['tanggal_cair'])->format('Y-m-d') : null,
            'nominal_cair' => valueToImask($row['nominal_cair']),
            'created_at' => $row['created_at'] ? Carbon::parse($row['created_at'])->format('Y-m-d') : null,
            'nominal_gensen' => valueToImask($row['nominal_gensen']),
            // 'jumlah_kirim_uang' => valueToImask($row['jumlah_kirim_uang']),
            'tanggal_kepulangan' => $row['tanggal_kepulangan'] ? Carbon::parse($row['tanggal_kepulangan'])->format('Y-m-d') : null,
            // REK PENERIMA
            'no_rekening_penerima' => $row['no_rekening_penerima'],
            'nama_bank_penerima' => $row['nama_bank_penerima'],
            'nama_penerima' => $row['nama_penerima'],
            'hubungan_penerima' => $row['hubungan_penerima'],
            'tahun_gensen' => $row['tahun_gensen'],
            'pic_code' => $row['pic_code'],
            'nama_instagram' => $row['nama_instagram'],
            'nama_tiktok' => $row['nama_tiktok'],
            'nomor_whatsapp' => $row['nomor_whatsapp'],
            'nomor_whatsapp_darurat' => $row['nomor_whatsapp_darurat'],
            'alamat_jepang' => $row['alamat_jepang'],
            'kode_pos_jepang' => $row['kode_pos_jepang'],
            'nama_lpk' => $row['nama_lpk'],
            'remarks_type' => $row['remarks_type'] == User::class ? 'Manual PIC' : 'Link Pengisian Clien',
            'keterangan' => $row['keterangan'],

        ];
    }

    public function hydrateEditedRows($data)
    {
        consoleLog($this, 'hidrate');
        foreach ($data as $row) {
            if (! isset($this->editedRows[$row['id']])) {
            }
        }
    }

    public function render()
    {
        $data = $this->datatableGetData();

        return view($this->getView(), [
            'data' => $data,
            'hasMore' => $data->hasMorePages(),
            'columns' => $this->getColumns(),
        ]);
    }
}
