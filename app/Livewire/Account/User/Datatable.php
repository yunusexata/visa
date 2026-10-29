<?php

namespace App\Livewire\Account\User;

use App\Helpers\Alert;
use App\Helpers\PermissionHelper;
use App\Repositories\Account\UserRepository;
use App\Traits\Livewire\WithDatatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;

class Datatable extends Component
{
    use WithDatatable;

    public $isCanUpdate;

    public $isCanDelete;

    // Filter
    public $role;

    // Delete Dialog
    public $targetDeleteId;

    public function onMount()
    {
        $authUser = UserRepository::authenticatedUser();
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_USER, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_USER, PermissionHelper::TYPE_DELETE));
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (! $this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }

        UserRepository::delete($this->targetDeleteId);
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
        $columns = [
            [
                'name' => 'Action',
                'sortable' => false,
                'searchable' => false,
                'render' => function ($item) {
                    $editHtml = '';
                    if ($this->isCanUpdate) {

                        $editUrl = route('user.edit', $item->id);
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
                'name' => 'Name',
            ],
            // [
            //     'key' => 'username',
            //     'name' => 'Username',
            // ],
            [
                'key' => 'email',
                'name' => 'Email',
            ],
            [
                'sortable' => false,
                'searchable' => false,
                'name' => 'Role',
                'render' => function ($item) {
                    return count($item->roles) > 0 ? $item->roles[0]->name : '';
                },
            ],
        ];

        if (config('template.email_verification_route')) {
            $columns[] =
                [
                    'sortable' => false,
                    'searchable' => false,
                    'name' => 'Verifikasi Email',
                    'render' => function ($item) {
                        if ($item->email_verified_at) {
                            $verifiedAt = Carbon::parse($item->email_verified_at)->isoFormat('DD MMMM Y, HH:mm');

                            return "<div class='badge badge-success'>$verifiedAt</div>";
                        } else {
                            return "<div class='badge badge-secondary'>Belum Verifikasi</div>";
                        }
                    },
                ];
        }

        return $columns;
    }

    public function getQuery(): Builder
    {
        return UserRepository::datatable($this->role);
    }

    public function getView(): string
    {
        return 'livewire.account.user.datatable';
    }
}
