<?php

namespace App\Models;

use App\Models\Client\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'document_id',
        'batch_id',
        'ip_address',
        'user_agent',
        'downloaded_at',
        'source',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
