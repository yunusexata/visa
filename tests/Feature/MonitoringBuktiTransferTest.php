<?php

use App\Helpers\PermissionHelper;
use App\Livewire\MonitoringBuktiTransfer\Datatable;
use App\Models\Client\Client;
use App\Models\Document;
use App\Models\DocumentDownload;
use App\Models\DocumentUploadBatch;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::findOrCreate(User::ROLE_SUPER_ADMIN, 'web');
    Role::findOrCreate(User::ROLE_SALES, 'web');

    // Seed all permissions from helper
    foreach (PermissionHelper::ACCESS_TYPE_ALL as $access => $types) {
        foreach ($types as $type) {
            Permission::findOrCreate(PermissionHelper::transform($access, $type), 'web');
        }
    }

    // Assign to roles
    $superAdminRole = Role::findByName(User::ROLE_SUPER_ADMIN);
    foreach (Permission::all() as $perm) {
        $superAdminRole->givePermissionTo($perm);
    }

    $salesRole = Role::findByName(User::ROLE_SALES);
    $salesRole->givePermissionTo(PermissionHelper::ACCESS_MONITORING_BUKTI_TRANSFER.'.read');
});

test('unauthorized user cannot access monitoring page', function () {
    $guest = User::factory()->create(); // No role

    $response = $this->actingAs($guest)->get('/monitoring-bukti-transfer');
    $response->assertStatus(401);
});

test('authorized super admin and sales can access monitoring page', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPER_ADMIN);

    $sales = User::factory()->create(['name' => 'Ainul', 'pic' => 'ainul']);
    $sales->assignRole(User::ROLE_SALES);

    $this->actingAs($admin)->get('/monitoring-bukti-transfer')->assertStatus(200);
    $this->actingAs($sales)->get('/monitoring-bukti-transfer')->assertStatus(200);
});

test('pic scoping restricts sales to their own clients', function () {
    // Create sales user with pic slamet
    $salesSlamet = User::factory()->create(['name' => 'slamet', 'pic' => 'slamet']);
    $salesSlamet->assignRole(User::ROLE_SALES);

    // Create sales user with pic ainul
    $salesAinul = User::factory()->create(['name' => 'ainul', 'pic' => 'ainul']);
    $salesAinul->assignRole(User::ROLE_SALES);

    // Create clients
    $clientSlamet = Client::create([
        'no_jp' => 'JP001', 'name' => 'Client Slamet', 'name_normalized' => 'CLIENT SLAMET',
        'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}',
        'pic' => 'slamet', 'source_file_name' => 'batch1.xlsx', 'imported_at' => now(),
    ]);
    Document::create([
        'client_id' => $clientSlamet->id, 'type' => 'resi', 'original_filename' => 'r.jpg',
        'bucket_path' => 'a.jpg', 'uploaded_by' => $salesSlamet->id,
    ]);

    $clientAinul = Client::create([
        'no_jp' => 'JP002', 'name' => 'Client Ainul', 'name_normalized' => 'CLIENT AINUL',
        'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}',
        'pic' => 'ainul', 'source_file_name' => 'batch1.xlsx', 'imported_at' => now(),
    ]);
    Document::create([
        'client_id' => $clientAinul->id, 'type' => 'kwitansi', 'original_filename' => 'k.jpg',
        'bucket_path' => 'b.jpg', 'uploaded_by' => $salesAinul->id,
    ]);

    // Test Livewire component under Slamet
    Livewire::actingAs($salesSlamet)
        ->test(Datatable::class)
        ->assertSee('Client Slamet')
        ->assertDontSee('Client Ainul');

    // Test Livewire component under Ainul
    Livewire::actingAs($salesAinul)
        ->test(Datatable::class)
        ->assertSee('Client Ainul')
        ->assertDontSee('Client Slamet');
});

test('status mapping translates downloads logs correctly', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPER_ADMIN);

    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'total_files' => 1,
        'matched_files' => 1,
        'failed_files' => 0,
        'status' => 'completed',
    ]);

    // Client 1: Live Tracked download
    $c1 = Client::create([
        'no_jp' => 'JP101', 'name' => 'Client Live', 'name_normalized' => 'CLIENT LIVE',
        'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}',
        'pic' => 'slamet', 'source_file_name' => 'b1.xlsx', 'imported_at' => now(),
    ]);
    $d1 = Document::create(['client_id' => $c1->id, 'type' => 'resi', 'original_filename' => 'a.jpg', 'bucket_path' => 'a.jpg', 'uploaded_by' => $admin->id, 'batch_id' => $batch->id]);
    DocumentDownload::create(['client_id' => $c1->id, 'document_id' => $d1->id, 'batch_id' => $batch->id, 'source' => 'live_tracked', 'downloaded_at' => now()]);

    // Client 2: Seeder download
    $c2 = Client::create([
        'no_jp' => 'JP102', 'name' => 'Client Seeded', 'name_normalized' => 'CLIENT SEEDED',
        'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}',
        'pic' => 'slamet', 'source_file_name' => 'b1.xlsx', 'imported_at' => now(),
    ]);
    $d2 = Document::create(['client_id' => $c2->id, 'type' => 'kwitansi', 'original_filename' => 'b.jpg', 'bucket_path' => 'b.jpg', 'uploaded_by' => $admin->id, 'batch_id' => $batch->id]);
    DocumentDownload::create(['client_id' => $c2->id, 'document_id' => $d2->id, 'batch_id' => $batch->id, 'source' => 'notified_only', 'downloaded_at' => now()]);

    // Client 3: No download
    $c3 = Client::create([
        'no_jp' => 'JP103', 'name' => 'Client None', 'name_normalized' => 'CLIENT NONE',
        'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}',
        'pic' => 'slamet', 'source_file_name' => 'b1.xlsx', 'imported_at' => now(),
    ]);
    Document::create(['client_id' => $c3->id, 'type' => 'ichijikin', 'original_filename' => 'c.jpg', 'bucket_path' => 'c.jpg', 'uploaded_by' => $admin->id, 'batch_id' => $batch->id]);

    Livewire::actingAs($admin)
        ->test(Datatable::class)
        ->assertViewHas('data', function ($data) {
            $mapped = collect($data->items())->pluck('status', 'name')->toArray();
            expect($mapped['Client Live'])->toBe('downloaded');
            expect($mapped['Client Seeded'])->toBe('seeded');
            expect($mapped['Client None'])->toBe('not_downloaded');

            return true;
        });
});

test('select and having status sync matches client records precisely', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPER_ADMIN);

    $batch = DocumentUploadBatch::create([
        'uploaded_by' => $admin->id,
        'total_files' => 1,
        'matched_files' => 1,
        'failed_files' => 0,
        'status' => 'completed',
    ]);

    $c1 = Client::create(['no_jp' => 'JP201', 'name' => 'Client A', 'name_normalized' => 'CLIENT A', 'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}', 'pic' => 'slamet', 'source_file_name' => 'b1.xlsx', 'imported_at' => now()]);
    $d1 = Document::create(['client_id' => $c1->id, 'type' => 'resi', 'original_filename' => 'a.jpg', 'bucket_path' => 'a.jpg', 'uploaded_by' => $admin->id, 'batch_id' => $batch->id]);
    DocumentDownload::create(['client_id' => $c1->id, 'document_id' => $d1->id, 'batch_id' => $batch->id, 'source' => 'live_tracked']);

    $c2 = Client::create(['no_jp' => 'JP202', 'name' => 'Client B', 'name_normalized' => 'CLIENT B', 'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}', 'pic' => 'slamet', 'source_file_name' => 'b1.xlsx', 'imported_at' => now()]);
    $d2 = Document::create(['client_id' => $c2->id, 'type' => 'resi', 'original_filename' => 'b.jpg', 'bucket_path' => 'b.jpg', 'uploaded_by' => $admin->id, 'batch_id' => $batch->id]);
    DocumentDownload::create(['client_id' => $c2->id, 'document_id' => $d2->id, 'batch_id' => $batch->id, 'source' => 'notified_only']);

    $c3 = Client::create(['no_jp' => 'JP203', 'name' => 'Client C', 'name_normalized' => 'CLIENT C', 'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}', 'pic' => 'slamet', 'source_file_name' => 'b1.xlsx', 'imported_at' => now()]);
    Document::create(['client_id' => $c3->id, 'type' => 'resi', 'original_filename' => 'c.jpg', 'bucket_path' => 'c.jpg', 'uploaded_by' => $admin->id, 'batch_id' => $batch->id]);

    // Test filter 'downloaded'
    Livewire::actingAs($admin)
        ->test(Datatable::class)
        ->set('filterStatus', 'downloaded')
        ->assertSee('Client A')
        ->assertDontSee('Client B')
        ->assertDontSee('Client C');

    // Test filter 'seeded'
    Livewire::actingAs($admin)
        ->test(Datatable::class)
        ->set('filterStatus', 'seeded')
        ->assertSee('Client B')
        ->assertDontSee('Client A')
        ->assertDontSee('Client C');

    // Test filter 'not_downloaded'
    Livewire::actingAs($admin)
        ->test(Datatable::class)
        ->set('filterStatus', 'not_downloaded')
        ->assertSee('Client C')
        ->assertDontSee('Client A')
        ->assertDontSee('Client B');
});

test('document type filter correctly restricts records', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPER_ADMIN);

    $c1 = Client::create(['no_jp' => 'JP301', 'name' => 'Client ResiOnly', 'name_normalized' => 'CLIENT RESIONLY', 'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}', 'pic' => 'slamet', 'source_file_name' => 'b1.xlsx', 'imported_at' => now()]);
    Document::create(['client_id' => $c1->id, 'type' => 'resi', 'original_filename' => 'a.jpg', 'bucket_path' => 'a.jpg', 'uploaded_by' => $admin->id]);

    $c2 = Client::create(['no_jp' => 'JP302', 'name' => 'Client KwitansiOnly', 'name_normalized' => 'CLIENT KWITANSIONLY', 'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}', 'pic' => 'slamet', 'source_file_name' => 'b1.xlsx', 'imported_at' => now()]);
    Document::create(['client_id' => $c2->id, 'type' => 'kwitansi', 'original_filename' => 'b.jpg', 'bucket_path' => 'b.jpg', 'uploaded_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(Datatable::class)
        ->set('documentType', 'resi')
        ->assertSee('Client ResiOnly')
        ->assertDontSee('Client KwitansiOnly');

    Livewire::actingAs($admin)
        ->test(Datatable::class)
        ->set('documentType', 'kwitansi')
        ->assertSee('Client KwitansiOnly')
        ->assertDontSee('Client ResiOnly');
});

test('excel export respects pic scoping and contains consistent status', function () {
    $sales = User::factory()->create(['name' => 'ainul', 'pic' => 'ainul']);
    $sales->assignRole(User::ROLE_SALES);

    $c1 = Client::create(['no_jp' => 'JP401', 'name' => 'Client Ainul', 'name_normalized' => 'CLIENT AINUL', 'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}', 'pic' => 'ainul', 'source_file_name' => 'b1.xlsx', 'imported_at' => now()]);
    Document::create(['client_id' => $c1->id, 'type' => 'resi', 'original_filename' => 'a.jpg', 'bucket_path' => 'a.jpg', 'uploaded_by' => $sales->id]);

    $c2 = Client::create(['no_jp' => 'JP402', 'name' => 'Client Slamet', 'name_normalized' => 'CLIENT SLAMET', 'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}', 'pic' => 'slamet', 'source_file_name' => 'b1.xlsx', 'imported_at' => now()]);
    Document::create(['client_id' => $c2->id, 'type' => 'resi', 'original_filename' => 'b.jpg', 'bucket_path' => 'b.jpg', 'uploaded_by' => $sales->id]);

    Livewire::actingAs($sales)
        ->test(Datatable::class)
        ->call('export', 'excel')
        ->assertStatus(200);

    // Verify through direct query invocation matching export
    $exportedData = Livewire::actingAs($sales)
        ->test(Datatable::class)
        ->instance()
        ->getQuery()
        ->get();

    expect($exportedData->pluck('name'))->toContain('Client Ainul');
    expect($exportedData->pluck('name'))->not->toContain('Client Slamet');

    // Assert that the compiled export view contains the data (not empty due to array cast bug)
    $viewHtml = view('app.monitoring-bukti-transfer.export', [
        'collection' => $exportedData,
        'request' => ['documentType' => 'all'],
    ])->render();

    expect($viewHtml)->toContain('Client Ainul');
    expect($viewHtml)->not->toContain('Client Slamet');
});

test('admin can filter datatable by pic', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPER_ADMIN);

    $cAinul = Client::create(['no_jp' => 'JP501', 'name' => 'Client AinulFilter', 'name_normalized' => 'CLIENT AINULFILTER', 'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}', 'pic' => 'ainul', 'source_file_name' => 'b1.xlsx', 'imported_at' => now()]);
    Document::create(['client_id' => $cAinul->id, 'type' => 'resi', 'original_filename' => 'a.jpg', 'bucket_path' => 'a.jpg', 'uploaded_by' => $admin->id]);

    $cSlamet = Client::create(['no_jp' => 'JP502', 'name' => 'Client SlametFilter', 'name_normalized' => 'CLIENT SLAMETFILTER', 'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}', 'pic' => 'slamet', 'source_file_name' => 'b1.xlsx', 'imported_at' => now()]);
    Document::create(['client_id' => $cSlamet->id, 'type' => 'resi', 'original_filename' => 'b.jpg', 'bucket_path' => 'b.jpg', 'uploaded_by' => $admin->id]);

    // When bulkPic is empty, both appear
    Livewire::actingAs($admin)
        ->test(Datatable::class)
        ->assertSee('Client AinulFilter')
        ->assertSee('Client SlametFilter');

    // When bulkPic is ainul, only Ainul appears
    Livewire::actingAs($admin)
        ->test(Datatable::class)
        ->set('bulkPic', 'ainul')
        ->assertSee('Client AinulFilter')
        ->assertDontSee('Client SlametFilter');

    // When bulkPic is slamet, only Slamet appears
    Livewire::actingAs($admin)
        ->test(Datatable::class)
        ->set('bulkPic', 'slamet')
        ->assertSee('Client SlametFilter')
        ->assertDontSee('Client AinulFilter');
});

test('admin excel export respects bulk pic filter', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPER_ADMIN);

    $cAinul = Client::create(['no_jp' => 'JP601', 'name' => 'Export Client Ainul', 'name_normalized' => 'EXPORT CLIENT AINUL', 'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}', 'pic' => 'ainul', 'source_file_name' => 'b1.xlsx', 'imported_at' => now()]);
    Document::create(['client_id' => $cAinul->id, 'type' => 'resi', 'original_filename' => 'a.jpg', 'bucket_path' => 'a.jpg', 'uploaded_by' => $admin->id]);

    $cSlamet = Client::create(['no_jp' => 'JP602', 'name' => 'Export Client Slamet', 'name_normalized' => 'EXPORT CLIENT SLAMET', 'dob' => '1990-01-01', 'claim_type' => 'speed', 'status' => 'active', 'raw_data' => '{}', 'pic' => 'slamet', 'source_file_name' => 'b1.xlsx', 'imported_at' => now()]);
    Document::create(['client_id' => $cSlamet->id, 'type' => 'resi', 'original_filename' => 'b.jpg', 'bucket_path' => 'b.jpg', 'uploaded_by' => $admin->id]);

    $component = Livewire::actingAs($admin)
        ->test(Datatable::class)
        ->set('bulkPic', 'ainul');

    $exportedData = $component->instance()->getQuery()->get();

    expect($exportedData->pluck('name'))->toContain('Export Client Ainul');
    expect($exportedData->pluck('name'))->not->toContain('Export Client Slamet');

    $viewHtml = view('app.monitoring-bukti-transfer.export', [
        'collection' => $exportedData,
        'request' => ['documentType' => 'all', 'pic' => 'ainul'],
    ])->render();

    expect($viewHtml)->toContain('Export Client Ainul');
    expect($viewHtml)->not->toContain('Export Client Slamet');
});
