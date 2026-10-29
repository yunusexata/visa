<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\DocumentUploadBatch;
use App\Models\DocumentUploadFailure;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessDocumentChunk implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @param  array  $filesData  Array of file results to process in this chunk
     */
    public function __construct(
        protected int $uploadBatchId,
        protected array $filesData
    ) {
        $this->onQueue('upload');
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Jika batch dibatalkan di tengah jalan, hentikan pemrosesan chunk ini
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        $batch = DocumentUploadBatch::find($this->uploadBatchId);
        if (! $batch) {
            return;
        }

        foreach ($this->filesData as $file) {
            $tempPath = $file['temp_path'];
            $originalName = $file['original_name'];
            $type = $file['type'];
            $isMatched = $file['matched'];

            // Jika file tidak dicocokkan (unmatched)
            if (! $isMatched) {
                $bestMatchName = $file['best_match_name'] ?? null;
                $bestMatchScore = $file['best_match_score'] ?? null;
                $reason = 'Nama tidak cocok. Kecocokan tertinggi: "'.($bestMatchName ?? 'N/A').'" dengan skor '.number_format($bestMatchScore ?? 0.0, 3).' < 0.85';

                DocumentUploadFailure::create([
                    'batch_id' => $this->uploadBatchId,
                    'original_filename' => $originalName,
                    'folder_type' => $type,
                    'failure_reason' => $reason,
                    'best_match_name' => $bestMatchName,
                    'best_match_score' => $bestMatchScore,
                    'resolved_client_id' => null,
                    'resolved_at' => null,
                ]);

                // Hapus file temp lokal
                if (Storage::disk('local')->exists($tempPath)) {
                    Storage::disk('local')->delete($tempPath);
                }

                continue;
            }

            // Memproses file yang cocok (matched)
            $clientId = $file['client_id'];
            $confidence = $file['confidence'] ?? 1.000;
            $targetPath = "documents/{$clientId}/{$type}/{$originalName}";

            // 0. Cek apakah berkas ini sudah sukses diproses pada percobaan sebelumnya (Idempotency check awal)
            $existingDoc = Document::where('client_id', $clientId)
                ->where('type', $type)
                ->first();

            if ($existingDoc && $existingDoc->batch_id === $this->uploadBatchId) {
                if ($existingDoc->original_filename === $originalName) {
                    // Kasus A: Sudah sukses diproses sebelumnya, lalu job mengalami crash/retry.
                    // Hapus file temp lokal secara defensif jika masih ada untuk menghindari file sampah
                    if (Storage::disk('local')->exists($tempPath)) {
                        Storage::disk('local')->delete($tempPath);
                    }

                    continue;
                } else {
                    // Kasus D: Konflik internal batch (Batch sama tapi nama file beda) -> Duplikasi
                    // Jangan timpa file pertama, catat kegagalan jika belum dicatat
                    $alreadyLogged = DocumentUploadFailure::where('batch_id', $this->uploadBatchId)
                        ->where('original_filename', $originalName)
                        ->where('failure_reason', 'duplicate_in_batch')
                        ->exists();

                    if (! $alreadyLogged) {
                        DocumentUploadFailure::create([
                            'batch_id' => $this->uploadBatchId,
                            'original_filename' => $originalName,
                            'folder_type' => $type,
                            'failure_reason' => 'duplicate_in_batch',
                            'best_match_name' => null,
                            'best_match_score' => null,
                            'resolved_client_id' => null,
                            'resolved_at' => null,
                        ]);
                    }

                    // Hapus file temp lokal karena ini adalah kegagalan permanen yang tidak perlu di-retry
                    if (Storage::disk('local')->exists($tempPath)) {
                        Storage::disk('local')->delete($tempPath);
                    }

                    continue;
                }
            }

            // 1. Cek keberadaan file temp lokal
            if (! Storage::disk('local')->exists($tempPath)) {
                DocumentUploadFailure::create([
                    'batch_id' => $this->uploadBatchId,
                    'original_filename' => $originalName,
                    'folder_type' => $type,
                    'failure_reason' => 'file_lost', // Penanda khusus jika file lokal hilang (ephemeral disk restart)
                    'best_match_name' => null,
                    'best_match_score' => null,
                    'resolved_client_id' => null,
                    'resolved_at' => null,
                ]);

                continue;
            }

            // 2. Upload file ke S3/Supabase Storage (di luar DB transaction)
            try {
                $fileContents = Storage::disk('local')->get($tempPath);
                $uploadSuccess = Storage::disk('supabase')->put($targetPath, $fileContents);
                if (! $uploadSuccess) {
                    throw new Exception('Gagal mengunggah file ke Supabase Storage (S3 return false).');
                }
            } catch (Throwable $uploadEx) {
                // Catat kegagalan upload S3 (file temp lokal tetap dipertahankan untuk retry)
                logger()->error("Gagal mengunggah file {$originalName} ke S3: ".$uploadEx->getMessage());
                DocumentUploadFailure::create([
                    'batch_id' => $this->uploadBatchId,
                    'original_filename' => $originalName,
                    'folder_type' => $type,
                    'failure_reason' => 'Gagal upload S3: '.$uploadEx->getMessage(),
                    'best_match_name' => null,
                    'best_match_score' => null,
                    'resolved_client_id' => null,
                    'resolved_at' => null,
                ]);

                continue;
            }

            // 3. Database insert/update record Document (DB transaction per-file)
            $dbSuccess = false;
            try {
                Document::withoutEvents(function () use ($clientId, $type, $originalName, $targetPath, $confidence, $batch, $existingDoc, &$dbSuccess) {
                    DB::transaction(function () use ($clientId, $type, $originalName, $targetPath, $confidence, $batch, $existingDoc, &$dbSuccess) {
                        if ($existingDoc) {
                            // Kasus Pembaruan Batch Berbeda (batch lama dari sesi sebelumnya) -> Lakukan update/replace
                            // Hapus file lama di S3 jika path berubah
                            if ($existingDoc->bucket_path !== $targetPath) {
                                try {
                                    if (Storage::disk('supabase')->exists($existingDoc->bucket_path)) {
                                        Storage::disk('supabase')->delete($existingDoc->bucket_path);
                                    }
                                } catch (Throwable $e) {
                                    logger()->warning('Gagal menghapus file lama di S3: '.$e->getMessage());
                                }
                            }

                            $existingDoc->update([
                                'original_filename' => $originalName,
                                'bucket_path' => $targetPath,
                                'match_confidence' => $confidence,
                                'batch_id' => $this->uploadBatchId,
                                'uploaded_by' => $batch->uploaded_by,
                                'uploaded_at' => now(),
                            ]);
                            $dbSuccess = true;
                        } else {
                            // Belum ada dokumen sama sekali -> Lakukan create baru
                            Document::create([
                                'client_id' => $clientId,
                                'type' => $type,
                                'original_filename' => $originalName,
                                'bucket_path' => $targetPath,
                                'match_confidence' => $confidence,
                                'batch_id' => $this->uploadBatchId,
                                'uploaded_by' => $batch->uploaded_by,
                                'uploaded_at' => now(),
                            ]);
                            $dbSuccess = true;
                        }
                    });
                });
            } catch (Throwable $dbEx) {
                // Log error database, rollback transaksi per-file (file lain tetap aman)
                logger()->error("Gagal menyimpan record DB untuk {$originalName}: ".$dbEx->getMessage());
                DocumentUploadFailure::create([
                    'batch_id' => $this->uploadBatchId,
                    'original_filename' => $originalName,
                    'folder_type' => $type,
                    'failure_reason' => 'Error Database: '.$dbEx->getMessage(),
                    'best_match_name' => null,
                    'best_match_score' => null,
                    'resolved_client_id' => null,
                    'resolved_at' => null,
                ]);

                continue;
            }

            // 4. Hapus file temp lokal HANYA jika upload S3 & DB transaction berhasil
            if ($dbSuccess) {
                if (Storage::disk('local')->exists($tempPath)) {
                    Storage::disk('local')->delete($tempPath);
                }
            }
        }
    }
}
