<?php

use App\Mail\EsignReminderNotification;
use App\Mail\NormalDisbursementConfirmation;
use App\Mail\SpeedServiceConfirmation;
use App\Models\Service\SendEmailLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('email:smoke-test', function () {
    $recipients = [
        'yunusexata@gmail.com' => 'Yunus Exata',
        // 'adam.zibran.co@gmail.com' => 'Adam Zibran',
    ];

    foreach ($recipients as $email => $name) {
        $this->info("Sending emails to {$name} <{$email}>...");

        // 1. Esign Reminder
        $log1 = new SendEmailLog;
        $log1->email = $email;
        $log1->mailable = EsignReminderNotification::class;
        $log1->data = json_encode([
            'client_name' => $name,
        ]);
        $log1->subject_line = '⚡ Tanda Tangan Dibutuhkan — Proses Speed Service Kamu Menunggu';

        $this->info('Sending Esign Reminder...');
        Mail::to($email)->send(new EsignReminderNotification($log1));

        // 2. Normal Disbursement Confirmation
        $log2 = new SendEmailLog;
        $log2->email = $email;
        $log2->mailable = NormalDisbursementConfirmation::class;
        $log2->data = json_encode([
            'client_name' => $name,
        ]);
        $log2->subject_line = '✅ Konfirmasi Pencairan Nenkin — Proses Kamu Sedang Berjalan';

        $this->info('Sending Normal Disbursement Confirmation...');
        Mail::to($email)->send(new NormalDisbursementConfirmation($log2));

        // 3. Speed Service Confirmation
        $log3 = new SendEmailLog;
        $log3->email = $email;
        $log3->mailable = SpeedServiceConfirmation::class;
        $log3->data = json_encode([
            'client_name' => $name,
            'signed_at' => now()->toIso8601String(),
        ]);
        $log3->subject_line = '🎉 Tanda Tangan Diterima — Speed Service Kamu Resmi Aktif!';

        $this->info('Sending Speed Service Confirmation...');
        Mail::to($email)->send(new SpeedServiceConfirmation($log3));

        $this->info("Successfully sent all 3 templates to {$email}!\n");
    }

    $this->comment('Smoke test completed!');
})->purpose('Send smoke test emails to specified test users');
