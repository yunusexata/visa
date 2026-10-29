<?php

namespace App\Models\Client;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

class ClientMagicLink extends Model
{
    // php artisan reverb:start
    use HasFactory, HasTrackHistory, SoftDeletes;

    protected $fillable = [

        'client_id',
        'token',

        'email',
        'password',

        'used_at',

        'expires_at',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::created(function ($model) {
            $vlaidatedData = [];
        });
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
}
