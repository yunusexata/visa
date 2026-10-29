<?php

namespace App\Models\Form;

use App\Enums\Form\VisaFormLinkStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

class VisaFormLink extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        // optional password protection
        'password',

        // usage control
        'name',
        'max_usage',
        'used_count',

        // expiration
        'expired_at',

        // lifecycle state
        'status',
        'created_by',
        'pic_code',
    ];

    protected $casts = [
        'status'     => VisaFormLinkStatus::class,
        'expired_at' => 'datetime',
    ];

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_INACTIVE = 'INACTIVE';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_SUCCESS = 'SUCCESS';

    const STATUS_CHOICE = [
        self::STATUS_ACTIVE => 'ACTIVE',
        // self::STATUS_INACTIVE => 'INACTIVE',
        self::STATUS_EXPIRED => 'EXPIRED',
        self::STATUS_SUCCESS => 'SUCCESS',
    ];

    protected $guarded = ['id'];

    public function isDeletable()
    {
        return true;
    }

    public function isEditable()
    {
        return true;
    }

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->token = Str::uuid();
            $model->status = VisaFormLinkStatus::STATUS_ACTIVE;

            if ($model->created_by && !$model->pic_code) {
                $model->pic_code = $model->creator?->pic_code;
            }
        });
        self::updating(function ($model) {

            if (
                $model->isDirty('used_count') &&
                $model->used_count >= $model->max_usage
            ) {
                $model->status = VisaFormLinkStatus::STATUS_SUCCESS;
            }
            if (
                $model->isDirty('used_count') &&
                $model->used_count < $model->max_usage
            ) {
                $model->status = VisaFormLinkStatus::STATUS_ACTIVE;
            }

            if (
                $model->isDirty('max_usage') &&
                $model->used_count < $model->max_usage
            ) {
                $model->status = VisaFormLinkStatus::STATUS_ACTIVE;
            }

            if (
                $model->isDirty('max_usage') &&
                $model->used_count >= $model->max_usage
            ) {
                $model->status = VisaFormLinkStatus::STATUS_SUCCESS;
            }
        });
    }

    public function incrementUsedCount()
    {
        $this->used_count++;

        $this->save(); // ✅ trigger updating event
    }
    public function decrementUsedCount()
    {
        $this->used_count--;

        $this->save(); // ✅ trigger updating event
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function gensenForms()
    {
        return $this->hasMany(GensenForm::class, 'remarks_id', 'id')->where('remarks_type', self::class);
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
