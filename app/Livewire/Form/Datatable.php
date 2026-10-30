<?php

namespace App\Livewire\Form;

use App\Helpers\Alert;
use App\Helpers\PermissionHelper;
use App\Models\Form\VisaForm;
use App\Models\User;
use App\Repositories\Account\UserRepository;
use App\Repositories\Form\VisaFormRepository;
use App\Services\Form\VisaJapanPdfService;
use App\Traits\Livewire\WithDatatable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Datatable extends Component
{
    use WithDatatable;

    public int $perPageStep = 50;

    public bool $isCanUpdate = false;

    public bool $isCanDelete = false;

    // Filter status
    public ?string $status = null;

    // Delete confirmation id
    public ?int $targetDeleteId = null;

    public function onMount(): void
    {
        $this->length = 50;
        $this->lengthOptions = [50, 100, 150, 200];

        $authUser = UserRepository::authenticatedUser();
        if ($authUser) {
            $isSuperAdmin = $authUser->hasRole(User::ROLE_SUPERADMIN);
            $this->isCanUpdate = $isSuperAdmin || $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_VISA_FORM, PermissionHelper::TYPE_UPDATE));
            $this->isCanDelete = $isSuperAdmin || $authUser->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_VISA_FORM, PermissionHelper::TYPE_DELETE));
        }

        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
    }

    public function generateVisaJapanPdf(int $id, VisaJapanPdfService $service): StreamedResponse
    {
        $visaForm = VisaForm::findOrFail($id);
        $pdfBinary = $service->generate($visaForm);
        $safeNoReg = str_replace(['/', '\\'], '_', $visaForm->nomor_registrasi ?: (string) $visaForm->id);
        $filename = "Visa_Japan_Application_{$safeNoReg}.pdf";

        return response()->streamDownload(function () use ($pdfBinary) {
            echo $pdfBinary;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->length = 50;
    }

    /**
     * Infinite scroll trigger from Alpine.js
     */
    public function loadMore(): void
    {
        $this->length += $this->perPageStep;
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm(): void
    {
        if (! $this->isCanDelete || ! $this->targetDeleteId) {
            return;
        }

        VisaForm::destroy($this->targetDeleteId);
        $this->targetDeleteId = null;
        Alert::success($this, 'Berhasil', 'Data permohonan visa berhasil dihapus.');
    }

    #[On('on-delete-dialog-cancel')]
    public function onDialogDeleteCancel(): void
    {
        $this->targetDeleteId = null;
    }

    public function showDeleteDialog(int $id): void
    {
        $this->targetDeleteId = $id;

        Alert::confirmation(
            $this,
            Alert::ICON_QUESTION,
            'Hapus Data',
            'Apakah Anda yakin ingin menghapus data permohonan visa ini?',
            'on-delete-dialog-confirm',
            'on-delete-dialog-cancel',
            'Hapus',
            'Batal'
        );
    }

    public function getColumns(): array
    {
        return [
            ['key' => 'nomor_registrasi', 'name' => 'ID', 'sortable' => true, 'searchable' => true],
            ['key' => 'nama_lengkap', 'name' => 'Nama Pemohon', 'sortable' => true, 'searchable' => true],
            ['key' => 'nama_kaisha', 'name' => 'Kaisha / LPK', 'sortable' => true, 'searchable' => true],
            ['key' => 'nomor_paspor', 'name' => 'No. Paspor', 'sortable' => true, 'searchable' => true],
            ['key' => 'nomor_coe', 'name' => 'No. COE', 'sortable' => true, 'searchable' => true],
            ['key' => 'pic_code', 'name' => 'Kode PIC', 'sortable' => true, 'searchable' => true],
            ['key' => 'tanggal_kedatangan', 'name' => 'Tgl Berangkat', 'sortable' => true, 'searchable' => false],
            ['key' => 'status', 'name' => 'Status', 'sortable' => true, 'searchable' => true],
            ['key' => 'created_at', 'name' => 'Tgl Dibuat', 'sortable' => true, 'searchable' => false],
        ];
    }

    public function getQuery(): Builder
    {
        return VisaFormRepository::datatable($this->status);
    }

    public function getView(): string
    {
        return 'livewire.form.visa-form';
    }
}
