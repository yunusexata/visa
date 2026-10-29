<?php

namespace App\Observers;

use App\Enums\Service\EmailLogStatus;
use App\Mail\SpeedServiceConfirmation;
use App\Models\Client\Client;
use App\Models\Client\ClientEsign;
use App\Models\Service\SendEmailLog;
use App\Models\User;
use App\Repositories\Service\SendEmailLogRepository;

class EsignObserver
{
    public function created(ClientEsign $esign): void
    {
        $this->notifyClient($esign);
    }

    public function updated(ClientEsign $esign): void
    {
        if ($esign->isDirty('signed_at') && $esign->signed_at !== null) {
            $this->notifyClient($esign);
        }
    }

    protected function notifyClient(ClientEsign $esign): void
    {
        if ($esign->signed_at === null) {
            return;
        }

        $client = $esign->client;
        if (! $client) {
            return;
        }

        $claimType = $client->claim_type;
        if (! in_array($claimType, [Client::CLAIM_TYPE_SPEED, Client::CLAIM_TYPE_ADJUSTMENT])) {
            return;
        }

        $user = User::where('client_id', $client->id)->first();
        if (! $user || ! $user->email) {
            logger()->warning('Cannot notify client of esign: client user or email not found', [
                'client_id' => $client->id,
            ]);

            return;
        }

        // Prevent duplicate email confirmation C
        $exists = SendEmailLog::where('subject_type', ClientEsign::class)
            ->where('subject_id', $esign->id)
            ->where('mailable', SpeedServiceConfirmation::class)
            ->whereIn('status', [
                EmailLogStatus::PENDING,
                EmailLogStatus::SENDING,
                EmailLogStatus::SENT,
            ])
            ->exists();

        if ($exists) {
            return;
        }

        SendEmailLogRepository::create([
            'subject_type' => ClientEsign::class,
            'subject_id' => $esign->id,
            'data' => json_encode([
                'client_name' => $client->name,
                'signed_at' => $esign->signed_at->toIso8601String(),
            ]),
            'email' => $user->email,
            'mailable' => SpeedServiceConfirmation::class,
            'subject_line' => '🎉 Tanda Tangan Diterima — Speed Service Kamu Resmi Aktif!',
            'status' => EmailLogStatus::PENDING,
        ]);
    }
}
