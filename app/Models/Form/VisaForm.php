<?php

namespace App\Models\Form;

use App\Enums\Form\VisaFormStatus;
use App\Helpers\NumberGenerator;
use App\Models\User;
use App\Traits\Models\LowercaseAttributes;
use App\Traits\Models\UppercaseAttributes;
use Database\Factories\Form\VisaFormFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

class VisaForm extends Model
{
    use HasFactory, HasTrackHistory, LowercaseAttributes, SoftDeletes, UppercaseAttributes;

    protected $guarded = ['id'];

    protected array $uppercase = [
        'nama_lengkap',
        'tempat_lahir',
        'kantor_paspor',
        'nomor_paspor',
        'nomor_coe',
        'nama_kaisha',
        'nama_penanggung_jawab',
    ];

    protected array $lowercase = [
        'email_aktif',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => VisaFormStatus::class,
            'tanggal_lahir' => 'date',
            'tanggal_pengeluaran_paspor' => 'date',
            'tanggal_habis_berlaku_paspor' => 'date',
            'tanggal_kedatangan' => 'date',
            'pernyataan_persetujuan' => 'boolean',
            'tanggal_diajukan' => 'datetime',
            'tanggal_verifikasi' => 'datetime',
        ];
    }

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->nomor_registrasi = $model->nomor_registrasi ?? NumberGenerator::generate(self::class);
            $model->status = $model->status ?? VisaFormStatus::DRAFT;
            // $model->created_by = rand(7, 9);
        });
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return VisaFormFactory::new();
    }

    /**
     * Relasi ke seluruh berkas lampiran dokumen.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(VisaFormAttachment::class, 'visa_form_id');
    }

    /**
     * Relasi ke magic link asal.
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo(VisaFormLink::class, 'visa_form_link_id');
    }

    /**
     * Relasi ke user pembuat (jika diinput oleh admin/staff).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke user pengubah / verifikator.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope untuk formulir yang berstatus DIAJUKAN.
     */
    public function scopeDiajukan(Builder $query): Builder
    {
        return $query->where('status', VisaFormStatus::DIAJUKAN);
    }

    /**
     * Scope untuk memfilter berdasarkan PIC code.
     */
    public function scopeByPic(Builder $query, string $picCode): Builder
    {
        return $query->where('pic_code', $picCode);
    }
}
