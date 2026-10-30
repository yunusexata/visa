<?php

namespace App\Repositories\Form;

use App\Models\Form\VisaForm;
use App\Repositories\MasterDataRepository;
use Illuminate\Database\Eloquent\Builder;

class VisaFormRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return VisaForm::class;
    }

    /**
     * Query builder untuk datatable dengan eager count dan optimasi kolom.
     */
    public static function datatable(?string $status = null): Builder
    {
        return VisaForm::query()
            ->select([
                'id',
                'nomor_registrasi',
                'nama_lengkap',
                'pic_code',
                'nomor_paspor',
                'nomor_coe',
                'nama_kaisha',
                'tanggal_kedatangan',
                'status',
                'created_at',
                'tanggal_diajukan',
                'visa_form_link_id',
                'created_by',
            ])
            ->withCount('attachments')
            ->when($status && $status !== 'Semua', function (Builder $query) use ($status) {
                $query->where('status', $status);
            });
    }
}
