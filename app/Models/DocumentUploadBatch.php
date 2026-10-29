<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Muhammadyunus1072\TrackHistory\HasTrackHistory;

class DocumentUploadBatch extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $table = 'document_upload_batchs';

    protected $fillable = [
        'uploaded_by',
        'laravel_batch_id',
        'total_files',
        'matched_files',
        'failed_files',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'batch_id');
    }

    public function failures()
    {
        return $this->hasMany(DocumentUploadFailure::class, 'batch_id');
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
