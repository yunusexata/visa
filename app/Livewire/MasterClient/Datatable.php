<?php

namespace App\Livewire\MasterClient;

use App\Helpers\Alert;
use App\Helpers\NumberFormatter;
use App\Helpers\PermissionHelper;
use App\Models\Client\Client;
use App\Models\GensenForm\GensenFormLink;
use App\Repositories\Account\UserRepository;
use App\Repositories\Client\ClientRepository;
use App\Repositories\Exata\ExataFormCandidateRepository;
use App\Repositories\GensenForm\GensenFormLinkRepository;
use App\Traits\Livewire\WithDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;


class Datatable extends Component
{
    use WithDatatable;

    public $isCanUpdate;
    public $isCanDelete;
    public $isCanUpdateBookingTime;
    public $isCanUpdateDetail;

    public $isAdmin = false;
    public $status;

    #[Url(except: '')]
    public $selectedBatch;
    public $filter_claim_type;

    #[Url(except: '')]
    public $filter_data_type;
    public $batches = [];

    #[Url(except: '')]
    public $bulkPic;

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

    public function updatedbulkPic($value)
    {

        $this->dispatch('modal-filter', [
            'bulkPic' => $value,
        ]);
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
        $authUser = UserRepository::authenticatedUser();
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_IMPORT_DATA_JEPANG, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_IMPORT_DATA_JEPANG, PermissionHelper::TYPE_DELETE));
        $this->loadBatches();
        $this->isAdmin = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_FILTER_ALL_PIC, PermissionHelper::TYPE_READ));
        if (!$this->selectedBatch) {
            $this->selectedBatch = 'all';
        }
        if (!$this->bulkPic) {
            $this->bulkPic = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_FILTER_ALL_PIC, PermissionHelper::TYPE_READ)) ? null : auth()->user()->pic;
        }
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (!$this->isCanDelete || $this->targetDeleteId == null) {
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
            "Hapus Data",
            "Apakah Anda Yakin Ingin Menghapus Data Ini ?",
            "on-delete-dialog-confirm",
            "on-delete-dialog-cancel",
            "Hapus",
            "Batal",
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
        $column = [

            [
                'searchable' => false,
                'sortable' => false,
                'name' => 'No',
                'render' => function ($item, $index) {
                    $page = $this->getPage();

                    return (($page - 1) * $this->length) + $index + 1;
                }
            ],

            [
                'name' => 'Action',
                'sortable' => false,
                'searchable' => false,
                'render' => function ($item) {
                    $link = route('client.create', $item->magicLink->token ?? '-');

                    $linkHtml = "
                        <div class='w-auto'>
                            <button class='p-1 inline-flex items-center justify-center text-emerald-600 hover:bg-emerald-50 rounded transition-colors'
                                onclick=\"copyToClipboard('$link')\"
                            >
                                <span class='material-symbols-outlined text-lg' data-icon='edit'>link</span>
                            </button>
                        </div>
                    ";
                    $credential = $item->email . " " . $item->password;
                    $credentialHtml = "
                        <div class='w-auto'>
                            <button class='p-1 inline-flex items-center justify-center text-blue-600 hover:bg-blue-50 rounded transition-colors'
                                onclick=\"copyToClipboard('$credential')\"
                            >
                                <span class='material-symbols-outlined text-lg' data-icon='edit'>vpn_key</span>
                            </button>
                        </div>
                    ";

                    $html = "<div class='flex flex-nowrap justify-start p-0 m-0'>
                        $linkHtml
                        $credentialHtml
                    </div>";

                    return $html;
                },
            ],
            [
                'key' => 'no_jp',
                'name' => 'No Input JPN',
                'render' => function ($item) {
                    return $item->no_jp;
                }
            ],
            [
                'key' => 'name',
                'name' => 'Nama',
                'render' => function ($item) {
                    return "<span onclick=\"copyToClipboard('$item->name')\">$item->name</span>";
                }
            ],
            [
                'key' => 'dob',
                'name' => 'Tgl Lahir',
                'render' => function ($item) {
                    return "<span onclick=\"copyToClipboard('$item->dob')\">$item->dob</span>";
                }
            ],
            [
                'key' => 'phone',
                'name' => 'No Telp',
                'render' => function ($item) {

                    $phone = NumberFormatter::formatForWhatsApp($item->phone);
                    $text = "";
                    if (!$item->user_exists) {
                        $text = "Halo+Kak+" . strtoupper($item->name) . "+%2C%0A%0AKabar+baik%21+Nama+kamu+masuk+ke+dalam+list+nama+pencairan+Nenkin+80%25+Bulan+ini.+Segera+Cek+rekening+kamu+Ya%21+%F0%9F%8E%89%0A%0APeriode+Transfer+uang+nenkin+80%25+dari+Japan+Pension+Service+tanggal+15+s%2Fd+Akhir+bulan+ini.%0A%0ASegera+buat+akun+di+Web+Nenkin+Portal+By+Exata+Indonesia%2C+agar+bisa+dilanjut+proses+pencairan+Nenkin+20%25+nya%2C+Caranya+gampang+banget%2C+Kakak+cukup+klik+link+di+bawah+ini%2C+lalu+masukkan+Email+aktif+dan+buat+Password+baru+%28+tanggal+lahir+%29+%3A%0A%0A%F0%9F%94%97+https%3A%2F%2Fnenkin.exata-indonesia.id%2Fclient%2F46f149d7-8606-4603-98e5-2d59ba0d8ee6%2Fcreate%0A%0AJika+kesulitan+saat+registrasi+akun+nya%2C+langsung+balas+pesan+ini+saja+ya+biar+kami+bantu+pandu+%F0%9F%99%8F";
                    } else {
                        $text = "Halo+Kak+" . strtoupper($item->name) . "+%2C+terima+kasih+sudah+membuat+akun+di+Nenkin+Portal+By+Exata+Indonesia%21%0A%0ALangkah+selanjutnya%2C+mohon+login+kembali+ke+Nenkin+Portal+untuk+melengkapi+data+diri+%28pastikan+semua+data+terisi%29%3A%0A%0A%F0%9F%94%97+%5Bhttps://nenkin.exata-indonesia.id%5D%0A%0ASetelah+semua+data+sudah+di+isi+mohon+baca+info+terakhir+untuk+info+lama+proses+pencairan+nenkin+20%25+nya+.+Yuk+segera+dilengkapi+agar+prosesnya+tidak+tertunda%21";
                    }

                    return '<a href="https://api.whatsapp.com/send/?phone=' . $phone . '&text=' . $text . '&type=phone_number&app_absent=0" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        ' . $phone . '
                    </a>';
                }
            ],
            [
                'key' => 'facebook',
                'name' => 'Nama FB',
                'render' => function ($item) {
                    return '<a href="https://www.facebook.com/' . ltrim($item->facebook, '@/') . '" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
    </svg>
    ' . $item->facebook . '
</a>';
                }
            ],
            [
                'key' => 'instagram',
                'name' => 'Nama IG',
                'render' => function ($item) {
                    return '<a href="https://www.instagram.com/' . ltrim($item->instagram, '@') . '" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-gradient-to-tr from-yellow-500 via-pink-500 to-purple-600 hover:opacity-90 text-white text-sm font-medium rounded-md transition-opacity shadow-sm">
    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
    </svg>
    ' . $item->instagram . '
</a>';
                }
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
                    $text = '';
                    if (
                        in_array(
                            $item?->confirmation?->claim_type,
                            [
                                Client::CLAIM_TYPE_ADJUSTMENT,
                                Client::CLAIM_TYPE_SPEED,
                            ]
                        ) && !$item?->esign
                    ) {
                        $text = Client::CLAIM_TYPE_PENDING;
                        $style = 'red';
                    } elseif (
                        $item?->confirmation?->claim_type == Client::CLAIM_TYPE_ADJUSTMENT
                        && $item?->esign
                    ) {
                        $text = Client::CLAIM_TYPE_ADJUSTMENT;
                        $style = 'amber';
                    } elseif (
                        $item?->confirmation?->claim_type == Client::CLAIM_TYPE_SPEED
                        && $item?->esign
                    ) {
                        $text = Client::CLAIM_TYPE_SPEED;
                        $style = 'success';
                    } elseif (
                        $item?->confirmation?->claim_type == Client::CLAIM_TYPE_NORMAL
                    ) {
                        $text = Client::CLAIM_TYPE_NORMAL;
                        $style = 'blue';
                    } elseif (!$item->confirmation) {
                        $text = Client::CLAIM_TYPE_NORMAL;
                        $style = 'blue';
                    }
                    return '<span class="bg-' . $style . '-50 dark:bg-' . $style . '-500/15 text-' . $style . '-600 dark:text-' . $style . '-500 text-theme-xs rounded-full px-2 py-0.5 font-medium">' . strtoupper($text) . '</span>';
                }
            ],
            [
                'searchable' => false,
                'key' => 'confirmation_exists',
                'name' => 'Konfimasi',
                'render' => function ($item) {
                    $style = 'success';
                    $text = 'belum';
                    switch ($item->confirmation_exists) {
                        case true:
                            $style = 'success';
                            $text = 'sudah';
                            break;
                        case false:
                            $style = 'warning';
                            $text = 'belum';
                            break;

                        default:
                            # code...
                            break;
                    }
                    return '<span class="bg-' . $style . '-50 dark:bg-' . $style . '-500/15 text-' . $style . '-700 dark:text-' . $style . '-500 text-theme-xs rounded-full px-2 py-0.5 font-medium">' . strtoupper($text) . '</span>';
                }
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
                            # code...
                            break;
                    }
                    return '<span class="bg-' . $style . '-50 dark:bg-' . $style . '-500/15 text-' . $style . '-700 dark:text-' . $style . '-500 text-theme-xs rounded-full px-2 py-0.5 font-medium">' . strtoupper($text) . '</span>';
                }
            ],
            [
                'searchable' => false,
                'key' => 'esign_exists',
                'name' => 'TTD',
                'render' => function ($item) {
                    $style = 'success';
                    $text = 'belum';
                    switch ($item->esign_exists) {
                        case true:
                            $style = 'success';
                            $text = 'sudah';
                            break;
                        case false:
                            $style = 'warning';
                            $text = 'belum';
                            break;

                        default:
                            # code...
                            break;
                    }
                    return '<span class="bg-' . $style . '-50 dark:bg-' . $style . '-500/15 text-' . $style . '-700 dark:text-' . $style . '-500 text-theme-xs rounded-full px-2 py-0.5 font-medium">' . strtoupper($text) . '</span>';
                }
            ],

        ];

        return $column;
    }


    public function updatedFilterClaimType($value)
    {
        $this->dispatch('modal-filter', [
            'filter_claim_type' => $value,
        ]);
    }
    public function updatedFilterDataType($value)
    {
        $this->dispatch('modal-filter', [
            'filter_data_type' => $value,
        ]);
    }

    public function getQuery(): Builder
    {
        return \App\Models\Client\Client::query()
            ->select('clients.*', 'client_magic_links.email', 'client_magic_links.password')
            ->selectRaw("
                EXISTS (
                    SELECT 1
                    FROM client_confirmations
                    WHERE client_confirmations.client_id = clients.id
                        AND client_confirmations.deleted_at IS NULL
                ) as confirmation_exists,
                EXISTS (
                    SELECT 1
                    FROM client_esigns
                    WHERE client_esigns.client_id = clients.id
                        AND client_esigns.deleted_at IS NULL
                ) as esign_exists,
                EXISTS (
                    SELECT 1
                    FROM users
                    WHERE users.client_id = clients.id
                        AND users.deleted_at IS NULL
                ) as user_exists
            ")
            ->join('client_magic_links', function ($j) {
                $j->on('client_magic_links.client_id', '=', 'clients.id')
                    ->whereNull('client_magic_links.deleted_at');
            })
            ->when($this->selectedBatch != 'all', function ($q) {
                $q->where('clients.source_file_name', $this->selectedBatch);
            })
            ->when($this->bulkPic && $this->isAdmin, function ($q) {
                $q->where('clients.pic', 'ILIKE', '%' . $this->bulkPic . '%');
            })
            ->when(!$this->isAdmin, function ($q) {
                $q->where('clients.pic', 'ILIKE', '%' . $this->bulkPic . '%');
            })
            ->whereDoesntHave('esign')
            ->when($this->filter_data_type && $this->filter_data_type === Client::FILTER_DATA_TYPE_CONFIRMATION, function ($q) {
                $q->whereHas('confirmation');
            })
            ->when($this->filter_data_type && $this->filter_data_type === Client::FILTER_DATA_TYPE_NOT_CONFIRMATION, function ($q) {
                $q->whereDoesntHave('confirmation')
                    ->whereHas('user');
            })
            ->when($this->filter_data_type && $this->filter_data_type === Client::FILTER_DATA_TYPE_REGISTERED, function ($q) {
                $q->whereHas('user');
            })
            ->when($this->filter_data_type && $this->filter_data_type === Client::FILTER_DATA_TYPE_NOT_REGISTERED, function ($q) {
                $q->whereDoesntHave('user');
            })
            ->where('clients.claim_type', '!=', Client::CLAIM_TYPE_KOKUMIN)
            ->whereDoesntHave('confirmation');
    }

    public function getView(): string
    {
        return 'livewire.master-client.datatable';
    }
}
