<?php

use App\Models\Client\Client;
use App\Services\DocumentMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('matchFilename behaves identically with preloaded collection vs allowed client IDs', function () {
    // Run seeders to set up roles and permissions
    (new \Database\Seeders\User\PermissionSeeder())->run();
    (new \Database\Seeders\User\RoleSeeder())->run();

    // Create reference clients in sqlite memory
    $client1 = Client::create([
        'no_jp' => 'JP111',
        'name' => 'Budi Santoso',
        'name_normalized' => 'budi santoso',
        'dob' => '1990-01-01',
        'phone' => '081234567890',
        'claim_type' => 'normal',
        'status' => 'pending',
        'raw_data' => '{}',
        'source_file_name' => 'ref.xlsx',
        'source_file_disk' => 'local',
        'source_file_path' => 'ref.xlsx',
        'imported_at' => now(),
        'pic' => 'sg',
    ]);

    $client2 = Client::create([
        'no_jp' => 'JP222',
        'name' => 'Siti Aminah',
        'name_normalized' => 'siti aminah',
        'dob' => '1992-02-02',
        'phone' => '081234567891',
        'claim_type' => 'normal',
        'status' => 'pending',
        'raw_data' => '{}',
        'source_file_name' => 'ref.xlsx',
        'source_file_disk' => 'local',
        'source_file_path' => 'ref.xlsx',
        'imported_at' => now(),
        'pic' => 'sg',
    ]);

    $service = new DocumentMatchingService();

    // 1. Exact Match Test
    $filename1 = '1_Budi Santoso.jpg';
    $resOld1 = $service->matchFilename($filename1, [$client1->id, $client2->id], 'ichijikin');
    
    $preloaded = Client::whereIn('id', [$client1->id, $client2->id])->get();
    $resNew1 = $service->matchFilename($filename1, $preloaded, 'ichijikin');

    expect($resNew1['status'])->toBe($resOld1['status']);
    expect($resNew1['matched_client']->id)->toBe($resOld1['matched_client']->id);
    expect($resNew1['confidence'])->toBe($resOld1['confidence']);

    // 2. Fuzzy Match Test (similar but not identical name)
    $filename2 = '1_Budi_Santoso_Extra.jpg';
    $resOld2 = $service->matchFilename($filename2, [$client1->id, $client2->id], 'ichijikin');
    $resNew2 = $service->matchFilename($filename2, $preloaded, 'ichijikin');

    expect($resNew2['status'])->toBe($resOld2['status']);
    expect($resNew2['confidence'])->toBe($resOld2['confidence']);
    
    // Explicitly compare client details of match outcomes
    if ($resOld2['matched_client'] !== null) {
        expect($resNew2['matched_client'])->not->toBeNull();
        expect($resNew2['matched_client']->id)->toBe($resOld2['matched_client']->id);
    } else {
        expect($resNew2['matched_client'])->toBeNull();
    }

    if ($resOld2['best_candidate'] !== null) {
        expect($resNew2['best_candidate'])->not->toBeNull();
        expect($resNew2['best_candidate']->id)->toBe($resOld2['best_candidate']->id);
    } else {
        expect($resNew2['best_candidate'])->toBeNull();
    }
});

test('matchFilename matches correctly based on document type patterns', function () {
    // Run seeders to set up roles and permissions
    (new \Database\Seeders\User\PermissionSeeder())->run();
    (new \Database\Seeders\User\RoleSeeder())->run();

    $client = Client::create([
        'no_jp' => 'JP333',
        'name' => 'John Doe',
        'name_normalized' => 'john doe',
        'dob' => '1985-05-05',
        'claim_type' => 'normal',
        'status' => 'pending',
        'raw_data' => '{}',
        'source_file_name' => 'ref.xlsx',
        'imported_at' => now(),
        'pic' => 'sg',
    ]);

    $client2 = Client::create([
        'no_jp' => 'JP444',
        'name' => 'Aan Ulin Nuha',
        'name_normalized' => 'aan ulin nuha',
        'dob' => '1988-08-08',
        'claim_type' => 'normal',
        'status' => 'pending',
        'raw_data' => '{}',
        'source_file_name' => 'ref.xlsx',
        'imported_at' => now(),
        'pic' => 'sg',
    ]);

    $client3 = Client::create([
        'no_jp' => 'JP555',
        'name' => 'Yuda Maredo Manullang',
        'name_normalized' => 'yuda maredo manullang',
        'dob' => '1989-09-09',
        'claim_type' => 'normal',
        'status' => 'pending',
        'raw_data' => '{}',
        'source_file_name' => 'ref.xlsx',
        'imported_at' => now(),
        'pic' => 'sg',
    ]);

    $service = new DocumentMatchingService();
    $preloaded = Client::all();

    // Test pattern Ichijikin: Old pattern ({angka}_{NAMA LENGKAP})
    $resIchijikinOld = $service->matchFilename('123_John Doe.jpg', $preloaded, 'ichijikin');
    expect($resIchijikinOld['status'])->toBe('matched');
    expect($resIchijikinOld['matched_client']->name)->toBe('John Doe');

    // Test pattern Ichijikin: New pattern ({NAMA LENGKAP}_{NO NENKIN})
    $resIchijikinNew = $service->matchFilename('AAN ULIN NUHA_3858043907.jpg', $preloaded, 'ichijikin');
    expect($resIchijikinNew['status'])->toBe('matched');
    expect($resIchijikinNew['matched_client']->name)->toBe('Aan Ulin Nuha');

    // Test pattern Resi: Old pattern ({kode}_{BANK}_{NAMA LENGKAP}_{angka})
    $resResiOld = $service->matchFilename('ABC_BCA_John Doe_999.jpg', $preloaded, 'resi');
    expect($resResiOld['status'])->toBe('matched');
    expect($resResiOld['matched_client']->name)->toBe('John Doe');

    // Test pattern Resi: New pattern ([PREFIX]_[BANK]_[NAMA LENGKAP]_[NOMOR])
    $resResiNew = $service->matchFilename('SPEED JUN 26 KL2_BRI_YUDA MAREDO MANULLANG_736301024206536.jpg', $preloaded, 'resi');
    expect($resResiNew['status'])->toBe('matched');
    expect($resResiNew['matched_client']->name)->toBe('Yuda Maredo Manullang');

    // Test pattern Kwitansi: {angka}_{tanggal}_{NAMA LENGKAP}_NENKIN_{tipe}.jpg (unmodified)
    $resKwitansi = $service->matchFilename('456_12-12-2023_John Doe_NENKIN_final.jpg', $preloaded, 'kwitansi');
    expect($resKwitansi['status'])->toBe('matched');
    expect($resKwitansi['matched_client']->name)->toBe('John Doe');
});
