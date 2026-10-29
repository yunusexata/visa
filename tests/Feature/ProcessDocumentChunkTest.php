<?php

use App\Models\Client\Client;
use App\Models\Document;
use App\Models\DocumentUploadBatch;
use App\Models\DocumentUploadFailure;
use App\Models\User;
use App\Jobs\ProcessDocumentChunk;
use Illuminate\Support\Facades\Storage;
beforeEach(function () {
    (new \Database\Seeders\User\PermissionSeeder())->run();
    (new \Database\Seeders\User\RoleSeeder())->run();
});

test('process document chunk handles successful upload and database creation', function () {
    Storage::fake('local');
    Storage::fake('supabase');

    $admin = User::factory()->create();
    $client = Client::create([
        'no_jp' => 'JP999',
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

    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'status' => 'processing',
        'total_files' => 1,
        'matched_files' => 0,
        'failed_files' => 0,
    ]);

    $tempPath = "tmp_uploads/{$batch->id}/file1.png";
    Storage::disk('local')->put($tempPath, 'dummy contents');

    $filesData = [
        [
            'temp_path' => $tempPath,
            'original_name' => 'file1.png',
            'type' => 'ichijikin',
            'matched' => true,
            'client_id' => $client->id,
            'confidence' => 0.98,
        ]
    ];

    $job = new ProcessDocumentChunk($batch->id, $filesData);
    $job->handle();

    // 1. Assert S3 upload succeeded
    Storage::disk('supabase')->assertExists("documents/{$client->id}/ichijikin/file1.png");

    // 2. Assert DB Document record was created
    $this->assertDatabaseHas('documents', [
        'client_id' => $client->id,
        'type' => 'ichijikin',
        'original_filename' => 'file1.png',
        'batch_id' => $batch->id,
    ]);

    // 3. Assert local temp file was deleted
    Storage::disk('local')->assertMissing($tempPath);
});

test('process document chunk handles S3 upload failure without database write and keeps temp file', function () {
    Storage::fake('local');
    // We can simulate S3 failure by faking supabase but NOT putting the file, or using mock
    Storage::fake('supabase');

    // We can override supabase put behaviour or use mock, but simpler:
    // We can configure a fake disk that is write-protected or throws error.
    // In Laravel, we can mock the Storage disk:
    $mockDisk = Mockery::mock(\Illuminate\Contracts\Filesystem\Filesystem::class);
    $mockDisk->shouldReceive('put')->andReturn(false); // Simulate return false on S3
    Storage::set('supabase', $mockDisk);

    $admin = User::factory()->create();
    $client = Client::create([
        'no_jp' => 'JP999',
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

    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'status' => 'processing',
        'total_files' => 1,
        'matched_files' => 0,
        'failed_files' => 0,
    ]);

    $tempPath = "tmp_uploads/{$batch->id}/file1.png";
    Storage::disk('local')->put($tempPath, 'dummy contents');

    $filesData = [
        [
            'temp_path' => $tempPath,
            'original_name' => 'file1.png',
            'type' => 'ichijikin',
            'matched' => true,
            'client_id' => $client->id,
            'confidence' => 0.98,
        ]
    ];

    $job = new ProcessDocumentChunk($batch->id, $filesData);
    $job->handle();

    // 1. Assert DB Document record was NOT created
    $this->assertDatabaseMissing('documents', [
        'client_id' => $client->id,
        'type' => 'ichijikin',
    ]);

    // 2. Assert local temp file STILL exists
    Storage::disk('local')->assertExists($tempPath);

    // 3. Assert failure log was created
    $this->assertDatabaseHas('document_upload_failures', [
        'batch_id' => $batch->id,
        'original_filename' => 'file1.png',
    ]);
});

test('process document chunk handles DB failure by rolling back record and keeping temp file', function () {
    Storage::fake('local');
    Storage::fake('supabase');

    $admin = User::factory()->create();
    
    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'status' => 'processing',
        'total_files' => 1,
        'matched_files' => 0,
        'failed_files' => 0,
    ]);

    $tempPath = "tmp_uploads/{$batch->id}/file1.png";
    Storage::disk('local')->put($tempPath, 'dummy contents');

    // Trigger DB failure by using a non-existent client ID that violates foreign key constraints
    $filesData = [
        [
            'temp_path' => $tempPath,
            'original_name' => 'file1.png',
            'type' => 'ichijikin',
            'matched' => true,
            'client_id' => 999999, // Invalid client ID
            'confidence' => 0.98,
        ]
    ];

    $job = new ProcessDocumentChunk($batch->id, $filesData);
    $job->handle();

    // Assert local temp file STILL exists
    Storage::disk('local')->assertExists($tempPath);

    // Assert failure log was created
    $this->assertDatabaseHas('document_upload_failures', [
        'batch_id' => $batch->id,
        'original_filename' => 'file1.png',
    ]);
});

test('process document chunk idempotency: retry handles previously processed files', function () {
    Storage::fake('local');
    Storage::fake('supabase');

    $admin = User::factory()->create();
    $client = Client::create([
        'no_jp' => 'JP999',
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

    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'status' => 'processing',
        'total_files' => 1,
        'matched_files' => 0,
        'failed_files' => 0,
    ]);

    // Create Document beforehand simulating first successful run
    $doc = Document::create([
        'client_id' => $client->id,
        'type' => 'ichijikin',
        'original_filename' => 'file1.png',
        'bucket_path' => "documents/{$client->id}/ichijikin/file1.png",
        'match_confidence' => 0.98,
        'batch_id' => $batch->id,
        'uploaded_by' => $admin->id,
    ]);

    // Temp file is already deleted in first run
    $tempPath = "tmp_uploads/{$batch->id}/file1.png";

    $filesData = [
        [
            'temp_path' => $tempPath,
            'original_name' => 'file1.png',
            'type' => 'ichijikin',
            'matched' => true,
            'client_id' => $client->id,
            'confidence' => 0.98,
        ]
    ];

    $job = new ProcessDocumentChunk($batch->id, $filesData);
    $job->handle();

    // Assert no duplicate index errors or new records
    expect(Document::count())->toBe(1);
    
    // Assert no duplicate failure records or file_lost logs
    $this->assertDatabaseMissing('document_upload_failures', [
        'batch_id' => $batch->id,
        'original_filename' => 'file1.png',
    ]);
});

test('process document chunk idempotency: duplicate_in_batch is skipped and recorded as failure', function () {
    Storage::fake('local');
    Storage::fake('supabase');

    $admin = User::factory()->create();
    $client = Client::create([
        'no_jp' => 'JP999',
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

    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'status' => 'processing',
        'total_files' => 1,
        'matched_files' => 0,
        'failed_files' => 0,
    ]);

    // Document already processed in the current batch with a DIFFERENT file name
    $doc = Document::create([
        'client_id' => $client->id,
        'type' => 'ichijikin',
        'original_filename' => 'first_file.png',
        'bucket_path' => "documents/{$client->id}/ichijikin/first_file.png",
        'match_confidence' => 0.98,
        'batch_id' => $batch->id,
        'uploaded_by' => $admin->id,
    ]);

    $tempPath = "tmp_uploads/{$batch->id}/second_file.png";
    Storage::disk('local')->put($tempPath, 'dummy contents');

    $filesData = [
        [
            'temp_path' => $tempPath,
            'original_name' => 'second_file.png',
            'type' => 'ichijikin',
            'matched' => true,
            'client_id' => $client->id,
            'confidence' => 0.98,
        ]
    ];

    $job = new ProcessDocumentChunk($batch->id, $filesData);
    $job->handle();

    // Assert the document remains the first one (not overwritten)
    $existing = Document::where('client_id', $client->id)->where('type', 'ichijikin')->first();
    expect($existing->original_filename)->toBe('first_file.png');

    // Assert failure log duplicate_in_batch was created
    $this->assertDatabaseHas('document_upload_failures', [
        'batch_id' => $batch->id,
        'original_filename' => 'second_file.png',
        'failure_reason' => 'duplicate_in_batch',
    ]);

    // Assert temp file was cleaned up
    Storage::disk('local')->assertMissing($tempPath);
});

test('process document chunk idempotency: replacement from old batch updates successfully', function () {
    Storage::fake('local');
    Storage::fake('supabase');

    $admin = User::factory()->create();
    $client = Client::create([
        'no_jp' => 'JP999',
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

    $oldBatchId = 11;
    $newBatch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'status' => 'processing',
        'total_files' => 1,
        'matched_files' => 0,
        'failed_files' => 0,
    ]);

    // Document already processed in an OLD batch
    $doc = Document::create([
        'client_id' => $client->id,
        'type' => 'ichijikin',
        'original_filename' => 'old_file.png',
        'bucket_path' => "documents/{$client->id}/ichijikin/old_file.png",
        'match_confidence' => 0.98,
        'batch_id' => $oldBatchId,
        'uploaded_by' => $admin->id,
    ]);

    $tempPath = "tmp_uploads/{$newBatch->id}/new_file.png";
    Storage::disk('local')->put($tempPath, 'dummy contents');

    $filesData = [
        [
            'temp_path' => $tempPath,
            'original_name' => 'new_file.png',
            'type' => 'ichijikin',
            'matched' => true,
            'client_id' => $client->id,
            'confidence' => 0.99,
        ]
    ];

    $job = new ProcessDocumentChunk($newBatch->id, $filesData);
    $job->handle();

    // Assert the document was updated successfully with new batch and filename
    $existing = Document::where('client_id', $client->id)->where('type', 'ichijikin')->first();
    expect($existing->original_filename)->toBe('new_file.png');
    expect($existing->batch_id)->toBe($newBatch->id);
    expect($existing->match_confidence)->toBe("0.990");

    // Assert old file deleted from S3
    Storage::disk('supabase')->assertMissing("documents/{$client->id}/ichijikin/old_file.png");
    Storage::disk('supabase')->assertExists("documents/{$client->id}/ichijikin/new_file.png");

    // Assert local temp file was deleted
    Storage::disk('local')->assertMissing($tempPath);
});

test('process document chunk handles lost local file and records failure_reason as file_lost', function () {
    Storage::fake('local');
    Storage::fake('supabase');

    $admin = User::factory()->create();
    $client = Client::create([
        'no_jp' => 'JP999',
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

    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'status' => 'processing',
        'total_files' => 1,
        'matched_files' => 0,
        'failed_files' => 0,
    ]);

    // Note: We do NOT put the file on local disk (simulating file_lost)
    $tempPath = "tmp_uploads/{$batch->id}/lost_file.png";

    $filesData = [
        [
            'temp_path' => $tempPath,
            'original_name' => 'lost_file.png',
            'type' => 'ichijikin',
            'matched' => true,
            'client_id' => $client->id,
            'confidence' => 0.98,
        ]
    ];

    $job = new ProcessDocumentChunk($batch->id, $filesData);
    $job->handle();

    // Assert S3 upload was not attempted (missing in S3)
    Storage::disk('supabase')->assertMissing("documents/{$client->id}/ichijikin/lost_file.png");

    // Assert DB Document record was NOT created
    $this->assertDatabaseMissing('documents', [
        'client_id' => $client->id,
        'type' => 'ichijikin',
    ]);

    // Assert failure log is registered as file_lost
    $this->assertDatabaseHas('document_upload_failures', [
        'batch_id' => $batch->id,
        'original_filename' => 'lost_file.png',
        'failure_reason' => 'file_lost',
    ]);
});
