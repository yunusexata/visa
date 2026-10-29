<?php

namespace App\Models\Client;

use App\Enums\Gensen\EmailLogStatus;
use App\Models\User;
use App\Repositories\Service\SendEmailLogRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

class ClientConfirmation extends Model
{
    // php artisan reverb:start
    use HasFactory, HasTrackHistory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'nenkin_80_received_type',
        'current_position',
        'back_to_jp_type',
        'departure_type',
        'departure_month',
        'departure_year',
        'estimated_return_month',
        'estimated_return_year',
        'indonesia_address',
        'nik',
        'current_phone',
        'claim_type',

        'client_closing_history_id',
        'client_closing_source_file_name',
        'client_closing_batch_number',
        'closed_at',
    ];

    protected $guarded = ['id'];

    const NENKIN_80_RECEIVED_TYPE_RECEIVED = 'received';

    const NENKIN_80_RECEIVED_TYPE_NOT_RECEIVED = 'not_received';

    const NENKIN_80_RECEIVED_TYPE_NOT_CHECKED = 'not_checked';

    const NENKIN_80_RECEIVED_TYPE_CHOICE = [
        self::NENKIN_80_RECEIVED_TYPE_RECEIVED => 'Sudah Diterima',
        self::NENKIN_80_RECEIVED_TYPE_NOT_RECEIVED => 'Belum Diterima',
        self::NENKIN_80_RECEIVED_TYPE_NOT_CHECKED => 'Belum Di Cek',
    ];

    const CURRENT_POSITION_INDONESIA = 'indonesia';

    const CURRENT_POSITION_JEPANG = 'jepang';

    const CURRENT_POSITION_CHOICE = [
        self::CURRENT_POSITION_INDONESIA => 'Indonesia',
        self::CURRENT_POSITION_JEPANG => 'Jepang',
    ];

    const BACK_TO_JP_TYPE_YES = 'yes';

    const BACK_TO_JP_TYPE_NO = 'no';

    const BACK_TO_JP_TYPE_CHOICE = [
        self::BACK_TO_JP_TYPE_YES => 'Ya',
        self::BACK_TO_JP_TYPE_NO => 'Tidak',
    ];

    protected static function onBoot()
    {
        self::created(function ($model) {
            // if ($model->client) {
            //     $model->client->update(['claim_type' => $model->claim_type]);
            // }
        });

        self::updated(function ($model) {
            if ($model->client) {
                $model->client->update(['claim_type' => $model->claim_type]);
            }
        });

        self::creating(function ($model) {
            if (
                $model->current_position === ClientConfirmation::CURRENT_POSITION_INDONESIA &&
                $model->back_to_jp_type === ClientConfirmation::BACK_TO_JP_TYPE_YES
            ) {
                $departure = Carbon::createFromFormat('Y-m', $model->departure_year.'-'.$model->departure_month);
                $is_less_then_7_month = $departure->greaterThanOrEqualTo(now()->addMonths(6));
                if (
                    $is_less_then_7_month
                ) {
                    // if ($model->client) {
                    // $model->client()->update([
                    //     'claim_type' => Client::CLAIM_TYPE_ADJUSTMENT
                    // ]);
                    $model->claim_type = Client::CLAIM_TYPE_ADJUSTMENT;
                    // }
                    // Alert::information($model, 'ADJUSMENT');
                }
                if (
                    ! $is_less_then_7_month
                ) {
                    // if ($model->client) {
                    // $model->client()->update([
                    //     'claim_type' => Client::CLAIM_TYPE_NORMAL
                    // ]);

                    $model->claim_type = Client::CLAIM_TYPE_NORMAL;
                    // }
                    // Alert::information($model, 'NORMAL');
                }
            }

            if (
                $model->current_position === ClientConfirmation::CURRENT_POSITION_INDONESIA &&
                $model->back_to_jp_type === ClientConfirmation::BACK_TO_JP_TYPE_NO
            ) {
                // if ($model->client) {
                // $model->client()->update([
                //     'claim_type' => Client::CLAIM_TYPE_SPEED
                // ]);

                $model->claim_type = Client::CLAIM_TYPE_SPEED;
                // }
                // Alert::information($model, 'SPEED');
            }
            if (
                $model->current_position === ClientConfirmation::CURRENT_POSITION_JEPANG
            ) {
                // if ($model->client) {
                // $model->client()->update([
                //     'claim_type' => Client::CLAIM_TYPE_NORMAL
                // ]);

                $model->claim_type = Client::CLAIM_TYPE_NORMAL;
                // }
                // Alert::information($model, 'SPEED');
            }
        });

        // self::created(function ($model) {
        //     SendEmailLogRepository::create(
        //         [
        //             'subject_type' => self::class,
        //             'subject_id' => $model->id,
        //             'data' => json_encode([], true),
        //             'email' => $model->email,
        //             'mailable' => ClientSubmitConfirmation::class,
        //             'subject_line' => "Pembuatan Akun Berhasil",
        //             'status' => EmailLogStatus::PENDING,
        //             'queued_at' => now(),
        //         ]
        //     );
        // });
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
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

    public function closing()
    {
        return $this->belongsTo(ClientClosingHistory::class, 'client_closing_history_id', 'id');
    }
}
