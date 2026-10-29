<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistoricalDocumentDownloadsSeeder extends Seeder
{
    /**
     * Seed historical download records for PT Sumber Rezeki Exata Indonesia.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Fetch notification anchors (send_email_logs with DocumentAvailableNotification)
            $emailAnchors = DB::table('send_email_logs')
                ->select(
                    'subject_id as client_id',
                    DB::raw('MIN(COALESCE(finished_at, created_at)) as notification_sent_at')
                )
                ->where('mailable', 'like', '%DocumentAvailableNotification%')
                ->groupBy('subject_id')
                ->get()
                ->keyBy('client_id');

            // Fetch survey & review activities
            $reviewActivities = DB::table('reviews')
                ->select('client_id', DB::raw('MIN(created_at) as first_review_at'))
                ->groupBy('client_id')
                ->get()
                ->keyBy('client_id');

            $upodActivities = DB::table('upod_submissions')
                ->select('client_id', DB::raw('MIN(submitted_at) as first_upod_at'))
                ->groupBy('client_id')
                ->get()
                ->keyBy('client_id');

            // Fetch latest document batch IDs per client
            $clientBatches = DB::table('documents')
                ->select('client_id', DB::raw('MAX(batch_id) as batch_id'))
                ->whereNotNull('batch_id')
                ->groupBy('client_id')
                ->get()
                ->keyBy('client_id');

            $proofHighCount = 0;
            $notifiedOnlyCount = 0;
            $now = now();

            foreach ($emailAnchors as $clientId => $anchor) {
                // sebelum insert, cek sudah ada record untuk client tsb
                $existing = DB::table('document_downloads')
                    ->where('client_id', $clientId)
                    ->whereIn('source', ['proof_high', 'notified_only', 'infra_log'])
                    ->exists();
                if ($existing) {
                    continue; // skip — sudah ter-seed
                }

                $notifSentAt = $anchor->notification_sent_at;
                $batchId = $clientBatches->has($clientId) ? $clientBatches[$clientId]->batch_id : null;

                $reviewAt = $reviewActivities->has($clientId) ? $reviewActivities[$clientId]->first_review_at : null;
                $upodAt = $upodActivities->has($clientId) ? $upodActivities[$clientId]->first_upod_at : null;

                // Pick earliest survey/review timestamp
                $surveyReviewAt = null;
                if ($reviewAt && $upodAt) {
                    $surveyReviewAt = min($reviewAt, $upodAt);
                } elseif ($reviewAt) {
                    $surveyReviewAt = $reviewAt;
                } elseif ($upodAt) {
                    $surveyReviewAt = $upodAt;
                }

                // Check if survey/review was submitted post-notification
                if ($surveyReviewAt && $surveyReviewAt >= $notifSentAt) {
                    DB::table('document_downloads')->insert([
                        'client_id' => $clientId,
                        'document_id' => null,
                        'batch_id' => $batchId,
                        'ip_address' => null,
                        'user_agent' => null,
                        'downloaded_at' => $surveyReviewAt,
                        'source' => 'proof_high',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $proofHighCount++;
                } else {
                    DB::table('document_downloads')->insert([
                        'client_id' => $clientId,
                        'document_id' => null,
                        'batch_id' => $batchId,
                        'ip_address' => null,
                        'user_agent' => null,
                        'downloaded_at' => $notifSentAt,
                        'source' => 'notified_only',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $notifiedOnlyCount++;
                }
            }

            $totalInserted = $proofHighCount + $notifiedOnlyCount;
            $this->command->info('Seeding completed successfully.');
            $this->command->info("Inserted 'proof_high': {$proofHighCount}");
            $this->command->info("Inserted 'notified_only': {$notifiedOnlyCount}");
            $this->command->info("Total rows inserted: {$totalInserted}");
        });
    }
}
