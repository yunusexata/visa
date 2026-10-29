<?php

use App\Enums\Service\EmailLogStatus;
use App\Mail\DocumentAvailableNotification;
use App\Models\Client\Client;
use App\Models\Document;
use App\Models\Service\SendEmailLog;
use App\Models\User;
use Database\Seeders\User\PermissionSeeder;
use Database\Seeders\User\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('notifications are only sent when all 3 required documents are present', function () {
    // Run seeders to set up roles and permissions
    (new PermissionSeeder)->run();
    (new RoleSeeder)->run();

    // Create sales user for the PIC requirement
    $sales = User::create([
        'name' => 'Sales Guy',
        'email' => 'sales@example.com',
        'pic' => 'sg',
        'password' => bcrypt('password'),
    ]);
    $sales->assignRole(User::ROLE_SALES);

    // Create client
    $client = Client::create([
        'no_jp' => 'JP123456',
        'name' => 'John Doe',
        'name_normalized' => 'john doe',
        'dob' => '1990-01-01',
        'email' => 'john@example.com',
        'phone' => '123456',
        'claim_type' => Client::CLAIM_TYPE_NORMAL,
        'status' => Client::STATUS_PENDING,
        'raw_data' => '{}',
        'source_file_name' => 'test.xlsx',
        'source_file_disk' => 'local',
        'source_file_path' => 'imports/test.xlsx',
        'imported_at' => now(),
        'pic' => 'sg',
    ]);

    // Create associated user for client
    $clientUser = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'client_id' => $client->id,
        'password' => bcrypt('password'),
    ]);

    // Step 1: Create 'ichijikin' document - notification should NOT trigger
    $doc1 = Document::create([
        'client_id' => $client->id,
        'type' => 'ichijikin',
        'original_filename' => 'ichijikin.pdf',
        'bucket_path' => 'docs/ichijikin.pdf',
        'uploaded_by' => $sales->id,
    ]);

    $this->assertDatabaseMissing('send_email_logs', [
        'subject_type' => Client::class,
        'subject_id' => $client->id,
    ]);

    // Step 2: Create 'resi' document - notification should NOT trigger
    $doc2 = Document::create([
        'client_id' => $client->id,
        'type' => 'resi',
        'original_filename' => 'resi.pdf',
        'bucket_path' => 'docs/resi.pdf',
        'uploaded_by' => $sales->id,
    ]);

    $this->assertDatabaseMissing('send_email_logs', [
        'subject_type' => Client::class,
        'subject_id' => $client->id,
    ]);

    // Step 3: Create 'kwitansi' document - notification SHOULD trigger exactly once
    $doc3 = Document::create([
        'client_id' => $client->id,
        'type' => 'kwitansi',
        'original_filename' => 'kwitansi.pdf',
        'bucket_path' => 'docs/kwitansi.pdf',
        'uploaded_by' => $sales->id,
    ]);

    $this->assertDatabaseHas('send_email_logs', [
        'subject_type' => Client::class,
        'subject_id' => $client->id,
        'email' => 'john@example.com',
        'mailable' => DocumentAvailableNotification::class,
        'status' => EmailLogStatus::SENT,
    ]);

    // Assert only one log exists for the client
    $logsCount = SendEmailLog::where('subject_type', Client::class)
        ->where('subject_id', $client->id)
        ->count();
    expect($logsCount)->toBe(1);

    // Step 4: Try updating one of the documents to see if it triggers duplicate notifications
    $doc3->update([
        'original_filename' => 'kwitansi_updated.pdf',
    ]);

    $logsCountAfterUpdate = SendEmailLog::where('subject_type', Client::class)
        ->where('subject_id', $client->id)
        ->count();
    expect($logsCountAfterUpdate)->toBe(1);
});
