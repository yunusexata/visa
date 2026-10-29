<?php

namespace App\Livewire\Account\Permission;

use App\Helpers\Alert;
use App\Helpers\PermissionHelper;
use App\Repositories\Account\PermissionRepository;
use App\Repositories\Account\UserRepository;
use App\Traits\Livewire\WithDatatable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;

class Datatable extends Component
{
    use WithDatatable;

    public $isCanUpdate;

    public $isCanDelete;

    // Delete Dialog
    public $targetDeleteId;

    public function onMount()
    {
        $authUser = UserRepository::authenticatedUser();
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_PERMISSION, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_PERMISSION, PermissionHelper::TYPE_DELETE));
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (! $this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }

        PermissionRepository::delete($this->targetDeleteId);
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

    public function getColumns(): array
    {
        return [
            [
                'name' => 'Action',
                'sortable' => false,
                'searchable' => false,
                'render' => function ($item) {
                    $editHtml = '';
                    if ($this->isCanUpdate) {

                        $editUrl = route('permission.edit', $item->id);
                        $editHtml = "<div class='col-auto'>
                            <a type='button' href='$editUrl' class='inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-2 py-1 text-sm bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600 disabled:bg-brand-300'>
                                <span class='material-symbols-outlined text-lg' data-icon='edit'>edit</span>
                            </a>
                        </div>";
                    }

                    $destroyHtml = '';
                    $destroyHtml = '';
                    if ($this->isCanDelete) {
                        $destroyHtml = "<div class='col-auto'>
                            <button type='button' class='inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-2 py-1 text-sm bg-red-500 text-white shadow-theme-xs hover:bg-red-600 disabled:bg-brand-300' wire:click=\"showDeleteDialog($item->id)\">
                                <span class='material-symbols-outlined text-lg' data-icon='delete'>delete</span>
                            </button>
                        </div>";
                    }
                    $html = "<div class='flex flex-nowrap justify-start p-0 m-0 gap-1'>
                        $editHtml $destroyHtml
                    </div>";

                    return $html;
                },
            ],
            [
                'key' => 'name',
                'name' => 'Nama',
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Alias',
                'render' => function ($item) {
                    return PermissionHelper::translate($item->name);
                },
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return PermissionRepository::datatable();
    }

    public function getView(): string
    {
        return 'livewire.account.permission.datatable';
    }
}
