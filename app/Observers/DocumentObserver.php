<?php

namespace App\Observers;

use App\Models\Document;
use App\Models\Service\SendEmailLog;
use App\Repositories\Service\SendEmailLogRepository;
use App\Mail\DocumentAvailableNotification;
use App\Enums\Service\EmailLogStatus;
use App\Models\User;
use App\Models\Client\Client;

class DocumentObserver
{
    public function created(Document $document): void
    {
        if ($document->client) {
            self::notifyIfComplete($document->client);
        }
    }

    public function updated(Document $document): void
    {
        if (($document->isDirty('bucket_path') || $document->isDirty('original_filename')) && $document->client) {
            self::notifyIfComplete($document->client);
        }
    }

    public static function notifyIfComplete(Client $client): void
    {
        // We only notify the client when all 3 required documents are available
        $documentTypes = $client->documents()->pluck('type')->toArray();
        $requiredTypes = ['ichijikin', 'resi', 'kwitansi'];
        foreach ($requiredTypes as $type) {
            if (!in_array($type, $documentTypes)) {
                return;
            }
        }

        $user = User::where('client_id', $client->id)->first();
        if (!$user || !$user->email) {
            logger()->warning("Cannot notify client of document: client user or email not found", [
                'client_id' => $client->id,
            ]);
            return;
        }

        // Use Client class and client ID as the subject to prevent duplicate logs/emails
        $exists = SendEmailLog::where('subject_type', Client::class)
            ->where('subject_id', $client->id)
            ->where('mailable', DocumentAvailableNotification::class)
            ->whereIn('status', [
                EmailLogStatus::PENDING,
                EmailLogStatus::SENDING,
                EmailLogStatus::SENT
            ])
            ->exists();

        if ($exists) {
            return;
        }

        SendEmailLogRepository::create([
            'subject_type' => Client::class,
            'subject_id' => $client->id,
            'data' => json_encode([
                'client_name' => $client->name,
            ]),
            'email' => $user->email,
            'mailable' => DocumentAvailableNotification::class,
            'subject_line' => " Selamat Nenkin 20% Kamu Sudah Cair!",
            'status' => EmailLogStatus::PENDING,
        ]);
    }
}
