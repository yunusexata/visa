<?php

namespace App\Mail;

use App\Models\Client\Client;
use App\Models\Service\SendEmailLog;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SpeedServiceConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $estimatedDate;

    public function __construct(
        public SendEmailLog $log
    ) {
        $esign = $this->log->subject;
        $signedAt = null;
        if ($esign && isset($esign->signed_at)) {
            $signedAt = $esign->signed_at;
        } else {
            $data = json_decode($this->log->data, true);
            $signedAt = isset($data['signed_at']) ? Carbon::parse($data['signed_at']) : now();
        }
        $this->estimatedDate = Carbon::parse($signedAt)->addDays(14);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->log->subject_line ?? '🎉 Tanda Tangan Diterima — Speed Service Kamu Resmi Aktif!',
        );
    }

    public function content(): Content
    {
        $data = json_decode($this->log->data, true);
        $clientName = $data['client_name'] ?? 'Klien';

        $esign = $this->log->subject;
        $signedAt = null;
        if ($esign && isset($esign->signed_at)) {
            $signedAt = $esign->signed_at;
        } else {
            $signedAt = isset($data['signed_at']) ? Carbon::parse($data['signed_at']) : now();
        }

        $signedAtFormatted = Carbon::parse($signedAt)->locale('id')->translatedFormat('d F Y, H:i');
        $estimatedDateFormatted = Carbon::parse($this->estimatedDate)->locale('id')->translatedFormat('d F Y');

        $waRaw = config('exata.admin_wa_number', '6281199896308');
        // Format as +62-811-9989-6308
        if (strlen($waRaw) === 13 && str_starts_with($waRaw, '62')) {
            $formattedWa = '+'.substr($waRaw, 0, 2).'-'.substr($waRaw, 2, 3).'-'.substr($waRaw, 5, 4).'-'.substr($waRaw, 9);
        } else {
            $formattedWa = '+'.$waRaw;
        }

        $client = $esign ? $esign->client : null;
        $serviceTypeLabel = 'Speed Service';
        if ($client) {
            if ($client->claim_type === Client::CLAIM_TYPE_SPEED) {
                $serviceTypeLabel = 'Speed Service';
            } elseif ($client->claim_type === Client::CLAIM_TYPE_ADJUSTMENT) {
                $serviceTypeLabel = 'Klaim Adjustment';
            } else {
                $serviceTypeLabel = 'Klaim Normal';
            }
        }

        return new Content(
            text: 'emails.esign_confirmation_plain',
            with: [
                'clientName' => $clientName,
                'signedAtFormatted' => $signedAtFormatted,
                'estimatedDateFormatted' => $estimatedDateFormatted,
                'dashboardUrl' => config('app.url').'/client/dashboard',
                'whatsappUrl' => 'https://wa.me/'.$waRaw,
                'formattedWa' => $formattedWa,
                'client' => $client,
                'signedAt' => $signedAt,
                'estimatedTransferDate' => $this->estimatedDate,
                'serviceTypeLabel' => $serviceTypeLabel,
            ],
        );
    }

    public function attachments(): array
    {
        $esign = $this->log->subject;
        if ($esign && $esign->path) {
            try {
                if (Storage::disk('local')->exists($esign->path)) {
                    $fullPath = Storage::disk('local')->path($esign->path);

                    return [
                        Attachment::fromPath($fullPath)
                            ->as('Dokumen_Perjanjian_Exata_Indonesia.pdf')
                            ->withMime('application/pdf'),
                    ];
                } else {
                    logger()->error('Attachment file not found on local disk', ['path' => $esign->path]);
                    // Flag the missing file in SendEmailLog
                    $this->log->update([
                        'error_message' => trim(($this->log->error_message ?? '').' | Attachment file not found: '.$esign->path, ' | '),
                    ]);
                }
            } catch (\Throwable $e) {
                logger()->error('Failed to attach file to email', [
                    'exception' => $e->getMessage(),
                    'path' => $esign->path,
                ]);
                $this->log->update([
                    'error_message' => trim(($this->log->error_message ?? '').' | Failed to attach: '.$e->getMessage(), ' | '),
                ]);
            }
        }

        return [];
    }
}
