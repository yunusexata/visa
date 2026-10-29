<?php

namespace App\Models\Service;

use App\Enums\Service\EmailLogStatus;
use App\Jobs\SendEmailJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

class SendEmailLog extends Model
{
    // php artisan reverb:start
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [

        // polymorphic relation
        'subject_id',
        'subject_type',
        'data',

        // recipient
        'email',

        // mail metadata
        'mailable',
        'view',
        'subject_line',

        // lifecycle
        'status',

        // monitoring
        'attempts',
        'error_message',

        // provider tracking (VERY IMPORTANT)
        'provider',
        'provider_message_id',

        // timing
        'started_at',
        'finished_at',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'status' => EmailLogStatus::class,
    ];

    protected static function onBoot()
    {
        self::created(function ($model) {
            SendEmailJob::dispatch($model);
        });
    }
    public function subject()
    {
        return $this->morphTo();
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
