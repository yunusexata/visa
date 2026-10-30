<?php

namespace App\Livewire\Form\VisaFormLink;

use App\Helpers\Alert;
use App\Helpers\PermissionHelper;
use App\Repositories\Account\UserRepository;
use App\Repositories\Form\VisaFormLinkRepository;
use App\Traits\Livewire\WithDatatable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;
use Vinkla\Hashids\Facades\Hashids;

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
        $this->isCanUpdate = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_VISA_FORM_LINK, PermissionHelper::TYPE_UPDATE));
        $this->isCanDelete = $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_VISA_FORM_LINK, PermissionHelper::TYPE_DELETE));
        $this->sortBy = 'expired_at';
        $this->sortDirection = 'DESC';
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (! $this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }

        VisaFormLinkRepository::delete($this->targetDeleteId);
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
            [
                'name' => 'Action',
                'sortable' => false,
                'searchable' => false,
                'render' => function ($item) {
                    $id = Hashids::encode($item->id);

                    $editHtml = '';
                    // if ($this->isCanUpdate) {
                    //     $editUrl = route('gensen_form_link.edit', $id);
                    //     $editHtml = "
                    //         <a href='{$editUrl}'
                    //            title='Edit'
                    //            class='inline-flex items-center justify-center p-1.5 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded-lg transition-colors focus:outline-none'>
                    //             <span class='material-symbols-outlined text-lg'>edit</span>
                    //         </a>";
                    // }

                    $destroyHtml = '';
                    if ($this->isCanDelete) {
                        $destroyHtml = "
                <button type='button' 
                        wire:click=\"showDeleteDialog({$item->id})\" 
                        title='Delete'
                        class='inline-flex items-center justify-center p-1.5 text-rose-600 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors focus:outline-none'>
                    <span class='material-symbols-outlined text-lg'>delete</span>
                </button>";
                    }

                    $link = url('/form/'.$id);
                    $linkHtml = "
            <button type='button' 
                    onclick=\"copyToClipboard('{$link}')\" 
                    title='Salin Tautan'
                    class='inline-flex items-center justify-center p-1.5 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition-colors focus:outline-none'>
                <span class='material-symbols-outlined text-lg'>content_copy</span>
            </button>";

                    return "<div class='inline-flex items-center gap-1.5 whitespace-nowrap'>
            {$editHtml}
            {$destroyHtml}
            {$linkHtml}
        </div>";
                },
            ],
            [
                'key' => 'expired_at',
                'name' => 'Expired Pada',
                'render' => function ($item) {
                    return $item->expired_at;
                },
            ],
            [
                'key' => 'name',
                'name' => 'Nama',
            ],
            [
                'key' => 'password',
                'name' => 'Password',
            ],
            [
                'key' => 'status',
                'name' => 'Status',
                'render' => function ($item) {
                    return $item->status->label();
                },
            ],
            [
                'key' => 'max_usage',
                'name' => 'Maks Penggunaan',
            ],
            [
                'key' => 'used_count',
                'name' => 'Jumlah Terpakai',
                'render' => function ($item) {
                    return $item->used_count;
                },
            ],
            [
                'sortable' => false,
                'searcable' => false,
                'name' => 'Kode PIC',
                'render' => function ($item) {
                    return $item->pic_code;
                },
            ],

        ];
    }

    public function getQuery(): Builder
    {
        return VisaFormLinkRepository::datatable($this->status);
    }

    public function getView(): string
    {
        return 'livewire.form.visa-form-link.datatable';
    }
}
