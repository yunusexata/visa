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
            'file_size' => 'integer',
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
     * Helper method untuk mendapatkan URL berkas (public, private, atau direct supabase url).
     */
    public function getUrl(): ?string
    {
        $filePath = $this->path ?? $this->attributes['jalur_file'] ?? null;
        if (empty($filePath)) {
            return null;
        }

        $diskName = $this->disk ?: config('filesystems.default_store_disk', env('DEFAULT_STORE_DISK', 'public'));

        if ($diskName === 'supabase') {
            $baseUrl = config('filesystems.disks.supabase.public_url', env('SUPABASE_PUBLIC_URL', ''));
            if (! empty($baseUrl)) {
                return rtrim($baseUrl, '/').'/'.ltrim($filePath, '/');
            }

            return (string) env('SUPABASE_PUBLIC_URL').$filePath;
        }

        return Storage::disk($diskName)->url($filePath);
    }

    /**
     * Helper accessor untuk mendapatkan URL publik berkas.
     */
    public function getUrlAttribute(): ?string
    {
        return $this->getUrl();
    }

    // Accessor aliases untuk kompatibilitas kode
    public function getUkuranFileAttribute(): ?int
    {
        return $this->file_size ?? $this->attributes['ukuran_file'] ?? null;
    }

    public function getEkstensiAttribute(): ?string
    {
        return $this->extension ?? $this->attributes['ekstensi'] ?? null;
    }

    public function getJalurFileAttribute(): ?string
    {
        return $this->path ?? $this->attributes['jalur_file'] ?? null;
    }

    public function getTipeMimeAttribute(): ?string
    {
        return $this->mime_type ?? $this->attributes['tipe_mime'] ?? null;
    }
}
