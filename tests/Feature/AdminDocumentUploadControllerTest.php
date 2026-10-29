<?php

use App\Jobs\ProcessDocumentChunk;
use App\Models\Client\Client;
use App\Models\DocumentUploadBatch;
use App\Models\User;
use Database\Seeders\User\PermissionSeeder;
use Database\Seeders\User\RoleSeeder;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    (new PermissionSeeder)->run();
    (new RoleSeeder)->run();
});

test('confirmAndUpload creates batch, chunks matched files, and dispatches Bus batch', function () {
    Storage::fake('local');
    Bus::fake();

    $admin = User::factory()->create();
    $this->actingAs($admin);

    $client = Client::create([
        'no_jp' => 'JP101',
        'name' => 'Budi Santoso',
        'name_normalized' => 'budi santoso',
        'dob' => '1990-01-01',
        'claim_type' => 'normal',
        'status' => 'pending',
        'raw_data' => '{}',
        'source_file_name' => 'ref.xlsx',
        'imported_at' => now(),
        'pic' => 'sg',
    ]);

    // Setup matching preview cache data
    $tempBatchId = 'temp_123';

    // Simulate 3 matched files and 1 failed file
    $matchedFiles = [
        [
            'temp_path' => "tmp_uploads/pending_{$admin->id}_{$tempBatchId}/file1.png",
            'original_filename' => 'file1.png',
            'folder_type' => 'ichijikin',
            'client_id' => $client->id,
            'confidence' => 0.95,
            'manually_assigned' => false,
        ],
        [
            'temp_path' => "tmp_uploads/pending_{$admin->id}_{$tempBatchId}/file2.png",
            'original_filename' => 'file2.png',
            'folder_type' => 'resi',
            'client_id' => $client->id,
            'confidence' => 0.96,
            'manually_assigned' => false,
        ],
        [
            'temp_path' => "tmp_uploads/pending_{$admin->id}_{$tempBatchId}/file3.png",
            'original_filename' => 'file3.png',
            'folder_type' => 'kwitansi',
            'client_id' => $client->id,
            'confidence' => 0.97,
            'manually_assigned' => false,
        ],
    ];

    $failedFiles = [
        [
            'temp_path' => "tmp_uploads/pending_{$admin->id}_{$tempBatchId}/file4.png",
            'original_filename' => 'file4.png',
            'folder_type' => 'ichijikin',
            'confidence' => 0.40,
            'best_candidate' => ['name' => 'Siti Aminah'],
        ],
    ];

    // Put temp files on local disk
    foreach (array_merge($matchedFiles, $failedFiles) as $file) {
        Storage::disk('local')->put($file['temp_path'], 'dummy contents');
    }

    Cache::put("matching_preview_{$admin->id}_{$tempBatchId}", [
        'matched_files' => $matchedFiles,
        'failed_files' => $failedFiles,
    ]);

    // Set chunk size to 2 via env
    putenv('DOCUMENT_CHUNK_SIZE=2');

    $response = $this->post(route('admin.documents.confirmUpload'), [
        'temp_batch_id' => $tempBatchId,
    ]);

    $batch = DocumentUploadBatch::first();
    expect($batch)->not->toBeNull();
    expect($batch->total_files)->toBe(4);
    expect($batch->matched_files)->toBe(3);
    expect($batch->failed_files)->toBe(1);
    expect($batch->status)->toBe('processing');
    expect($batch->laravel_batch_id)->not->toBeNull();

    // Verify Bus::batch was dispatched with 2 chunks (since matched count is 3 and size is 2)
    Bus::assertBatched(function (PendingBatch $pendingBatch) {
        return $pendingBatch->jobs->count() === 2
            && $pendingBatch->jobs->first() instanceof ProcessDocumentChunk;
    });

    // Verify failed file local temp was deleted immediately and recorded in DB
    Storage::disk('local')->assertMissing("tmp_uploads/pending_{$admin->id}_{$tempBatchId}/file4.png");
    $this->assertDatabaseHas('document_upload_failures', [
        'batch_id' => $batch->id,
        'original_filename' => 'file4.png',
    ]);

    // Clean up putenv
    putenv('DOCUMENT_CHUNK_SIZE');
});
