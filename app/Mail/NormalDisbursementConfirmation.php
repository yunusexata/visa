<?php

namespace App\Mail;

use App\Models\Service\SendEmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NormalDisbursementConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SendEmailLog $log
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->log->subject_line ?? '✅ Konfirmasi Pencairan Nenkin — Proses Kamu Sedang Berjalan',
        );
    }

    public function content(): Content
    {
        $data = json_decode($this->log->data, true);
        $clientName = $data['client_name'] ?? 'Klien';

        $waRaw = config('exata.admin_wa_number', '6281199896308');
        // Format as +62-811-9989-6308
        if (strlen($waRaw) === 13 && str_starts_with($waRaw, '62')) {
            $formattedWa = '+'.substr($waRaw, 0, 2).'-'.substr($waRaw, 2, 3).'-'.substr($waRaw, 5, 4).'-'.substr($waRaw, 9);
        } else {
            $formattedWa = '+'.$waRaw;
        }

        return new Content(
            view: 'emails.normal_disbursement_confirmation',
            with: [
                'clientName' => $clientName,
                'dashboardUrl' => config('app.url').'/dashboard',
                'whatsappUrl' => 'https://wa.me/'.$waRaw,
                'formattedWa' => $formattedWa,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
