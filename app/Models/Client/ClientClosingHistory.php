<?php

namespace App\Models\Client;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

class ClientClosingHistory extends Model
{
    // php artisan reverb:start
    use HasFactory, HasTrackHistory, SoftDeletes;

    protected $fillable = [

        'source_file_name',
        'batch_number',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        static::creating(function (ClientClosingHistory $model) {

            $nextBatchNumber = self::query()
                ->where('source_file_name', $model->source_file_name)
                ->max('batch_number');

            $model->batch_number = ($nextBatchNumber ?? 0) + 1;
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
}
