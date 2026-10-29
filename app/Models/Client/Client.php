<?php

namespace App\Models\Client;

use App\Models\User;
use App\Repositories\Client\ClientMagicLinkRepository;
use App\Traits\Models\LowercaseAttributes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

class Client extends Model
{
    // php artisan reverb:start
    use HasFactory, SoftDeletes, HasTrackHistory, LowercaseAttributes;

    protected $fillable = [

        'no_jp',
        'name',
        'name_normalized',
        'dob',
        'phone',
        'claim_type',
        'status',
        'raw_data',
        'source_file_name',
        'source_file_disk',
        'source_file_path',
        'imported_at',
        'facebook',
        'instagram',
        'pic',
        'lpk',
        'note',
        'is_exported',
        'exported_at',
        'upod_type',
    ];

    protected $guarded = ['id'];

    protected array $lowercase = [
        'pic',
    ];
    protected array $uppercase = [
        'name',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETE = 'complete';

    // const CLAIM_TYPE_RE_ENTRY = 're_entry';
    const CLAIM_TYPE_ADJUSTMENT = 'adjusment';
    const CLAIM_TYPE_NORMAL = 'normal';
    const CLAIM_TYPE_SPEED = 'speed';
    const CLAIM_TYPE_PENDING = 'pending';
    const CLAIM_TYPE_KOKUMIN = 'kokumin';

    const CLAIM_TYPE_CHOICE = [
        self::CLAIM_TYPE_NORMAL => 'NORMAL',
        self::CLAIM_TYPE_PENDING => 'PENDING',
        self::CLAIM_TYPE_ADJUSTMENT => 'ADJUSMENT',
        self::CLAIM_TYPE_SPEED => 'SPEED',
        self::CLAIM_TYPE_KOKUMIN => 'KOKUMIN',
    ];

    const FILTER_DATA_TYPE_CONFIRMATION = 'confirmation';
    const FILTER_DATA_TYPE_NOT_CONFIRMATION = 'not_confirmation';
    const FILTER_DATA_TYPE_REGISTERED = 'registered';
    const FILTER_DATA_TYPE_NOT_REGISTERED = 'not_registered';

    const FILTER_DATA_TYPE_CHOICE = [
        self::FILTER_DATA_TYPE_CONFIRMATION => 'Sudah Konfirmasi',
        self::FILTER_DATA_TYPE_NOT_CONFIRMATION => 'Belum Konfirmasi',
        self::FILTER_DATA_TYPE_REGISTERED => 'Sudah Buat Akun',
        self::FILTER_DATA_TYPE_NOT_REGISTERED => 'Belum Buat Akun',
    ];

    protected static function onBoot()
    {
        self::created(function ($model) {
            $vlaidatedData = [
                'client_id' => $model->id,
                'token' => Str::uuid(),
                'expires_at' => Carbon::now()->addDays(7),
            ];

            ClientMagicLinkRepository::create($vlaidatedData);
        });
        self::creating(function ($model) {
            $model->upod_type = User::query()
                ->where('pic', $model->pic)
                ->role(User::ROLE_SALES)
                ->value('upod_type');
        });
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

    public function agentPic()
    {
        return $this->belongsTo(User::class, 'pic', 'pic')->role(User::ROLE_SALES);
    }
    public function magicLink()
    {
        return $this->hasOne(ClientMagicLink::class);
    }
    public function historyMagicLink()
    {
        return $this->hasMany(ClientMagicLink::class)->latestOfMany();
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
    public function confirmation()
    {
        return $this->hasOne(ClientConfirmation::class);
    }
    public function esign()
    {
        return $this->hasOne(ClientEsign::class);
    }
    public function review()
    {
        return $this->hasOne(\App\Models\Review::class);
    }
    public function documents()
    {
        return $this->hasMany(\App\Models\Document::class);
    }
    public function upodSubmission()
    {
        return $this->hasOne(\App\Models\UpodSubmission::class);
    }
    public function upodSurveyResponse()
    {
        return $this->hasOne(\App\Models\UpodSurveyResponse::class);
    }
    public function downloads()
    {
        return $this->hasMany(\App\Models\DocumentDownload::class, 'client_id');
    }
    public function liveDownloads()
    {
        return $this->downloads()->where('source', 'live_tracked');
    }
    public function resolvedUpodType(?array $picMappings = null): string
    {
        $type = null;

        // 1. Priority: agentPic->upod_type
        if ($this->agentPic?->upod_type) {
            $type = $this->agentPic->upod_type;
        } elseif ($this->upod_type) {
            $type = $this->upod_type;
        } elseif ($this->pic) {
            $picKey = strtolower($this->pic);
            if ($picMappings && array_key_exists($picKey, $picMappings)) {
                $type = $picMappings[$picKey] ?? 'google_review';
            } else {
                $sales = User::query()
                    ->where('pic', $picKey)
                    ->role(User::ROLE_SALES)
                    ->first();
                if ($sales && $sales->upod_type) {
                    $type = $sales->upod_type;
                }
            }
        }

        if (!$type) {
            $type = 'google_review';
        }

        // Normalize values to either 'google_review' or 'satisfaction_survey'
        if ($type === 'google' || $type === 'google_review') {
            return 'google_review';
        }

        if ($type === 'riset' || $type === 'satisfaction_survey') {
            return 'satisfaction_survey';
        }

        return 'google_review';
    }
}
