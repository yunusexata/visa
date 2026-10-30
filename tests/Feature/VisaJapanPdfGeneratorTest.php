<?php

use App\Enums\Form\VisaFormStatus;
use App\Livewire\Form\Datatable;
use App\Models\Form\VisaForm;
use App\Models\User;
use App\Services\Form\VisaJapanPdfService;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => User::ROLE_SUPERADMIN]);

    $this->visaForm = VisaForm::factory()->create([
        'nama_lengkap' => 'NARUTO UZUMAKI',
        'status' => VisaFormStatus::DIAJUKAN,
        'nomor_paspor' => 'B1234567',
        'nomor_coe' => 'COE-9999',
        'nik_ktp' => '3201010101900001',
        'tanggal_lahir' => '1990-01-01',
        'tempat_lahir' => 'JAKARTA',
        'kantor_paspor' => 'JAKARTA SELATAN',
        'tanggal_pengeluaran_paspor' => '2020-01-01',
        'tanggal_habis_berlaku_paspor' => '2030-01-01',
        'nama_kaisha' => 'HONDA MOTOR CO LTD',
        'status_kependudukan_jepang' => 'SPECIFIED SKILLED WORKER (i)',
        'lama_stay_jepang' => '1 YEAR',
        'tanggal_kedatangan' => '2026-12-01',
        'bandara_tujuan' => 'HANEDA AIRPORT',
        'maskapai' => 'GARUDA INDONESIA',
        'alamat_jepang' => '1-1 MINAMI-AOYAMA, MINATO-KU, TOKYO',
        'telepon_jepang' => '03-3423-1111',
        'alamat_indonesia' => 'JL. SUDIRMAN NO. 1, JAKARTA',
        'telepon_indonesia' => '081234567890',
        'email_aktif' => 'naruto@example.com',
    ]);
});

test('visa japan pdf service generates valid pdf from visa form', function () {
    $service = app(VisaJapanPdfService::class);
    $binary = $service->generate($this->visaForm);

    expect($binary)->toBeString();
    expect(str_starts_with($binary, '%PDF-'))->toBeTrue();
    expect(strlen($binary))->toBeGreaterThan(5000);
});

test('livewire datatable component triggers visa japan pdf download', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPERADMIN);

    $component = Livewire::actingAs($admin)
        ->test(Datatable::class)
        ->call('generateVisaJapanPdf', $this->visaForm->id);

    $safeNoReg = str_replace(['/', '\\'], '_', $this->visaForm->nomor_registrasi ?: (string) $this->visaForm->id);
    $expectedFilename = "Visa_Japan_Application_{$safeNoReg}.pdf";

    $component->assertFileDownloaded($expectedFilename);
});

test('datatable view contains download visa japan pdf button', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPERADMIN);

    Livewire::actingAs($admin)
        ->test(Datatable::class)
        ->assertSee('title="Download Form Visa Jepang (PDF)"', false)
        ->assertSee('picture_as_pdf');
});

test('controller route downloads visa japan pdf for authenticated user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPERADMIN);

    $response = $this->actingAs($admin)
        ->get(route('visa_form.generate_pdf', ['id' => $this->visaForm->id]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');

    $safeNoReg = str_replace(['/', '\\'], '_', $this->visaForm->nomor_registrasi ?: (string) $this->visaForm->id);
    $response->assertHeader('content-disposition', "attachment; filename=Visa_Japan_Application_{$safeNoReg}.pdf");
});

test('guest cannot access visa japan pdf download route', function () {
    $response = $this->get(route('visa_form.generate_pdf', ['id' => $this->visaForm->id]));

    $response->assertRedirect('/login');
});
