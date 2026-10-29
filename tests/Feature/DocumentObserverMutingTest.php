<?php

use App\Jobs\ProcessDocumentChunk;
use App\Models\Client\Client;
use App\Models\Document;
use App\Models\DocumentUploadBatch;
use App\Models\Service\SendEmailLog;
use App\Models\User;
use App\Observers\DocumentObserver;
use Database\Seeders\User\PermissionSeeder;
use Database\Seeders\User\RoleSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    (new PermissionSeeder)->run();
    (new RoleSeeder)->run();
});

test('DocumentObserver is muted during ProcessDocumentChunk and triggered consolidatedly afterwards', function () {
    Storage::fake('local');
    Storage::fake('supabase');

    $admin = User::factory()->create();
    $client = Client::create([
        'no_jp' => 'JP777',
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

    // Create client user and email for notification
    $clientUser = User::create([
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => bcrypt('password'),
        'client_id' => $client->id,
    ]);

    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'total_files' => 3,
        'matched_files' => 3,
        'failed_files' => 0,
        'status' => 'processing',
    ]);

    // Prepare 3 matched files: ichijikin, resi, kwitansi
    $files = [
        [
            'original_name' => 'doc1.png',
            'temp_path' => "tmp_uploads/{$batch->id}/ichijikin/doc1.png",
            'type' => 'ichijikin',
            'matched' => true,
            'client_id' => $client->id,
        ],
        [
            'original_name' => 'doc2.png',
            'temp_path' => "tmp_uploads/{$batch->id}/resi/doc2.png",
            'type' => 'resi',
            'matched' => true,
            'client_id' => $client->id,
        ],
        [
            'original_name' => 'doc3.png',
            'temp_path' => "tmp_uploads/{$batch->id}/kwitansi/doc3.png",
            'type' => 'kwitansi',
            'matched' => true,
            'client_id' => $client->id,
        ],
    ];

    foreach ($files as $file) {
        Storage::disk('local')->put($file['temp_path'], 'dummy contents');
    }

    // 1. Process chunk job. While running, model events/observers are MUTED
    $job = new ProcessDocumentChunk($batch->id, $files);
    $job->handle();

    // Verify all 3 documents were successfully written to database
    expect(Document::where('batch_id', $batch->id)->count())->toBe(3);

    // Verify that NO email log was created during the chunk processing (because events were muted)
    $emailLogCount = SendEmailLog::where('subject_type', Client::class)
        ->where('subject_id', $client->id)
        ->count();
    expect($emailLogCount)->toBe(0);

    // 2. Trigger the consolidated check manually (simulating the batch finally callback behavior)
    DocumentObserver::notifyIfComplete($client);

    // Verify that exactly ONE email log was created after the consolidated check ran
    $finalEmailLog = SendEmailLog::where('subject_type', Client::class)
        ->where('subject_id', $client->id)
        ->first();
    expect($finalEmailLog)->not->toBeNull();
    expect($finalEmailLog->email)->toBe('budi@example.com');
});

test('DocumentObserver is active for standard single uploads outside the chunk job context', function () {
    $admin = User::factory()->create();
    $client = Client::create([
        'no_jp' => 'JP888',
        'name' => 'Siti Aminah',
        'name_normalized' => 'siti aminah',
        'dob' => '1990-01-01',
        'claim_type' => 'normal',
        'status' => 'pending',
        'raw_data' => '{}',
        'source_file_name' => 'ref.xlsx',
        'imported_at' => now(),
        'pic' => 'sg',
    ]);

    User::create([
        'name' => 'Siti Aminah',
        'email' => 'siti@example.com',
        'password' => bcrypt('password'),
        'client_id' => $client->id,
    ]);

    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'total_files' => 3,
        'matched_files' => 3,
        'failed_files' => 0,
        'status' => 'completed',
    ]);

    // Create first two documents directly (outside of withoutEvents)
    Document::create([
        'client_id' => $client->id,
        'type' => 'ichijikin',
        'original_filename' => 'doc1.png',
        'bucket_path' => 'documents/1/ichijikin/doc1.png',
        'batch_id' => $batch->id,
        'uploaded_by' => $admin->id,
    ]);

    Document::create([
        'client_id' => $client->id,
        'type' => 'resi',
        'original_filename' => 'doc2.png',
        'bucket_path' => 'documents/1/resi/doc2.png',
        'batch_id' => $batch->id,
        'uploaded_by' => $admin->id,
    ]);

    // No email log should exist yet because required documents are not complete (missing kwitansi)
    $emailLogCount = SendEmailLog::where('subject_type', Client::class)
        ->where('subject_id', $client->id)
        ->count();
    expect($emailLogCount)->toBe(0);

    // Create the third document directly (outside of withoutEvents). This triggers the observer
    Document::create([
        'client_id' => $client->id,
        'type' => 'kwitansi',
        'original_filename' => 'doc3.png',
        'bucket_path' => 'documents/1/kwitansi/doc3.png',
        'batch_id' => $batch->id,
        'uploaded_by' => $admin->id,
    ]);

    // An email log should be created immediately because observer is active for single uploads
    $emailLog = SendEmailLog::where('subject_type', Client::class)
        ->where('subject_id', $client->id)
        ->first();
    expect($emailLog)->not->toBeNull();
    expect($emailLog->email)->toBe('siti@example.com');
});
