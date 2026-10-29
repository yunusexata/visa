<?php

namespace App\Jobs;

use App\Enums\Service\EmailLogStatus;
use App\Models\Service\SendEmailLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(public SendEmailLog $log)
    {
        $this->onQueue('email');
    }

    public function handle()
    {
        logger('email sending');
        $this->log->update([
            'status' => EmailLogStatus::SENDING,
            'started_at' => now(),
            'attempts' => $this->log->attempts + 1,
        ]);

        try {

            logger(['email process', $this->log->email]);
            Mail::to($this->log->email)->send(new $this->log->mailable($this->log));

            $this->log->update([
                'status' => EmailLogStatus::SENT,
                'finished_at' => now(),
            ]);
        } catch (Throwable $e) {

            $this->log->update([
                'status' => EmailLogStatus::FAILED,
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            throw $e;
        }
    }

    public function failed(?Throwable $e): void
    {
        $this->log->update([
            'status' => EmailLogStatus::FAILED,
            'finished_at' => now(),
            'error_message' => $e->getMessage(),
        ]);
    }
}
