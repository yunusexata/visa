<?php

namespace App\Mail;

use App\Models\Service\SendEmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentAvailableNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SendEmailLog $log
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->log->subject_line,
        );
    }

    public function content(): Content
    {
        logger('mail document available created');

        $data = json_decode($this->log->data, true);
        $clientName = $data['client_name'] ?? 'Klien';

        return new Content(
            view: 'emails.document_available',
            with: [
                'clientName' => $clientName,
                'dashboardUrl' => config('app.url').'/dashboard',
                'reviewUrl' => config('exata.google_review_url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
