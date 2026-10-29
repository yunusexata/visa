<?php

use App\Models\Client\Client;
use App\Models\Document;
use App\Models\DocumentUploadBatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('supabase');
    foreach ([User::ROLE_SUPER_ADMIN, User::ROLE_HS, User::ROLE_HS2, User::ROLE_HS3, User::ROLE_SALES, User::ROLE_CLIENT] as $roleName) {
        Role::findOrCreate($roleName, 'web');
    }
});

test('document download records live tracking activity with batch_id', function () {
    // 1. Setup mock models
    $user = User::factory()->create();
    $client = Client::create([
        'no_jp' => 'JP12345',
        'name' => 'Test Client',
        'name_normalized' => 'TEST CLIENT',
        'dob' => '1995-01-01',
        'claim_type' => 'speed',
        'status' => 'active',
        'raw_data' => json_encode([]),
        'source_file_name' => 'test.xlsx',
        'imported_at' => now(),
        'pic' => 'Admin Test',
    ]);
    $user->update(['client_id' => $client->id]);

    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $user->id,
        'total_files' => 1,
        'matched_files' => 1,
        'failed_files' => 0,
        'status' => 'completed',
    ]);

    $filePath = 'documents/'.$client->id.'/ichijikin/test.jpg';
    Storage::disk('supabase')->put($filePath, 'dummy file content');

    $document = Document::create([
        'client_id' => $client->id,
        'type' => 'ichijikin',
        'original_filename' => 'test.jpg',
        'bucket_path' => $filePath,
        'batch_id' => $batch->id,
        'uploaded_by' => $user->id,
    ]);

    // 2. Perform authenticated download request
    $response = $this->actingAs($user)
        ->get(route('documents.download', ['type' => 'ichijikin']));

    // 3. Assert HTTP response success & headers
    $response->assertStatus(200);
    $response->assertHeader('Content-Disposition', 'attachment; filename="test.jpg"');

    // 4. Assert row created in document_downloads
    $this->assertDatabaseHas('document_downloads', [
        'client_id' => $client->id,
        'document_id' => $document->id,
        'batch_id' => $batch->id,
        'source' => 'live_tracked',
    ]);
});

test('document download survives db logging exception (Test Case #3)', function () {
    // 1. Setup mock models
    $user = User::factory()->create();
    $client = Client::create([
        'no_jp' => 'JP12346',
        'name' => 'Test Client Fail',
        'name_normalized' => 'TEST CLIENT FAIL',
        'dob' => '1995-01-01',
        'claim_type' => 'speed',
        'status' => 'active',
        'raw_data' => json_encode([]),
        'source_file_name' => 'test.xlsx',
        'imported_at' => now(),
        'pic' => 'Admin Test',
    ]);
    $user->update(['client_id' => $client->id]);

    $filePath = 'documents/'.$client->id.'/resi/test_resi.jpg';
    Storage::disk('supabase')->put($filePath, 'dummy resi content');

    Document::create([
        'client_id' => $client->id,
        'type' => 'resi',
        'original_filename' => 'test_resi.jpg',
        'bucket_path' => $filePath,
        'uploaded_by' => $user->id,
    ]);

    // 2. Simulate DB logging failure by dropping document_downloads table
    Schema::dropIfExists('document_downloads');

    // 3. Perform download request
    $response = $this->actingAs($user)
        ->get(route('documents.download', ['type' => 'resi']));

    // 4. Assert download response remains 200 OK despite DB insert failure
    $response->assertStatus(200);
    $response->assertHeader('Content-Disposition', 'attachment; filename="test_resi.jpg"');
});

test('multiple download requests by same client record separate rows (Test Case #4)', function () {
    // 1. Setup mock models
    $user = User::factory()->create();
    $client = Client::create([
        'no_jp' => 'JP12347',
        'name' => 'Test Client Multi',
        'name_normalized' => 'TEST CLIENT MULTI',
        'dob' => '1995-01-01',
        'claim_type' => 'speed',
        'status' => 'active',
        'raw_data' => json_encode([]),
        'source_file_name' => 'test.xlsx',
        'imported_at' => now(),
        'pic' => 'Admin Test',
    ]);
    $user->update(['client_id' => $client->id]);

    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $user->id,
        'total_files' => 1,
        'matched_files' => 1,
        'failed_files' => 0,
        'status' => 'completed',
    ]);

    $filePath = 'documents/'.$client->id.'/kwitansi/test_kwitansi.jpg';
    Storage::disk('supabase')->put($filePath, 'dummy kwitansi content');

    $document = Document::create([
        'client_id' => $client->id,
        'type' => 'kwitansi',
        'original_filename' => 'test_kwitansi.jpg',
        'bucket_path' => $filePath,
        'batch_id' => $batch->id,
        'uploaded_by' => $user->id,
    ]);

    // 2. Execute 3 consecutive download requests
    $this->actingAs($user)->get(route('documents.download', ['type' => 'kwitansi']))->assertStatus(200);
    $this->actingAs($user)->get(route('documents.download', ['type' => 'kwitansi']))->assertStatus(200);
    $this->actingAs($user)->get(route('documents.download', ['type' => 'kwitansi']))->assertStatus(200);

    // 3. Query document_downloads and assert exactly 3 separate rows were created
    $downloadLogsCount = DB::table('document_downloads')
        ->where('client_id', $client->id)
        ->where('document_id', $document->id)
        ->where('source', 'live_tracked')
        ->count();

    expect($downloadLogsCount)->toBe(3);
});
