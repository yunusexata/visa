<?php

namespace App\Models\VisaForm;

use App\Enums\Form\VisaAttachmenStatus;
use App\Enums\Form\VisaAttachmentType;
use App\Enums\Gensen\GensenAttachmenStatus;
use App\Enums\Gensen\GensenAttachmentType;
use App\Enums\Gensen\JobStatus;
use App\Models\Ai\AiJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

#[Fillable([
    'gensen_form_id',
    'upload_batch_id',
    'type',
    'original_name',       // KK_andi.jpg
    'stored_name',         // uuid filename
    'description',

    'disk',                // local / s3
    'path',                // storage path
    'note',                // attachment note

    'extension',           // jpg
    'mime_type',           // image/jpeg
    'file_size',           // bytes

    'checksum',
    'status',               // STORED ,REJECTED, EDITED
    'convert_image',        // BOOLEAN
])]
class VisaFormAttachment extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $guarded = ['id'];

    protected $casts = [
        'type' => VisaAttachmentType::class,
        'status' => VisaAttachmenStatus::class,
    ];

    public function isDeletable()
    {
        return true;
    }

    public function isEditable()
    {
        return true;
    }
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function previewUrl(): string
    {
        return match ($this->disk) {

            // =========================================
            // LOCAL PUBLIC
            // =========================================

            'public' => URL::temporarySignedRoute(
                'gensen.attachment.preview',
                now()->addMinutes(30),
                [
                    'attachment' => $this->id,
                ]
            ),

            // =========================================
            // LOCAL PRIVATE
            // =========================================

            'private' => URL::temporarySignedRoute(
                'gensen.attachment.preview',
                now()->addMinutes(30),
                [
                    'attachment' => $this->id,
                ]
            ),

            // =========================================
            // SUPABASE
            // =========================================
            'supabase' => ($this->type === GensenAttachmentType::SELURUH_BERKAS) ?
                URL::temporarySignedRoute(
                    'gensen.attachment.preview-supabase',
                    now()->addMinutes(30),
                    [
                        'attachment' => $this->id,
                    ]
                ) : 'https://pevrthazwqqzmxrthphg.supabase.co/storage/v1/object/public/gensen-exata/' . $this->path,
            default => throw new \Exception(
                "Unsupported disk [{$this->disk}]"
            ),
        };
    }

    public function saveInfo($object, $data = false, $prefix = "")
    {
        if ($data) {
        } else {
            $object[$prefix . 'type'] = $this->type?->value;
            $object[$prefix . 'original_name'] = $this->original_name;
            $object[$prefix . 'stored_name'] = $this->stored_name;
            $object[$prefix . 'description'] = $this->description;
            $object[$prefix . 'disk'] = $this->disk;
            $object[$prefix . 'path'] = $this->path;
            $object[$prefix . 'note'] = $this->note;
            $object[$prefix . 'remittance_type'] = $this->remittance_type?->value;
            $object[$prefix . 'extension'] = $this->extension;
            $object[$prefix . 'mime_type'] = $this->mime_type;
            $object[$prefix . 'file_size'] = $this->file_size;
            $object[$prefix . 'checksum'] = $this->checksum;
            $object[$prefix . 'status'] = $this->status?->value;
        }

        return $object;
    }

    public function url(): string
    {
        return $this->type->is(GensenAttachmentType::REKAP_PENGIRIMAN_UANG)
            ? generateUrl(
                "gensen/{$this->gensen_form_id}/{$this->type->value}",
                $this->path
            )
            : generateUrl(
                "gensen/{$this->gensen_form_id}/{$this->type->value}/{$this->note}",
                $this->path
            );
    }

    protected static function onBoot()
    {
        self::creating(function ($model) {
            if (!$model->status) {
                $model->status = GensenAttachmenStatus::STATUS_STORED;
            }
        });
    }

    public function gensenForm()
    {
        return $this->belongsTo(GensenForm::class, 'gensen_form_id', 'id');
    }

    public function latestJob()
    {
        if ($this->type == GensenAttachmentType::PERSYARATAN_PENGURUSAN_GENSEN) {

            return $this->hasOne(
                PersyaratanGensenJob::class,
                'gensen_form_id', // FK in jobs table
                'gensen_form_id'              // PK in gensen_forms
            )
                ->latest('id');
        }
        if ($this->type == GensenAttachmentType::SELURUH_BERKAS) {
            return $this->hasOne(
                SeluruhBerkasJob::class,
                'gensen_form_id', // FK in jobs table
                'gensen_form_id'              // PK in gensen_forms
            )
                ->latest('id');
        }
    }
    public function isJobProcess()
    {
        return $this->latestJob;
    }
    public function isJobProcessDone()
    {
        return $this->latestJob?->status === JobStatus::DONE;
    }

    public function aiJobs()
    {
        return $this->morphMany(AiJob::class, 'subject');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updator()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function deletor()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }
}
