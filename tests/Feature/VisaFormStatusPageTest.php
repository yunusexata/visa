<?php

use App\Enums\Form\VisaFormLinkStatus;
use App\Enums\Form\VisaFormStatus;
use App\Livewire\Form\VisaFormStatusPage;
use App\Models\Form\VisaForm;
use App\Models\Form\VisaFormLink;
use Livewire\Livewire;
use Vinkla\Hashids\Facades\Hashids;

test('invalid hashid renders 404 not found status', function () {
    Livewire::test(VisaFormStatusPage::class, ['hashid' => 'random-invalid-hash'])
        ->assertSet('statusType', 'not_found')
        ->assertSee('404')
        ->assertSee('Tautan Formulir Tidak Ditemukan')
        ->assertSee('/images/error/404.svg');
});

test('expired link renders 410 expired status and expiration info', function () {
    $expiredDate = now()->subDays(2);
    $link = VisaFormLink::create([
        'name' => 'Budi Pratama',
        'pic_code' => 'PIC-JAKARTA',
        'max_usage' => 1,
        'used_count' => 0,
        'expired_at' => $expiredDate,
        'status' => VisaFormLinkStatus::STATUS_ACTIVE,
    ]);

    $hashid = Hashids::encode($link->id);

    Livewire::test(VisaFormStatusPage::class, ['hashid' => $hashid])
        ->assertSet('statusType', 'expired')
        ->assertSee('410')
        ->assertSee('Masa Berlaku Pengisian Telah Berakhir')
        ->assertSee('Budi Pratama')
        ->assertSee('PIC-JAKARTA');
});

test('exhausted link renders 429 quota reached status', function () {
    $link = VisaFormLink::create([
        'name' => 'Siti Rahma',
        'pic_code' => 'PIC-SURABAYA',
        'max_usage' => 1,
        'used_count' => 1,
        'expired_at' => now()->addDays(3),
        'status' => VisaFormLinkStatus::STATUS_SUCCESS,
    ]);

    $hashid = Hashids::encode($link->id);

    Livewire::test(VisaFormStatusPage::class, ['hashid' => $hashid])
        ->assertSet('statusType', 'exhausted')
        ->assertSee('429')
        ->assertSee('Batas Pengisian Formulir Telah Tercapai')
        ->assertSee('Siti Rahma');
});

test('exhausted link renders detail submission data when form exists', function () {
    $link = VisaFormLink::create([
        'name' => 'Ahmad Dahlan',
        'pic_code' => 'PIC-BALI',
        'max_usage' => 1,
        'used_count' => 1,
        'expired_at' => now()->addDays(5),
        'status' => VisaFormLinkStatus::STATUS_SUCCESS,
    ]);

    $visaForm = VisaForm::create([
        'visa_form_link_id' => $link->id,
        'nomor_registrasi' => 'REG-TEST-9988',
        'nama_lengkap' => 'Ahmad Dahlan',
        'nik_ktp' => '3201011122330001',
        'email_aktif' => 'ahmad@example.com',
        'nomor_paspor' => 'X9876543',
        'nomor_coe' => 'COE-776655',
        'nama_kaisha' => 'Yamaha Corp Tokyo',
        'status' => VisaFormStatus::DRAFT,
        'tanggal_diajukan' => now(),
    ]);

    $hashid = Hashids::encode($link->id);

    Livewire::test(VisaFormStatusPage::class, ['hashid' => $hashid])
        ->assertSet('statusType', 'exhausted')
        ->assertSee('Data Rincian Pengajuan Formulir')
        ->assertSee('REG-TEST-9988')
        ->assertSee('Ahmad Dahlan')
        ->assertSee('X9876543')
        ->assertSee('COE-776655')
        ->assertSee('Yamaha Corp Tokyo')
        ->assertSee('Lanjutkan Unggah Dokumen');
});

test('inactive link renders 403 inactive status', function () {
    $link = VisaFormLink::create([
        'name' => 'Klien Nonaktif',
        'max_usage' => 1,
        'used_count' => 0,
        'expired_at' => now()->addDays(5),
        'status' => VisaFormLinkStatus::STATUS_INACTIVE,
    ]);

    $hashid = Hashids::encode($link->id);

    Livewire::test(VisaFormStatusPage::class, ['hashid' => $hashid])
        ->assertSet('statusType', 'inactive')
        ->assertSee('403')
        ->assertSee('Tautan Formulir Dinonaktifkan');
});
