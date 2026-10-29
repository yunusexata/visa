<?php

namespace App\Models;

use App\Enums\Service\EmailLogStatus;
use App\Mail\ResetPasswordMail;
use App\Models\Client\Client;
use App\Repositories\Service\SendEmailLogRepository;
use App\Traits\Models\LowercaseAttributes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, HasTrackHistory, LowercaseAttributes, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'no_whatsapp',
        'form_password',
        'email_verified_at',
        'is_active',
    ];

    const ROLE_SUPERADMIN = 'SUPERADMIN';

    const ROLE_ADMIN = 'ADMIN';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function onBoot() {}

    public function sendPasswordResetNotification($token)
    {
        SendEmailLogRepository::create([
            'subject_type' => self::class,
            'subject_id' => $this->id,
            'data' => json_encode([
                'token' => $token,
                'email' => $this->email,
            ], true),
            'email' => $this->email,
            'mailable' => ResetPasswordMail::class,
            'subject_line' => 'Reset Password Anda',
            'status' => EmailLogStatus::PENDING,
        ]);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
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
