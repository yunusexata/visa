<?php

use App\Models\Client\Client;
use App\Models\Document;
use App\Models\DocumentUploadBatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('supabase');
    foreach ([User::ROLE_SUPER_ADMIN, User::ROLE_HS, User::ROLE_HS2, User::ROLE_HS3, User::ROLE_SALES, User::ROLE_CLIENT] as $roleName) {
        Role::findOrCreate($roleName, 'web');
    }
});

test('end-to-end dummy batch creation, client download, and live-tracking verification', function () {
    // 1. Create Dummy Batch
    $admin = User::first();
    $dummyBatch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id ?? 1,
        'total_files' => 3,
        'matched_files' => 3,
        'failed_files' => 0,
        'status' => 'completed',
    ]);

    // 2. Create Dummy Client & User
    $dummyClient = Client::create([
        'no_jp' => 'TEST_DUMMY_JP999',
        'name' => 'CLIENT DUMMY TEST TRACKING',
        'name_normalized' => 'CLIENT DUMMY TEST TRACKING',
        'dob' => '1998-08-08',
        'claim_type' => 'speed',
        'status' => 'active',
        'raw_data' => json_encode([]),
        'source_file_name' => 'dummy_test.xlsx',
        'imported_at' => now(),
        'pic' => 'Admin Test',
    ]);

    $dummyUser = User::create([
        'client_id' => $dummyClient->id,
        'name' => 'CLIENT DUMMY TEST TRACKING',
        'email' => 'dummy_e2e_test_'.uniqid().'@exata.test',
        'password' => bcrypt('password'),
    ]);
    $dummyUser->assignRole(User::ROLE_CLIENT);

    // 3. Create Dummy Documents (Ichijikin, Resi, Kwitansi)
    $filePath = 'documents/'.$dummyClient->id.'/ichijikin/dummy_ichijikin.jpg';
    Storage::disk('supabase')->put($filePath, 'dummy binary content');

    $dummyDoc = Document::create([
        'client_id' => $dummyClient->id,
        'type' => 'ichijikin',
        'original_filename' => 'dummy_ichijikin.jpg',
        'bucket_path' => $filePath,
        'batch_id' => $dummyBatch->id,
        'uploaded_by' => $admin->id ?? 1,
    ]);

    // 4. Perform Download Request as Client
    $response = $this->actingAs($dummyUser)
        ->get(route('documents.download', ['type' => 'ichijikin']));

    // Assert download response success
    $response->assertStatus(200);
    $response->assertHeader('Content-Disposition', 'attachment; filename="dummy_ichijikin.jpg"');

    // 5. Verify live tracking record in document_downloads table
    $trackedRow = DB::table('document_downloads')
        ->where('client_id', $dummyClient->id)
        ->where('document_id', $dummyDoc->id)
        ->first();

    expect($trackedRow)->not->toBeNull();
    expect($trackedRow->source)->toBe('live_tracked');
    expect($trackedRow->batch_id)->toBe($dummyBatch->id);

    // 6. Clean Up Dummy Data
    DB::table('document_downloads')->where('client_id', $dummyClient->id)->delete();
    Document::where('client_id', $dummyClient->id)->delete();
    $dummyUser->delete();
    $dummyClient->delete();
    $dummyBatch->delete();
});
