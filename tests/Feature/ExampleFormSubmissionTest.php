<?php

use App\Helpers\Alert;
use App\Livewire\Client\ExampleFormSubmission;
use App\Models\MasterData\Regency;
use Livewire\Livewire;

test('example form submission route is accessible without authentication', function () {
    $response = $this->get(route('example-form'));

    $response->assertOk();
    $response->assertSeeLivewire(ExampleFormSubmission::class);
});

test('example form route alias redirects to example-form', function () {
    $response = $this->get('/example/form');

    $response->assertRedirect(route('example-form'));
});

test('example form component renders all required form sections and does not have reset button', function () {
    Livewire::test(ExampleFormSubmission::class)
        ->assertOk()
        ->assertSee('Portal Visa')
        ->assertSee('Identitas Diri Pemohon')
        ->assertSee('Tempat Lahir')
        ->assertSee('Data di Paspor')
        ->assertSee('Certificate of Eligibility (COE)')
        ->assertSee('Rencana Kedatangan')
        ->assertSee('Ringkasan & Konfirmasi Pengajuan', false)
        ->assertDontSee('Reset Form')
        ->assertDontSee('restart_alt');
});

test('example form can load regencies through loadRegencies method', function () {
    Regency::create(['name' => 'KOTA SURABAYA']);
    Regency::create(['name' => 'KABUPATEN BOGOR']);

    $component = Livewire::test(ExampleFormSubmission::class);
    $regencies = $component->instance()->loadRegencies();

    expect($regencies)->toContain('KOTA SURABAYA');
    expect($regencies)->toContain('KABUPATEN BOGOR');
});

test('example form nextStep records step history and dispatches consoleLog and step-advanced', function () {
    $stepPayload = [
        'nama_lengkap' => 'BUDI SANTOSO',
        'tempat_lahir' => 'KOTA SURABAYA',
        'nik_ktp'      => '3578012345670001',
    ];

    Livewire::test(ExampleFormSubmission::class)
        ->call('nextStep', 1, $stepPayload)
        ->assertDispatched(Alert::EVENT_CONSOLE_LOG)
        ->assertDispatched('step-advanced', step: 2)
        ->assertSet('stepHistory.1.data.nama_lengkap', 'BUDI SANTOSO');
});

test('example form submission requires declaration agreement before submitting', function () {
    Livewire::test(ExampleFormSubmission::class)
        ->set('declaration_agreed', false)
        ->call('submit')
        ->assertHasErrors(['declaration_agreed'])
        ->assertSet('is_submitted', false);
});

test('example form submission succeeds when declaration is agreed and dispatches clear-form-draft and SwalInfo', function () {
    Livewire::test(ExampleFormSubmission::class)
        ->set('nama_lengkap', 'BUDI SANTOSO')
        ->set('nik_ktp', '3578012345670001')
        ->set('nomor_paspor', 'C1234567')
        ->set('nomor_coe', 'COE-998877')
        ->set('declaration_agreed', true)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('is_submitted', true)
        ->assertDispatched('clear-form-draft')
        ->assertDispatched(Alert::EVENT_CONSOLE_LOG)
        ->assertDispatched('SwalInfo');
});
