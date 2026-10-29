<?php

namespace App\Livewire\MasterClientRegistered;

use App\Helpers\Alert;
use App\Helpers\ExportHelper;
use App\Helpers\NumberFormatter;
use App\Helpers\PermissionHelper;
use App\Models\Client\Client;
use App\Models\Client\ClientClosingHistory;
use App\Models\Client\ClientConfirmation;
use App\Models\User;
use App\Repositories\Account\UserRepository;
use App\Repositories\Client\ClientClosingHistoryRepository;
use App\Repositories\Client\ClientConfirmationRepository;
use App\Repositories\Client\ClientEsignRepository;
use App\Repositories\Client\ClientRepository;
use App\Traits\Livewire\WithDatatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
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

    public $isCanGetAll = false;

    public $status;

    #[Url(except: '')]
    public $selectedBatch;

    public $batches = [];

    #[Url(except: '')]
    public $bulkPic;

    public $batch_name;

    #[Url(except: '')]
    public $filter_claim_type;

    public $filter_export_status;

    // Delete Dialog
    public $targetDeleteId;

    public function selectBatch($batchName)
    {
        $this->selectedBatch = $batchName;

        $this->dispatch('modal-filter', [
            'selectedBatch' => $batchName,
        ]);
        $this->resetPage();
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
        $this->loadBatches();

        $authUser = UserRepository::authenticatedUser();
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_MASTER_CLIENT_REGISTERED, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_MASTER_CLIENT_REGISTERED, PermissionHelper::TYPE_DELETE));
        $this->isCanGetAll = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_FILTER_ALL_PIC, PermissionHelper::TYPE_READ));
        if (! $this->selectedBatch) {
            $this->selectedBatch = 'all';
        }
        if (! $this->bulkPic) {
            $this->bulkPic = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_FILTER_ALL_PIC, PermissionHelper::TYPE_READ)) ? null : auth()->user()->pic;
        }
    }

    #[On('on-delete-confirmation-dialog-confirm')]
    public function onDialogDeleteConfirmationConfirm()
    {
        if (! $this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }

        ClientConfirmationRepository::deleteBy([
            ['client_id', $this->targetDeleteId],
        ]);
        ClientEsignRepository::deleteBy([
            ['client_id', $this->targetDeleteId],
        ]);
        Alert::success($this, 'Berhasil', 'Data berhasil dihapus');
    }

    #[On('on-delete-confirmation-dialog-cancel')]
    public function onDialogDeleteConfirmationCancel()
    {
        $this->targetDeleteId = null;
    }

    public function showDeleteConfirmationDialog($id)
    {
        $this->targetDeleteId = $id;

        Alert::confirmation(
            $this,
            Alert::ICON_QUESTION,
            'Hapus Data',
            'Apakah Anda Yakin Ingin Menghapus Data Ini ?',
            'on-delete-confirmation-dialog-confirm',
            'on-delete-confirmation-dialog-cancel',
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
                'searchable' => false,
                'sortable' => false,
                'name' => 'No',
                'render' => function ($item, $index) {
                    $page = $this->getPage();

                    return (($page - 1) * $this->length) + $index + 1;
                },
            ],

            [
                'name' => 'Action',
                'sortable' => false,
                'searchable' => false,
                'render' => function ($item) {
                    $destroyHtml = '';
                    if ($this->isCanDelete) {
                        $destroyHtml = "<div class='col-auto'>
                            <button type='button' class='inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-2 py-1 text-sm bg-red-500 text-white shadow-theme-xs hover:bg-red-600 disabled:bg-brand-300' wire:click=\"showDeleteConfirmationDialog($item->id)\">
                                <span class='material-symbols-outlined text-lg' data-icon='delete'>delete</span>
                            </button>
                        </div>";
                    }

                    $esignHtml = '';
                    if ($item->esign_exists) {
                        $esignHtml = "<div class='col-auto'>
                                <button type='button' class='inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-2 py-1 text-sm bg-success-500 text-white shadow-theme-xs hover:bg-red-600 disabled:bg-brand-300' wire:click=\"dispatch('showEsign', '".$item?->esign?->signature_data."')\">
                                    <span class='material-symbols-outlined text-lg' data-icon='signature'>signature</span>
                                </button>
                            </div>";
                    }
                    $credential = $item->email.' '.$item->password;
                    $credentialHtml = "<div class='col-auto'>
                                <button type='button' class='inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-2 py-1 text-sm bg-success-500 text-white shadow-theme-xs hover:bg-success-600 disabled:bg-brand-300' onclick=\"copyToClipboard('$credential')\">
                                    <span class='material-symbols-outlined text-lg' data-icon='edit'>vpn_key</span>
                                </button>
                            </div>
                    ";

                    $clientHtml = "<div class='col-auto'>
                                <a href='".route('pov_client.index', Crypt::encrypt($item->id))."' target='_BLANK' class='inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-2 py-1 text-sm bg-blue-600 text-white shadow-theme-xs hover:bg-blue-800 disabled:bg-brand-300'>
                                    <span class='material-symbols-outlined text-lg' data-icon='identity_platform'>identity_platform</span>
                                </a>
                            </div>";

                    $html = "<div class='flex flex-nowrap justify-start p-0 m-0 gap-1'>
                        $destroyHtml $esignHtml $clientHtml $credentialHtml
                    </div>";

                    return $html;
                },
            ],
            [
                'key' => 'clients.no_jp',
                'name' => 'No Input JPN',
                'render' => function ($item) {
                    return $item->no_jp;
                },
            ],
            [
                'key' => 'clients.name',
                'name' => 'Nama',
                'render' => fn ($item) => $item->name,
            ],
            [
                'key' => 'clients.dob',
                'name' => 'Tgl Lahir',
                'render' => fn ($item) => $item->dob,
            ],
            [
                'key' => 'clients.phone',
                'name' => 'No Telp',
                'render' => function ($item) {
                    $phone = NumberFormatter::formatForWhatsApp($item->phone);
                    $text = '';
                    if (
                        in_array(
                            $item?->confirmation?->claim_type,
                            [
                                Client::CLAIM_TYPE_ADJUSTMENT,
                                Client::CLAIM_TYPE_SPEED,
                            ]
                        ) && ! $item?->esign
                    ) {
                        $text = 'Halo+Kak+'.strtoupper($item->name).'+%2C+pilihan+pencairan+jalur+Speed+Service+untuk+Nenkin+20%25+Kakak+sudah+kami+terima%21+%E2%9A%A1%0A%0AAgar+sistem+kami+bisa+langsung+memprosesnya%2C+Kakak+perlu+menandatangani+Surat+Perjanjian+Speed+Service+secara+digital.+Silakan+login+ke+portal+%0A%0A%5Bhttps://nenkin.exata-indonesia.id%5D%0A%0A+klik+tombol+tanda+tangan+ya.%0A%0AProsesnya+cuma+1+menit+kok%2C+Kak.+Ditunggu+ya%21';
                    }

                    if (! $item->user_exists) {
                        $text = 'Halo+Kak+'.strtoupper($item->name).'+%2C+kabar+baik%21+Nenkin+80%25+Kakak+sudah+berhasil+cair+ya.+%F0%9F%8E%89%0A%0AUntuk+mulai+memproses+sisa+Nenkin+20%25-nya%2C+yuk+segera+buat+akun+di+web+Nenkin+Portal+By+Exata+Indonesia.+Caranya+gampang+banget%2C+Kakak+cukup+klik+link+di+bawah+ini%2C+lalu+masukkan+Email+aktif+dan+buat+Password+baru+%28+masukkan+tanggal+lahir+%29%3A%0A%0A%F0%9F%94%97+%5Bhttps://nenkin.exata-indonesia.id%5D%0A%0AKalau+kesulitan+saat+registrasi%2C+langsung+balas+pesan+ini+saja+ya+biar+kami+bantu+pandu.';
                    }

                    return '<a href="https://api.whatsapp.com/send/?phone='.$phone.'&text='.$text.'&type=phone_number&app_absent=0" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        '.$phone.'
                    </a>';
                },
            ],
            [
                'key' => 'current_phone',
                'name' => 'WA Update',
                'render' => function ($item) {
                    $phone = NumberFormatter::formatForWhatsApp($item->current_phone);
                    $text = '';
                    if (
                        in_array(
                            $item?->confirmation?->claim_type,
                            [
                                Client::CLAIM_TYPE_ADJUSTMENT,
                                Client::CLAIM_TYPE_SPEED,
                            ]
                        ) && ! $item?->esign
                    ) {
                        $text = 'Halo+Kak+'.strtoupper($item->name).'+%2C+pilihan+pencairan+jalur+Speed+Service+untuk+Nenkin+20%25+Kakak+sudah+kami+terima%21+%E2%9A%A1%0A%0AAgar+sistem+kami+bisa+langsung+memprosesnya%2C+Kakak+perlu+menandatangani+Surat+Perjanjian+Speed+Service+secara+digital.+Silakan+login+ke+portal+%0A%0A%5Bhttps://nenkin.exata-indonesia.id%5D%0A%0A+klik+tombol+tanda+tangan+ya.%0A%0AProsesnya+cuma+1+menit+kok%2C+Kak.+Ditunggu+ya%21';
                    }

                    if (! $item->user_exists) {
                        $text = 'Halo+Kak+'.strtoupper($item->name).'+%2C+kabar+baik%21+Nenkin+80%25+Kakak+sudah+berhasil+cair+ya.+%F0%9F%8E%89%0A%0AUntuk+mulai+memproses+sisa+Nenkin+20%25-nya%2C+yuk+segera+buat+akun+di+web+Nenkin+Portal+By+Exata+Indonesia.+Caranya+gampang+banget%2C+Kakak+cukup+klik+link+di+bawah+ini%2C+lalu+masukkan+Email+aktif+dan+buat+Password+baru+%28+masukkan+tanggal+lahir+%29%3A%0A%0A%F0%9F%94%97+%5Bhttps://nenkin.exata-indonesia.id%5D%0A%0AKalau+kesulitan+saat+registrasi%2C+langsung+balas+pesan+ini+saja+ya+biar+kami+bantu+pandu.';
                    }

                    return '<a href="https://api.whatsapp.com/send/?phone='.$phone.'&text='.$text.'&type=phone_number&app_absent=0" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        '.$phone.'
                    </a>';
                },
            ],
            [
                'key' => 'clients.facebook',
                'name' => 'Nama FB',
                'render' => fn ($item) => '<a href="https://www.facebook.com/'.ltrim($item->facebook, '@/').'" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
    </svg>
    '.$item->facebook.'
</a>',
            ],
            [
                'key' => 'clients.instagram',
                'name' => 'Nama IG',
                'render' => fn ($item) => '<a href="https://www.instagram.com/'.ltrim($item->instagram, '@').'" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-gradient-to-tr from-yellow-500 via-pink-500 to-purple-600 hover:opacity-90 text-white text-sm font-medium rounded-md transition-opacity shadow-sm">
    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
    </svg>
    '.$item->instagram.'
</a>',
            ],
            [
                'key' => 'clients.pic',
                'name' => 'PIC',
                'render' => fn ($item) => $item->pic,
            ],
            [
                'key' => 'clients.lpk',
                'name' => 'LPK',
                'render' => fn ($item) => $item->lpk,
            ],
            [
                'key' => 'clients.note',
                'name' => 'Note',
                'render' => fn ($item) => $item->note,
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
            [
                'searchable' => false,
                'key' => 'confirmation_exists',
                'name' => 'Form Konfirmasi',
                'render' => function ($item) {
                    $style = 'primary';
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
                            // code...
                            break;
                    }

                    return '<span class="bg-'.$style.'-50 dark:bg-'.$style.'-500/15 text-'.$style.'-700 dark:text-'.$style.'-500 text-theme-xs rounded-full px-2 py-0.5 font-medium">'.strtoupper($text).'</span>';
                },
            ],
            [
                'searchable' => false,
                'key' => 'esign_exists',
                'name' => 'Tanda Tangan',
                'render' => function ($item) {
                    $style = 'primary';
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
                            // code...
                            break;
                    }

                    return '<span class="bg-'.$style.'-50 dark:bg-'.$style.'-500/15 text-'.$style.'-700 dark:text-'.$style.'-500 text-theme-xs rounded-full px-2 py-0.5 font-medium">'.strtoupper($text).'</span>';
                },
            ],
            [
                'searchable' => false,
                'sortable' => false,
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
                        ) && ! $item?->esign
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
                    } elseif (! $item->confirmation) {
                        $text = Client::CLAIM_TYPE_NORMAL;
                        $style = 'blue';
                    }

                    return '<span class="bg-'.$style.'-50 dark:bg-'.$style.'-500/15 text-'.$style.'-600 dark:text-'.$style.'-500 text-theme-xs rounded-full px-2 py-0.5 font-medium">'.strtoupper($text).'</span>';
                },
            ],
            [
                'key' => 'is_exported',
                'name' => 'Status Ekspor',
                'sortable' => false,
                'searchable' => false,
                'render' => function ($item) {
                    if ($item->is_exported) {
                        return '<span class="bg-success-50 dark:bg-success-500/15 text-success-600 dark:text-success-500 text-theme-xs rounded-full px-2 py-0.5 font-medium">EXPORTED</span>';
                    }

                    return '<span class="bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-theme-xs rounded-full px-2 py-0.5 font-medium">UN-EXPORTED</span>';
                },
            ],
            [
                'searchable' => false,
                'key' => 'nenkin_80_received_type',
                'name' => 'Status Penerimaan Nenkin 80%',
                'render' => function ($item) {
                    return $item->nenkin_80_received_type ? ClientConfirmation::NENKIN_80_RECEIVED_TYPE_CHOICE[$item->nenkin_80_received_type] : '-';
                },
            ],
            [
                'searchable' => false,
                'key' => 'current_position',
                'name' => 'Posisi Saat Ini',
                'render' => function ($item) {
                    return $item->current_position ?? '-';
                },
            ],
            [
                'searchable' => false,
                'key' => 'back_to_jp_type',
                'name' => 'Rencana Kembali Ke Jepang',
                'render' => function ($item) {
                    return $item->back_to_jp_type ? ClientConfirmation::BACK_TO_JP_TYPE_CHOICE[$item->back_to_jp_type] : '-';
                },
            ],
            [
                'searchable' => false,
                'key' => 'departure_month',
                'name' => 'Bulan Kembali Ke Jepang',
                'render' => function ($item) {
                    if (blank($item->departure_month)) {
                        return '-';
                    }

                    return Carbon::create()
                        ->month((int) $item->departure_month)
                        ->locale('id')
                        ->translatedFormat('F');
                },
            ],
            [
                'searchable' => false,
                'key' => 'departure_year',
                'name' => 'Tahun Kembali Ke Jepang',
                'render' => fn ($item) => $item->departure_year ?? '-',
            ],
            [
                'searchable' => false,
                'key' => 'estimated_return_month',
                'name' => 'Bulan Kepulangan',
                'render' => function ($item) {
                    if (blank($item->estimated_return_month)) {
                        return '-';
                    }

                    return Carbon::create()
                        ->month((int) $item->estimated_return_month)
                        ->locale('id')
                        ->translatedFormat('F');
                },
            ],
            [
                'searchable' => false,
                'key' => 'estimated_return_year',
                'name' => 'Tahun Kepulangan',
                'render' => fn ($item) => $item->estimated_return_year ?? '',
            ],
            [
                'searchable' => false,
                'key' => 'nik',
                'name' => 'NIK',
                'render' => fn ($item) => $item->nik ?? '',
            ],
            [
                'searchable' => false,
                'key' => 'indonesia_address',
                'name' => 'Alamat Indonesia',
                'render' => fn ($item) => $item->indonesia_address ?? '',
            ],
            [
                'key' => 'client_confirmations.client_closing_batch_number',
                'name' => 'Nomor Closing',
                'render' => fn ($item) => $item->client_closing_batch_number ?? 'Belum Masuk Closing',
            ],
            [
                'key' => 'client_confirmations.closed_at',
                'name' => 'Waktu Closing',
                'render' => fn ($item) => $item->closed_at ?? 'Belum Masuk Closing',
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return Client::query()
            ->where('clients.claim_type', '!=', Client::CLAIM_TYPE_KOKUMIN)
            ->select(
                'clients.*',
                'client_confirmations.nenkin_80_received_type',
                'client_confirmations.current_phone',
                'client_confirmations.current_position',
                'client_confirmations.back_to_jp_type',
                'client_confirmations.departure_type',
                'client_confirmations.departure_month',
                'client_confirmations.departure_year',
                'client_confirmations.estimated_return_month',
                'client_confirmations.estimated_return_year',
                'client_confirmations.nik',
                'client_confirmations.indonesia_address',
                'client_confirmations.client_closing_batch_number',
                'client_confirmations.closed_at',
                'client_magic_links.email',
                'client_magic_links.password',
            )
            ->selectRaw('
                EXISTS (
                    SELECT 1
                    FROM client_esigns
                    WHERE client_esigns.client_id = clients.id
                    AND client_esigns.deleted_at IS NULL
                    
                ) as esign_exists,
                EXISTS (
                    SELECT 1
                    FROM client_confirmations
                    WHERE client_confirmations.client_id = clients.id
                    AND client_confirmations.deleted_at IS NULL
                ) as confirmation_exists,
                EXISTS (
                    SELECT 1
                    FROM users
                    WHERE users.client_id = clients.id
                        AND users.deleted_at IS NULL
                ) as user_exists
            ')
            ->join('client_confirmations', function ($j) {
                $j->on('client_confirmations.client_id', '=', 'clients.id')
                    ->whereNull('client_confirmations.deleted_at');
            })
            ->join('client_magic_links', function ($j) {
                $j->on('client_magic_links.client_id', '=', 'clients.id')
                    ->whereNull('client_magic_links.deleted_at');
            })
            ->whereHas('confirmation')
            ->when($this->selectedBatch != 'all', function ($q) {
                $q->where('clients.source_file_name', $this->selectedBatch);
            })

            ->when($this->bulkPic && $this->isCanGetAll, function ($q) {
                $q->where('clients.pic', 'ILIKE', '%'.$this->bulkPic.'%');
            })
            ->when(! $this->isCanGetAll, function ($q) {
                $q->where('clients.pic', 'ILIKE', '%'.$this->bulkPic.'%');
            })
            ->when($this->filter_export_status === 'exported', function ($q) {
                $q->where('clients.is_exported', true);
            })
            ->when($this->filter_export_status === 'unexported', function ($q) {
                $q->where('clients.is_exported', false);
            })
            ->when($this->filter_claim_type && $this->filter_claim_type === Client::CLAIM_TYPE_NORMAL, function ($q) {
                $q->where('client_confirmations.claim_type', Client::CLAIM_TYPE_NORMAL);
            })
            ->when($this->filter_claim_type &&
                in_array(
                    $this->filter_claim_type,
                    [Client::CLAIM_TYPE_SPEED, Client::CLAIM_TYPE_ADJUSTMENT]
                ), function ($q) {
                    $q->where('client_confirmations.claim_type', $this->filter_claim_type)
                        ->whereHas('esign');
                })
            ->when($this->filter_claim_type && $this->filter_claim_type === Client::STATUS_PENDING, function ($q) {
                $q->whereIn('client_confirmations.claim_type', [Client::CLAIM_TYPE_ADJUSTMENT, Client::CLAIM_TYPE_SPEED])
                    ->whereDoesntHave('esign');
            });
    }

    #[On('modal-filter')]
    public function modalFilter($filter)
    {
        foreach ($filter as $key => $value) {
            $this->$key = $value;
        }
    }

    #[On('showLockConfirmation')]
    public function showLockConfirmation()
    {
        if (! auth()->user()->hasRole(User::ROLE_HS3) && ! auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
            Alert::fail($this, 'Gagal', 'Gagal Menutup Pengisian');

            return;
        }
        if (blank($this->selectedBatch) || $this->selectedBatch === 'all') {

            Alert::fail($this, 'Gagal', 'Silakan pilih batch terlebih dahulu');

            return;
        }

        Alert::confirmation(
            $this,
            Alert::ICON_QUESTION,
            'Tutup Pengisian Data',
            'Apakah Anda Yakin Ingin Mengunci Batch Ini ?',
            'on-lock-confirmation-dialog-confirm',
            'on-lock-confirmation-dialog-cancel',
            'Kunci',
            'Batal',
        );
    }

    #[On('on-lock-confirmation-dialog-confirm')]
    public function onDialogLockConfirmationConfirm()
    {
        if (! auth()->user()->hasRole(User::ROLE_HS3) && ! auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
            return;
        }
        if (blank($this->selectedBatch) || $this->selectedBatch === 'all') {

            Alert::fail($this, 'Gagal', 'Silakan pilih batch terlebih dahulu');

            return;
        }
        $clientsWithoutConfirmation = Client::query()
            ->where('clients.claim_type', '!=', Client::CLAIM_TYPE_KOKUMIN)
            ->where('clients.source_file_name', $this->selectedBatch)
            ->whereDoesntHave('confirmation')
            ->get();
        foreach ($clientsWithoutConfirmation as $client) {

            $client_confirmation = ClientConfirmation::firstOrCreate([
                'client_id' => $client->id,
            ], [
                'client_id' => $client->id,
                'nenkin_80_received_type' => ClientConfirmation::NENKIN_80_RECEIVED_TYPE_RECEIVED,
                'current_position' => ClientConfirmation::CURRENT_POSITION_JEPANG,
                'back_to_jp_type' => null,
                'departure_month' => null,
                'departure_year' => null,
                'estimated_return_month' => now()->addMonth()->format('m'),
                'estimated_return_year' => now()->addYear()->format('Y'),
                'indonesia_address' => null,
                'nik' => null,
            ]);
        }
        ClientConfirmation::query()
            ->whereIn('client_confirmations.claim_type', [
                Client::CLAIM_TYPE_SPEED,
                Client::CLAIM_TYPE_ADJUSTMENT,
            ])
            ->whereHas('client', function ($q) {
                $q->whereDoesntHave('esign')
                    ->where('source_file_name', $this->selectedBatch);
            })
            ->each(function ($confirmation) {
                $confirmation->update([
                    'claim_type' => Client::CLAIM_TYPE_NORMAL,
                ]);
            });

        Alert::information($this, 'Data Berhasil Disimpan');
    }

    #[On('on-lock-confirmation-dialog-cancel')]
    public function onDialogLockConfirmationCancel() {}

    #[On('showClosingConfirmation')]
    public function showClosingConfirmation()
    {
        if (! auth()->user()->hasRole(User::ROLE_HS3) && ! auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
            Alert::fail($this, 'Gagal', 'Gagal Membuat Closing');

            return;
        }
        if (blank($this->selectedBatch) || $this->selectedBatch === 'all') {

            Alert::fail($this, 'Gagal', 'Silakan pilih batch terlebih dahulu');

            return;
        }

        Alert::confirmation(
            $this,
            Alert::ICON_QUESTION,
            'Buat Kloter Closing',
            'Apakah Anda Yakin Ingin Closing Batch Ini ?',
            'on-closing-confirmation-dialog-confirm',
            'on-closing-confirmation-dialog-cancel',
            'Buat Closing',
            'Batal',
        );
    }

    #[On('on-closing-confirmation-dialog-confirm')]
    public function onDialogClosingConfirmationConfirm()
    {
        if (! auth()->user()->hasRole(User::ROLE_HS3) && ! auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
            Alert::fail($this, 'Gagal', 'Gagal Membuat Closing');

            return;
        }
        if (blank($this->selectedBatch) || $this->selectedBatch === 'all') {
            Alert::fail($this, 'Gagal', 'Silakan pilih batch terlebih dahulu');

            return;
        }

        $history = ClientClosingHistory::create([
            'source_file_name' => $this->selectedBatch,
        ]);
        DB::update("
            UPDATE client_confirmations AS cc
            SET
                client_closing_history_id = ?,
                client_closing_source_file_name = ?,
                client_closing_batch_number = ?,
                closed_at = NOW()
            FROM clients AS c
            WHERE cc.client_id = c.id
            AND cc.deleted_at IS NULL
            AND c.deleted_at IS NULL
            AND cc.client_closing_history_id IS NULL
            AND cc.client_closing_source_file_name IS NULL
            AND cc.client_closing_batch_number IS NULL
            AND cc.closed_at IS NULL
            AND c.source_file_name = ?
            AND (
                    cc.claim_type NOT IN ('speed', 'adjustment')
                    OR EXISTS (
                        SELECT 1
                        FROM client_esigns AS ce
                        WHERE ce.client_id = cc.client_id
                        AND ce.deleted_at IS NULL
                    )
                );
        ", [
            $history->id,
            $history->source_file_name,
            $history->batch_number,
            $history->source_file_name,
        ]);

        Alert::information($this, 'Data Berhasil Disimpan');
    }

    #[On('on-closing-confirmation-dialog-cancel')]
    public function onDialogClosingConfirmationCancel() {}

    // #[On('export')]
    // public function export($type)
    // {
    //     $fileName = "Data Client Registered " . Carbon::now()->format('Y-m-d H:i:s');
    //     $query = ClientRepository::getClientRegistered(
    //         $this->selectedBatch,
    //         $this->filter_claim_type,
    //         $this->bulkPic
    //     );

    //     $export = ExportHelper::export(
    //         $type,
    //         $fileName,
    //         $query,
    //         'app.master-client-registered.export',
    //         [
    //             'title' => 'Data Client Registered',
    //             'type' => $type,
    //         ],
    //         [
    //             'size' => 'legal',
    //             'orientation' => 'landscape',
    //         ]
    //     );

    //     if (auth()->user()->hasRole(\App\Models\User::ROLE_HS3)) {
    //         $query->update([
    //             'is_exported' => true,
    //             'exported_at' => now(),
    //         ]);
    //     }
    //     return $export;
    // }
    #[On('export')]
    public function export($type)
    {
        if (blank($this->selectedBatch) || $this->selectedBatch === 'all') {
            Alert::fail($this, 'Gagal', 'Silakan pilih batch terlebih dahulu');

            return;
        }
        $fileName = 'Data Client Konfirmasi '.Carbon::now()->format('Y-m-d H:i:s');

        $qhs3 = ClientRepository::getClientRegistered(
            $this->selectedBatch,
            $this->filter_claim_type,
            $this->bulkPic
        );
        $data['query'][] = $qhs3;
        $data['view'][] = 'app.master-client-registered.export';
        $data['request'][] = [
            'title' => 'Data Client Registered',
            'type' => $type,
        ];

        $closingHistories = ClientClosingHistoryRepository::getBy([
            ['source_file_name', $this->selectedBatch],
        ]);

        $kl_number = 1;
        foreach ($closingHistories as $index => $closing) {
            $q = ClientRepository::getClientRegistered(
                $this->selectedBatch,
                $this->filter_claim_type,
                $this->bulkPic,
                $closing->id,
                true
            );
            if ($q->count()) {
                $data['query'][] = $q;

                $data['view'][] = 'app.master-client-registered.export-closing';
                $data['request'][] = [
                    'title' => 'Closing '.($kl_number).' '.$closing->created_at->format('Ymd H_i_s'),
                    'type' => $type,
                ];
                $kl_number++;
            }
        }

        $data['query'][] = ClientRepository::getClientUnclosing(
            $this->selectedBatch,
            $this->filter_claim_type,
            $this->bulkPic
        );
        $data['view'][] = 'app.master-client-registered.export-closing';
        $data['request'][] = [
            'title' => 'Data Client Belum Closing',
            'type' => $type,
        ];
        $data['query'][] = ClientRepository::getClientUnregistered(
            $this->selectedBatch,
            $this->filter_claim_type,
            $this->bulkPic
        );
        $data['view'][] = 'app.master-client-registered.export';
        $data['request'][] = [
            'title' => 'Data Client Belum Konfirmasi',
            'type' => $type,
        ];
        $data['query'][] = ClientRepository::getClientKokumin(
            $this->selectedBatch,
            $this->filter_claim_type,
            $this->bulkPic
        );
        $data['view'][] = 'app.master-client-registered.export';
        $data['request'][] = [
            'title' => 'Data Client Kokumin',
            'type' => $type,
        ];
        // dd($data['request'][0]);

        $export = ExportHelper::export(
            $type,
            $fileName,
            $data['query'],
            $data['view'],
            $data['request'],
            [
                'size' => 'legal',
                'orientation' => 'landscape',
            ],
            true
        );

        if (auth()->user()->hasRole(User::ROLE_HS3)) {
            $qhs3->where('is_exported', false)
                ->whereNull('exported_at')
                ->update([
                    'is_exported' => true,
                    'exported_at' => now(),
                ]);
        }

        return $export;
    }

    #[On('exportReEntry')]
    public function exportReEntry($type)
    {
        if (blank($this->selectedBatch) || $this->selectedBatch === 'all') {
            Alert::fail($this, 'Gagal', 'Silakan pilih batch terlebih dahulu');

            return;
        }
        $fileName = 'Data Client Re-Entry '.Carbon::now()->format('Y-m-d H:i:s');

        $qhs3 = ClientRepository::getClientReEntry(
            $this->selectedBatch,
            $this->filter_claim_type,
            $this->bulkPic
        );
        $data['query'][] = $qhs3;
        $data['view'][] = 'app.master-client-registered.export';
        $data['request'][] = [
            'title' => 'Data Client Re-Entry',
            'type' => $type,
        ];
        // dd($data['request'][0]);

        $export = ExportHelper::export(
            $type,
            $fileName,
            $data['query'],
            $data['view'],
            $data['request'],
            [
                'size' => 'legal',
                'orientation' => 'landscape',
            ],
            true
        );

        return $export;
    }

    public function getView(): string
    {
        return 'livewire.master-client-registered.datatable';
    }
}
