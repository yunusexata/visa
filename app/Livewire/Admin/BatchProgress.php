<?php

namespace App\Livewire\Admin;

use App\Models\Document;
use App\Models\DocumentUploadBatch;
use Illuminate\Support\Facades\Bus;
use Livewire\Component;

class BatchProgress extends Component
{
    public $batchId;

    public $progress = 0;

    public $status = 'processing';

    public $totalJobs = 0;

    public $pendingJobs = 0;

    public $failedJobs = 0;

    public function mount($batchId)
    {
        $this->batchId = $batchId;
        $this->checkProgress();
    }

    public function checkProgress()
    {
        $batch = DocumentUploadBatch::find($this->batchId);

        if (! $batch) {
            $this->status = 'failed';
            $this->progress = 100;

            return;
        }

        $this->status = $batch->status;

        // Jika status sudah selesai di DB (completed/failed/partial_failed), progress langsung 100
        if (in_array($batch->status, ['completed', 'failed', 'partial_failed'])) {
            $this->progress = 100;
        }
        // Cek progress lewat Laravel Bus Batch jika laravel_batch_id tersedia
        elseif ($batch->laravel_batch_id) {
            $laravelBatch = Bus::findBatch($batch->laravel_batch_id);

            if ($laravelBatch) {
                $this->progress = $laravelBatch->progress();
                $this->totalJobs = $laravelBatch->totalJobs;
                $this->pendingJobs = $laravelBatch->pendingJobs;
                $this->failedJobs = $laravelBatch->failedJobs;

                if ($laravelBatch->finished()) {
                    $batch->refresh();
                    $this->status = $batch->status;
                    $this->progress = 100;
                }
            } else {
                $this->fallbackProgress($batch);
            }
        } else {
            $this->fallbackProgress($batch);
        }

        // Auto-redirect jika proses selesai
        if (in_array($this->status, ['completed', 'failed', 'partial_failed'])) {
            $message = $this->status === 'completed'
                ? 'Upload dokumen selesai.'
                : ($this->status === 'partial_failed'
                    ? 'Upload selesai dengan beberapa kegagalan.'
                    : 'Seluruh upload dalam batch gagal.');

            return redirect()->route('admin.documents.batchDetail', $this->batchId)
                ->with('success', $message);
        }
    }

    protected function fallbackProgress($batch)
    {
        if ($batch->status === 'processing') {
            $successCount = Document::where('batch_id', $batch->id)->count();
            $totalMatched = $batch->matched_files;

            if ($totalMatched > 0) {
                // Batasi maksimal 99% agar 100% hanya tercapai saat status resmi selesai
                $this->progress = min(99, (int) round(($successCount / $totalMatched) * 100));
            } else {
                $this->progress = 0;
            }
        } else {
            $this->progress = 100;
        }
    }

    public function render()
    {
        return view('livewire.admin.batch-progress');
    }
}
