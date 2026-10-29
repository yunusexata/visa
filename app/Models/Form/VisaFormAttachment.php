<?php

namespace App\Models\Form;

use App\Enums\Form\VisaAttachmenStatus;
use App\Enums\Form\VisaAttachmentType;
use Database\Factories\Form\VisaFormAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

class VisaFormAttachment extends Model
{
    use HasFactory, HasTrackHistory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis_dokumen' => VisaAttachmentType::class,
            'status' => VisaAttachmenStatus::class,
            'ukuran_file' => 'integer',
            'urutan' => 'integer',
        ];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return VisaFormAttachmentFactory::new();
    }

    /**
     * Relasi ke Formulir Visa induk.
     */
    public function visaForm(): BelongsTo
    {
        return $this->belongsTo(VisaForm::class, 'visa_form_id');
    }

    /**
     * Helper accessor untuk mendapatkan URL publik berkas.
     */
    public function getUrlAttribute(): ?string
    {
        if (empty($this->jalur_file)) {
            return null;
        }

        return Storage::disk($this->disk ?: 'public')->url($this->jalur_file);
    }
}
