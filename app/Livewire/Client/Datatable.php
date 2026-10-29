<?php

namespace App\Livewire\MasterClient;

use App\Helpers\Alert;
use App\Helpers\PermissionHelper;
use App\Repositories\Account\UserRepository;
use App\Repositories\Client\ClientRepository;
use App\Traits\Livewire\WithDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
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

    // Delete Dialog
    public $targetDeleteId;

    public function onMount()
    {
        $authUser = UserRepository::authenticatedUser();
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_IMPORT_DATA_JEPANG, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_IMPORT_DATA_JEPANG, PermissionHelper::TYPE_DELETE));
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

        ];
    }

    public function getQuery(): Builder
    {
        return ClientRepository::datatable();
    }

    public function getView(): string
    {
        return 'livewire.master-client.datatable';
    }
}
