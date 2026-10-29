<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

class Regency extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $table = 'regencies';

    protected $fillable = [
        'name',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
