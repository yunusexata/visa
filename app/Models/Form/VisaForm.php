<?php

namespace App\Models\GensenForm;

use App\Enums\Gensen\EmailLogStatus;
use App\Enums\Gensen\GensenAttachmenStatus;
use App\Enums\Gensen\GensenAttachmentType;
use App\Enums\Gensen\GensenFormDetailStatus;
use App\Enums\Gensen\JobStatus;
use App\Helpers\AppLog;
use App\Helpers\NumberGenerator;
use App\Helpers\PermissionHelper;
use App\Mail\Admin\ClientNewSubmission;
use App\Models\Ai\AiJob;
use App\Models\Gensen\Ai\RemittanceExtraction;
use App\Models\User;
use App\Repositories\Account\UserRepository;
use App\Repositories\GensenForm\PersyaratanGensenJobRepository;
use App\Repositories\GensenForm\SeluruhBerkasJobRepository;
use App\Repositories\Service\SendEmailLogRepository;
use App\Traits\Models\LowercaseAttributes;
use App\Traits\Models\UppercaseAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

class GensenForm extends Model
{
    //  FIlter export
    //  Import excel (bulk update)
    //  poin of recommendation
    //  max 3
    use HasFactory, SoftDeletes, HasTrackHistory, UppercaseAttributes, LowercaseAttributes;

    protected $fillable = [
        // Sistem
        'id_customer',
        'status',

        // ---------- //
        // Form Input //
        // ---------- //

        // Data Diri
        'nama_lengkap',
        'tanggal_lahir',
        'email',
        'tanggal_kepulangan',
        'nama_instagram',
        'nama_tiktok',
        'nomor_whatsapp',
        'nomor_whatsapp_darurat',
        'alamat_jepang',
        'kode_pos_jepang',
        'nama_lpk',

        // REK PENERIMA
        'no_rekening_penerima',
        'nama_bank_penerima',
        'nama_penerima',
        'hubungan_penerima',

        // Gensen
        'tahun_gensen',
        'tahun_transfer',

        // Relasi History
        'remarks_id',
        'remarks_type',
        'pic_code',

        // Validasi
        'is_should_filled',
        'is_submitted',

        // ----------- //
        // Flow Bisnis //
        // ----------- //

        // Step 1 - HS
        'tanggal_lengkap',

        // Step 2 - HS2
        'tanggal_verified',

        // Step 3 - Admin Jepang
        'no_input_jepang',
        'tanggal_cancel',
        'tanggal_honnin',
        'tanggal_mondai',

        // Step 4 - Admin Jepang
        'tanggal_pengajuan',  // Tanggal Pengajuan Ke Kantor Pajak

        // Step 6 - Acc Exata

        // MONDAI
        'keterangan',
        'is_previously_processed',
    ];

    const STATUS_BELUM_LENGKAP = 'BELUM LENGKAP';
    const STATUS_SIAP_VERIFIKASI = 'SIAP VERIFIKASI'; // NOT INCLUDE IN FLOW / CUSTOM STATUS 
    const STATUS_LENGKAP = 'LENGKAP';
    const STATUS_VERIFIED = 'VERIFIED';
    const STATUS_NO_INPUT_JEPANG = 'NO INPUT JEPANG'; // NOT INCLUDE IN FLOW / CUSTOM STATUS 
    const STATUS_DALAM_PENGAJUAN = 'DALAM PENGAJUAN';
    const STATUS_TARIK_DATA = 'TARIK DATA ACC';
    const STATUS_GENSEN_CAIR = 'GENSEN CAIR';
    const STATUS_CANCEL = 'CANCEL';
    const STATUS_HONNIN = 'HONNIN';
    const STATUS_MONDAI = 'MONDAI';
    const STATUS_CHOICE = [

        self::STATUS_BELUM_LENGKAP => 'BELUM LENGKAP',
        self::STATUS_LENGKAP => 'LENGKAP',
        self::STATUS_SIAP_VERIFIKASI => 'SIAP VERIFIKASI',
        self::STATUS_VERIFIED => 'VERIFIED',
        self::STATUS_NO_INPUT_JEPANG => 'NO INPUT JEPANG',
        self::STATUS_DALAM_PENGAJUAN => 'DALAM PENGAJUAN',
        self::STATUS_TARIK_DATA => 'TARIK DATA ACC',
        self::STATUS_GENSEN_CAIR => 'GENSEN CAIR',
        self::STATUS_CANCEL => 'CANCEL',
        self::STATUS_HONNIN => 'HONNIN',
        self::STATUS_MONDAI => 'MONDAI',
    ];

    const ATTACHMENT_ORDER_BY = [
        GensenAttachmentType::KERTAS_GENSEN->value,
        GensenAttachmentType::ZAIRYOU_CARD_FRONT->value,
        GensenAttachmentType::ZAIRYOU_CARD_BACK->value,
        GensenAttachmentType::MY_NUMBER_FRONT->value,
        GensenAttachmentType::MY_NUMBER_BACK->value,
        GensenAttachmentType::REKENING_INDONESIA->value,
        GensenAttachmentType::REKAP_PENGIRIMAN_UANG->value,
        GensenAttachmentType::KARTU_KELUARGA->value,
    ];
    protected array $uppercase = [
        'nama_lengkap',
        'nama_lpk',
        'nama_penerima',
        'hubungan_penerima',
    ];
    protected array $lowercase = [
        'email',
    ];
    public function statusColor()
    {
        return match ($this->status) {
            self::STATUS_BELUM_LENGKAP => '#eae1e5',
            self::STATUS_LENGKAP => '#5DEBD7',
            self::STATUS_VERIFIED => '#F9B2D7',
            self::STATUS_DALAM_PENGAJUAN => '#4689e8',
            self::STATUS_TARIK_DATA => '#b2dc46',
            self::STATUS_GENSEN_CAIR => '#2bdf46',
            self::STATUS_CANCEL => '#f4b989',
            self::STATUS_HONNIN => '#D1855C',
            self::STATUS_MONDAI => '#e80606',
            default => '#ffffff',
        };
    }

    protected $guarded = ['id'];

    public function isDeletable()
    {
        return true;
    }

    public function isEditable()
    {
        return true;
    }

    public function isCanDelete()
    {
        return $this->isDeletable() && UserRepository::authenticatedUser()->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_GENSEN_DATA, PermissionHelper::TYPE_DELETE));
    }

    public function isCanUpdate()
    {
        return $this->isEditable() && UserRepository::authenticatedUser()->hasPermissionTo(PermissionHelper::transform(PermissionHelper::ACCESS_GENSEN_DATA, PermissionHelper::TYPE_UPDATE));
    }

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->id_customer = $model->id_customer ?? NumberGenerator::generate(self::class);
            $model->status = $model->status ?? self::STATUS_BELUM_LENGKAP;
            // $model->created_by = rand(7, 9);
        });
        self::updating(function ($model) {
            if ($model->isDirty('tanggal_cancel') && $model->tanggal_cancel) {
                AppLog::info(
                    'Updating Tanggal Cancel',
                    'models_gensen_form',
                    [],
                    [
                        'id' => $model->id,
                        'from' => $model->getOriginal('tanggal_cancel'),
                        'to' => $model->tanggal_cancel,
                    ],
                    'gensen_form_status_cancel'
                );
                $model->status = self::STATUS_CANCEL;
            }
            if ($model->isDirty('tanggal_lengkap') && $model->tanggal_lengkap) {
                AppLog::info(
                    'Updating Tanggal Lengkap',
                    'models_gensen_form',
                    [],
                    [
                        'id' => $model->id,
                        'from' => $model->getOriginal('tanggal_lengkap'),
                        'to' => $model->tanggal_lengkap,
                    ],
                );
                $model->status = self::STATUS_LENGKAP;
            }
            if ($model->isDirty('tanggal_verified') && $model->tanggal_verified) {
                AppLog::info(
                    'Updating Tanggal Verified',
                    'models_gensen_form',
                    [],
                    [
                        'id' => $model->id,
                        'from' => $model->getOriginal('tanggal_verified'),
                        'to' => $model->tanggal_verified,
                    ],
                );
                $model->status = self::STATUS_VERIFIED;
            }

            if ($model->isDirty('tanggal_pengajuan') && $model->tanggal_pengajuan) {
                AppLog::info(
                    'Updating Tanggal Pengajuan',
                    'models_gensen_form',
                    [],
                    [
                        'id' => $model->id,
                        'from' => $model->getOriginal('tanggal_pengajuan'),
                        'to' => $model->tanggal_pengajuan,
                    ],
                );
                $model->status = self::STATUS_DALAM_PENGAJUAN;
            }
            if ($model->isDirty('tanggal_tarik_data') && $model->tanggal_tarik_data) {
                AppLog::info(
                    'Updating Tanggal Tarik Data',
                    'models_gensen_form',
                    [],
                    [
                        'id' => $model->id,
                        'from' => $model->getOriginal('tanggal_tarik_data'),
                        'to' => $model->tanggal_tarik_data,
                    ],
                );
                $model->status = self::STATUS_TARIK_DATA;
            }
            if ($model->isDirty('tanggal_mondai') && $model->tanggal_mondai && $model->isDirty('status') && $model->status === self::STATUS_MONDAI) {
                $updated_no_input_jepang = "M" . $model->no_input_jepang;
                AppLog::info(
                    'Updating Mondai No Input Japan from ' . $model->no_input_jepang . ' to ' . $updated_no_input_jepang,
                    'models_gensen_form',
                    [],
                    [
                        'id' => $model->id,
                        'from' => $model->no_input_jepang,
                        'to' => $updated_no_input_jepang,
                    ],
                );
                $model->no_input_jepang = $updated_no_input_jepang;
            }
        });
        self::created(function ($model) {
            if ($model->remarks_type === GensenFormLink::class) {
                $model->remarks->incrementUsedCount();
            }
        });
        self::deleted(function ($model) {
            if ($model->remarks_type === GensenFormLink::class) {
                $model->remarks->decrementUsedCount();
            }
        });
    }

    public function copyInfo($object, $data = false, $prefix = "")
    {
        if ($data) {
        } else {

            // ---------- //
            // Form Input //
            // ---------- //

            // Data Diri
            $object[$prefix . 'nama_lengkap'] = $this->nama_lengkap;
            $object[$prefix . 'tanggal_lahir'] = $this->tanggal_lahir;
            $object[$prefix . 'email'] = $this->email;
            $object[$prefix . 'tanggal_kepulangan'] = $this->tanggal_kepulangan;
            $object[$prefix . 'nama_instagram'] = $this->nama_instagram;
            $object[$prefix . 'nama_tiktok'] = $this->nama_tiktok;
            $object[$prefix . 'nomor_whatsapp'] = $this->nomor_whatsapp;
            $object[$prefix . 'nomor_whatsapp_darurat'] = $this->nomor_whatsapp_darurat;
            $object[$prefix . 'alamat_jepang'] = $this->alamat_jepang;
            $object[$prefix . 'kode_pos_jepang'] = $this->kode_pos_jepang;
            $object[$prefix . 'nama_lpk'] = $this->nama_lpk;

            // REK PENERIMA
            $object[$prefix . 'no_rekening_penerima'] = $this->no_rekening_penerima;
            $object[$prefix . 'nama_bank_penerima'] = $this->nama_bank_penerima;
            $object[$prefix . 'nama_penerima'] = $this->nama_penerima;
            $object[$prefix . 'hubungan_penerima'] = $this->hubungan_penerima;

            // Relasi History
            $object[$prefix . 'remarks_id'] = $this->remarks_id;
            $object[$prefix . 'remarks_type'] = $this->remarks_type;
            $object[$prefix . 'pic_code'] = $this->pic_code;

            // Validasi
            $object[$prefix . 'is_should_filled'] = true;
            $object[$prefix . 'is_submitted'] = true;
        }

        return $object;
    }

    public function onSubmitted()
    {
        if ($this->allGensenDetailsTarikData()) {
            logger('status tarik data');
            $this->status = self::STATUS_TARIK_DATA;
        }
        if ($this->allGensenDetailsCair()) {
            logger('status cair');
            $this->status = self::STATUS_GENSEN_CAIR;
        }
        $this->is_should_filled = $this->isShouldFilled();

        $this->save();
    }

    public function allGensenDetailsCair(): bool
    {
        return !$this->gensenFormDetails()
            ->whereNull('deleted_at')
            ->where(function ($query) {
                // A detail is considered incomplete/un-updated if:
                $query->where(function ($q) {
                    $q->where('status', GensenFormDetailStatus::PROCESS) // Still in process
                        ->orWhereNull('status')                              // FIX: Added actual NULL check for status
                        ->orWhere('status', '')                              // Status is empty string
                        ->orWhere('status', GensenFormDetailStatus::VALID);   // (See Note 2 below about VALID)
                })
                    ->whereNull('tanggal_cair')                      // OR missing date
                    ->where(function ($query) {
                        $query->whereNull('nominal_cair')                      // OR missing nominal
                            ->orWhere('nominal_cair', '<=', 0);                // OR nominal is 0
                    });
            })->exists() &&
            $this->gensenFormDetails()
            ->whereNull('deleted_at')
            ->where(function ($query) {
                $query
                    ->where(function ($q) {
                        $q->where('status', GensenFormDetailStatus::PROCESS) // Still in process
                            ->orWhereNull('status')                              // FIX: Added actual NULL check for status
                            ->orWhere('status', '')                              // Status is empty string
                            ->orWhere('status', GensenFormDetailStatus::VALID);   // (See Note 2 below about VALID)
                    });
            })->count() > 0;
    }

    public function allGensenDetailsTarikData(): bool
    {
        return !$this->gensenFormDetails()
            ->whereNull('deleted_at') // (See Note 1 below)
            ->where(function ($query) {
                $query
                    ->where(function ($q) {
                        $q->where('status', GensenFormDetailStatus::PROCESS) // Still in process
                            ->orWhereNull('status')                              // FIX: Added actual NULL check for status
                            ->orWhere('status', '')                              // Status is empty string
                            ->orWhere('status', GensenFormDetailStatus::VALID);   // (See Note 2 below about VALID)
                    })
                    ->whereNull('tanggal_tarik_data')                  // Missing date
                    ->whereNull('label');                               // Missing label
            })->exists() &&
            $this->gensenFormDetails()
            ->whereNull('deleted_at') // (See Note 1 below)
            ->where(function ($query) {
                $query
                    ->where(function ($q) {
                        $q->where('status', GensenFormDetailStatus::PROCESS) // Still in process
                            ->orWhereNull('status')                              // FIX: Added actual NULL check for status
                            ->orWhere('status', '')                              // Status is empty string
                            ->orWhere('status', GensenFormDetailStatus::VALID);   // (See Note 2 below about VALID)
                    });
            })->count() > 0;
    }

    public function isAttachmentReady($requiredTypes = false): bool
    {
        $requiredTypes = $requiredTypes ? $requiredTypes : GensenAttachmentType::cases();
        $uploadedTypes = $this->attachments
            ->where('status', '!=', GensenAttachmenStatus::STATUS_REJECTED)
            ->whereNull('deleted_at')
            ->pluck('type')
            ->map(fn($t) => $t->value)
            ->toArray();
        foreach ($requiredTypes as $type) {
            if (!in_array($type->value, $uploadedTypes)) {
                return false;
            }
        }

        return true;
    }

    public function handleMergePersyaratanGensen()
    {
        if (
            !$this->persyaratanGensenJob() || !$this->persyaratanGensenJob()
                ->where('status', '=', JobStatus::PENDING)
                ->exists()
        ) {
            PersyaratanGensenJobRepository::create([
                'gensen_form_id' => $this->id,
                'status' => JobStatus::PENDING,
            ]);
        }
    }

    public function handleMergeSeluruhBerkas()
    {
        if (
            !$this->seluruhBerkasJob() || !$this->seluruhBerkasJob()
                ->where('status', '=', JobStatus::PENDING)
                ->exists()
        ) {
            SeluruhBerkasJobRepository::create([
                'gensen_form_id' => $this->id,
                'status' => JobStatus::PENDING,
            ]);
        }
    }

    const SHOULD_FILLED = [
        'nama_lengkap',
        'email',
        'tanggal_lahir',
        'nama_instagram',
        'nama_tiktok',
        'nomor_whatsapp',
        'nomor_whatsapp_darurat',
        'alamat_jepang',
        'kode_pos_jepang',

        // REK PENERIMA
        'no_rekening_penerima',
        'nama_bank_penerima',
        'nama_penerima',
    ];

    public function isShouldFilled(): bool
    {
        return collect(self::SHOULD_FILLED)
            ->every(fn($field) => filled($this->{$field}));
    }

    public function attachmentGroups($types = null, $not_converted = true)
    {
        $attachments = $this->attachments
            ->when($not_converted, function ($q) {
                return $q->where('status', '!=', GensenAttachmenStatus::STATUS_CONVERTED);
            })
            ->when($types, function ($q) use ($types) {
                return $q->whereIn('type', $types);
            })
            ->groupBy(fn($a) => $a->type->value);

        return collect(GensenAttachmentType::cases())
            ->mapWithKeys(function ($type) use ($attachments) {

                $items = $attachments->get($type->value, collect());

                if (in_array($type, [
                    GensenAttachmentType::KARTU_KELUARGA,
                    GensenAttachmentType::KERTAS_GENSEN,
                    GensenAttachmentType::REKAP_PENGIRIMAN_UANG,
                ])) {

                    return [
                        $type->value => [
                            'type' => $type,
                            'label' => $type->label(),
                            'uploaded' => $items->isNotEmpty(),
                            'groups' => $items
                                ->groupBy('remittance_type')
                                ->map(function ($files, $provider) {
                                    return [
                                        'provider' => $provider,
                                        'files' => $files->map(fn($file) => [
                                            'id' => Crypt::encrypt($file->id),
                                            'filename' => $file->original_name,
                                            'remittance_type' => $file->remittance_type,
                                            'note' => $file->note,
                                            'path' => $file->path,
                                            'disk' => $file->disk,
                                            'convert_image' => $file->convert_image,
                                            'size' => $file->file_size,
                                            'type' => $file->type,
                                            'created_at' => $file->created_at,
                                            'printStatus' => $file?->status?->print(),
                                            'isPdf' => $file->isPdf(),
                                            'isImage' => $file->isImage(),
                                            'url' => $file->previewUrl(),
                                        ]),
                                    ];
                                })
                                ->values()
                                ->toArray(),
                        ]
                    ];
                }
                if (in_array($type, [
                    GensenAttachmentType::PERSYARATAN_PENGURUSAN_GENSEN,
                    GensenAttachmentType::SELURUH_BERKAS,
                ])) {

                    $attachment = $items->first();
                    return [
                        $type->value => [
                            'id' => $attachment?->id ? Crypt::encrypt($attachment->id) : null,
                            'type' => $type,
                            'label' => $type->label(),
                            'uploaded' => (bool) $attachment,
                            'attachment' => $attachment,
                            'filename' => $attachment?->original_name,
                            'nama_file' => $this->nama_lengkap . "." . $attachment?->extension,
                            'isJobProcess' => $attachment?->isJobProcess(),
                            'isJobProcessDone' => $attachment?->isJobProcessDone(),
                            'status' => $attachment?->status,
                            'printStatus' => $attachment?->status?->print(),
                            'size' => $attachment?->file_size,
                            'note' => $attachment?->note,
                            'created_at' => $attachment?->created_at,
                            'isPdf' => $attachment?->isPdf(),
                            'isImage' => $attachment?->isImage(),
                            'url' => $attachment?->previewUrl(),
                        ]
                    ];
                }

                $attachment = $items->first();

                return [
                    $type->value => [
                        'id' => $attachment?->id ? Crypt::encrypt($attachment->id) : null,
                        'type' => $type,
                        'label' => $type->label(),
                        'uploaded' => (bool) $attachment,
                        'attachment' => $attachment,
                        'filename' => $attachment?->original_name,
                        'status' => $attachment?->status,
                        'printStatus' => $attachment?->status?->print(),
                        'size' => $attachment?->file_size,
                        'path' => $attachment?->path,
                        'disk' => $attachment?->disk,
                        'mime_type' => $attachment?->mime_type,
                        'note' => $attachment?->note,
                        'created_at' => $attachment?->created_at,
                        'isPdf' => $attachment?->isPdf(),
                        'isImage' => $attachment?->isImage(),
                        'url' => $attachment?->previewUrl(),
                    ]
                ];
            });
    }

    // Mysql
    // public function attachments()
    // {
    //     return $this->hasMany(GensenFormAttachment::class, 'gensen_form_id', 'id')->orderByRaw(
    //         "FIELD(type, " . collect(self::ATTACHMENT_ORDER_BY)
    //             ->map(fn($v) => "'$v'")
    //             ->implode(',') . ")"
    //     );
    // }


    // PostgreSQL
    public function attachments($types = null)
    {
        $case = collect(self::ATTACHMENT_ORDER_BY)
            ->values()
            ->map(
                fn($value, $index) =>
                "WHEN '{$value}' THEN " . ($index + 1)
            )
            ->implode(' ');

        return $this->hasMany(GensenFormAttachment::class, 'gensen_form_id', 'id')
            ->when($types, function ($q) use ($types) {
                $q->whereIn('type', $types);
            })
            ->orderByRaw("CASE type {$case} ELSE 999 END");
    }
    public function attachmentsToConvert()
    {
        return $this->hasMany(GensenFormAttachment::class, 'gensen_form_id', 'id')->whereIn('type', [
            GensenAttachmentType::KERTAS_GENSEN->value,
            GensenAttachmentType::KARTU_KELUARGA->value,
            GensenAttachmentType::REKAP_PENGIRIMAN_UANG->value,
        ])
            ->where(function ($q) {
                $q->whereNull('convert_image')
                    ->orWhere('convert_image', false);
            })
            ->where('status', '!=', GensenAttachmenStatus::STATUS_CONVERTED);
    }
    public function attachmentsConvertedRekapanPengirimanUang()
    {
        return $this->hasMany(GensenFormAttachment::class, 'gensen_form_id', 'id')->where('type', GensenAttachmentType::REKAP_PENGIRIMAN_UANG);
    }
    public function attachmentsCopy()
    {
        return $this->hasMany(GensenFormAttachment::class, 'gensen_form_id', 'id')
            ->whereIn('type', [
                GensenAttachmentType::KERTAS_GENSEN,
                GensenAttachmentType::REKAP_PENGIRIMAN_UANG,
                GensenAttachmentType::KARTU_KELUARGA,
                GensenAttachmentType::ZAIRYOU_CARD_FRONT,
                GensenAttachmentType::ZAIRYOU_CARD_BACK,
                GensenAttachmentType::MY_NUMBER_FRONT,
                GensenAttachmentType::MY_NUMBER_BACK,
                GensenAttachmentType::REKENING_INDONESIA,
            ]);
    }
    public function attachmentsRollback()
    {
        return $this->hasMany(GensenFormAttachment::class, 'gensen_form_id', 'id')
            ->whereIn('type', [
                GensenAttachmentType::KERTAS_GENSEN,
                GensenAttachmentType::REKAP_PENGIRIMAN_UANG,
                GensenAttachmentType::KARTU_KELUARGA,
                GensenAttachmentType::ZAIRYOU_CARD_FRONT,
                GensenAttachmentType::ZAIRYOU_CARD_BACK,
                GensenAttachmentType::MY_NUMBER_FRONT,
                GensenAttachmentType::MY_NUMBER_BACK,
                GensenAttachmentType::REKENING_INDONESIA,
                GensenAttachmentType::PERSYARATAN_PENGURUSAN_GENSEN,
                GensenAttachmentType::SELURUH_BERKAS,
            ]);
    }

    public function remarks()
    {
        return $this->morphTo();
    }

    public function getPicAttribute()
    {
        if (!$this->remarks) {
            return null;
        }

        if ($this->remarks instanceof GensenFormLink) {
            return $this->remarks->creator;
        }

        return $this->remarks;
    }

    public function persyaratanGensenJob()
    {
        return $this->hasOne(
            PersyaratanGensenJob::class,
            'gensen_form_id', // FK in jobs table
            'id'              // PK in gensen_forms
        );
    }

    public function seluruhBerkasJob()
    {
        return $this->hasOne(
            SeluruhBerkasJob::class,
            'gensen_form_id', // FK in jobs table
            'id'              // PK in gensen_forms
        );
    }

    public function remittanceExtraction()
    {
        return $this->hasOne(RemittanceExtraction::class, 'subject_id', 'id')->where('subject_type', self::class);
    }

    public function gensenFormDetails()
    {
        return $this->hasMany(GensenFormDetail::class, 'gensen_form_id', 'id');
    }

    public function aiJobs()
    {
        return $this->hasMany(AiJob::class, 'subject_id', 'id')->where('subject_type', self::class);
    }

    public function hasPendingAiJob(): bool
    {
        return $this->aiJobs()
            ->where('status', JobStatus::PENDING)
            ->exists();
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
