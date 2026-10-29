<?php

use App\Livewire\Admin\BatchProgress;
use App\Models\Client\Client;
use App\Models\Document;
use App\Models\DocumentUploadBatch;
use App\Models\User;
use Database\Seeders\User\PermissionSeeder;
use Database\Seeders\User\RoleSeeder;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;

beforeEach(function () {
    (new PermissionSeeder)->run();
    (new RoleSeeder)->run();
});

test('BatchProgress Livewire component loads, displays progress, and handles fallback', function () {
    $admin = User::factory()->create();

    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'total_files' => 10,
        'matched_files' => 8,
        'failed_files' => 2,
        'status' => 'processing',
    ]);

    // Create 4 distinct clients and 1 document for each to get successCount = 4
    // 4 success / 8 matched_files = 50% progress
    for ($i = 0; $i < 4; $i++) {
        $client = Client::create([
            'no_jp' => "JP99{$i}",
            'name' => "Client {$i}",
            'name_normalized' => "client {$i}",
            'dob' => '1990-01-01',
            'claim_type' => 'normal',
            'status' => 'pending',
            'raw_data' => '{}',
            'source_file_name' => 'ref.xlsx',
            'imported_at' => now(),
            'pic' => 'sg',
        ]);

        Document::create([
            'client_id' => $client->id,
            'type' => 'ichijikin',
            'original_filename' => "file_{$i}.png",
            'bucket_path' => "documents/{$client->id}/ichijikin/file_{$i}.png",
            'batch_id' => $batch->id,
            'uploaded_by' => $admin->id,
        ]);
    }

    // Test component mount and initial checkProgress (no laravel_batch_id fallback)
    Livewire::test(BatchProgress::class, ['batchId' => $batch->id])
        ->assertSet('batchId', $batch->id)
        ->assertSet('status', 'processing')
        ->assertSet('progress', 50) // Dynamic fallback progress is (4/8)*100 = 50
        ->assertSee('Mengunggah Dokumen ke Supabase')
        ->assertSee('Status:')
        ->assertSee('Processing');
});

test('BatchProgress component handles database completed status redirect', function () {
    $admin = User::factory()->create();
    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'total_files' => 10,
        'matched_files' => 10,
        'failed_files' => 0,
        'status' => 'completed',
    ]);

    // If batch status is already completed, mounting should trigger redirect
    Livewire::test(BatchProgress::class, ['batchId' => $batch->id])
        ->assertRedirect(route('admin.documents.batchDetail', $batch->id));
});

test('BatchProgress component reads real Laravel Bus batch progress', function () {
    $admin = User::factory()->create();

    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'laravel_batch_id' => 'mock-laravel-batch-uuid',
        'total_files' => 10,
        'matched_files' => 10,
        'failed_files' => 0,
        'status' => 'processing',
    ]);

    // Mock the Bus::findBatch method
    $mockLaravelBatch = Mockery::mock(Batch::class);
    $mockLaravelBatch->shouldReceive('progress')->andReturn(75);
    $mockLaravelBatch->shouldReceive('finished')->andReturn(false);
    $mockLaravelBatch->totalJobs = 4;
    $mockLaravelBatch->pendingJobs = 1;
    $mockLaravelBatch->failedJobs = 0;

    Bus::shouldReceive('findBatch')
        ->with('mock-laravel-batch-uuid')
        ->andReturn($mockLaravelBatch);

    Livewire::test(BatchProgress::class, ['batchId' => $batch->id])
        ->assertSet('status', 'processing')
        ->assertSet('progress', 75)
        ->assertSet('totalJobs', 4)
        ->assertSet('pendingJobs', 1)
        ->assertSet('failedJobs', 0);
});
