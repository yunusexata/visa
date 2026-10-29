<?php

namespace App\Models;

use App\Models\Client\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

class DocumentUploadFailure extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'batch_id',
        'original_filename',
        'folder_type',
        'failure_reason',
        'best_match_name',
        'best_match_score',
        'resolved_client_id',
        'resolved_at',
    ];

    protected $casts = [
        'best_match_score' => 'decimal:3',
        'resolved_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(DocumentUploadBatch::class, 'batch_id');
    }

    public function resolvedClient()
    {
        return $this->belongsTo(Client::class, 'resolved_client_id');
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
