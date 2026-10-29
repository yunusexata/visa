<?php

namespace App\Observers;

use App\Models\Client\ClientConfirmation;
use App\Models\Service\SendEmailLog;
use App\Repositories\Service\SendEmailLogRepository;
use App\Mail\EsignReminderNotification;
use App\Mail\NormalDisbursementConfirmation;
use App\Enums\Service\EmailLogStatus;
use App\Models\User;
use App\Models\Client\Client;

class ClientConfirmationObserver
{
    public function created(ClientConfirmation $confirmation): void
    {
        $this->notifyClient($confirmation);
    }

    protected function notifyClient(ClientConfirmation $confirmation): void
    {
        $client = $confirmation->client;
        if (!$client) {
            return;
        }

        $user = User::where('client_id', $client->id)->first();
        if (!$user || !$user->email) {
            logger()->warning("Cannot notify client of confirmation: client user or email not found", [
                'client_id' => $client->id,
            ]);
            return;
        }

        $claimType = $confirmation->claim_type;

        if ($claimType === Client::CLAIM_TYPE_NORMAL) {
            $exists = SendEmailLog::where('subject_type', ClientConfirmation::class)
                ->where('subject_id', $confirmation->id)
                ->where('mailable', NormalDisbursementConfirmation::class)
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
                'subject_type' => ClientConfirmation::class,
                'subject_id' => $confirmation->id,
                'data' => json_encode([
                    'client_name' => $client->name,
                    'claim_type' => $confirmation->claim_type,
                ]),
                'email' => $user->email,
                'mailable' => NormalDisbursementConfirmation::class,
                'subject_line' => "Konfirmasi Pencairan Nenkin — Proses Pencairan Kamu Sedang Berjalan",
                'status' => EmailLogStatus::PENDING,
            ]);
        }

        if ($claimType === Client::CLAIM_TYPE_SPEED || $claimType === Client::CLAIM_TYPE_ADJUSTMENT) {
            logger('CLAIM TYPE OBSERVER : ' . $claimType);
            $exists = SendEmailLog::where('subject_type', ClientConfirmation::class)
                ->where('subject_id', $confirmation->id)
                ->where('mailable', EsignReminderNotification::class)
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
                'subject_type' => ClientConfirmation::class,
                'subject_id' => $confirmation->id,
                'data' => json_encode([
                    'client_name' => $client->name,
                    'claim_type' => $confirmation->claim_type,
                ]),
                'email' => $user->email,
                'mailable' => EsignReminderNotification::class,
                'subject_line' => "Tanda Tangan Dibutuhkan — Proses Nenkin Speed Service Kamu Menunggu",
                'status' => EmailLogStatus::PENDING,
            ]);
        }
    }
}
