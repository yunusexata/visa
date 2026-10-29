<?php

namespace App\Mail;

use App\Models\Service\SendEmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public SendEmailLog $log
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->log->subject_line,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        logger('mail reset password created');
        
        $data = json_decode($this->log->data, true);
        $token = $data['token'] ?? '';
        $email = $data['email'] ?? '';

        return new Content(
            view: 'app.emails.reset_password',
            with: [
                'user' => $this->log->subject,
                'token' => $token,
                'email' => $email,
                'resetUrl' => route('password.reset', ['token' => $token, 'email' => $email]),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
