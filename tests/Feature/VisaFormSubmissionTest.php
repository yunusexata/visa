<?php

use App\Enums\Form\VisaFormLinkStatus;
use App\Enums\Form\VisaFormStatus;
use App\Livewire\Form\VisaFormSubmission;
use App\Models\Form\VisaForm;
use App\Models\Form\VisaFormLink;
use Livewire\Livewire;
use Vinkla\Hashids\Facades\Hashids;

test('invalid hashid redirects to form status page', function () {
    Livewire::test(VisaFormSubmission::class, ['hashid' => 'invalid-hash-123'])
        ->assertRedirect(route('form.status', ['hashid' => 'invalid-hash-123']));
});

test('expired magic link redirects to form status page', function () {
    $link = VisaFormLink::create([
        'name' => 'Klien Kadaluarsa',
        'password' => 'password123',
        'max_usage' => 1,
        'expired_at' => now()->subDay(),
        'status' => VisaFormLinkStatus::STATUS_ACTIVE,
    ]);

    $hashid = Hashids::encode($link->id);

    Livewire::test(VisaFormSubmission::class, ['hashid' => $hashid])
        ->assertRedirect(route('form.status', ['hashid' => $hashid]));
});

test('exhausted magic link redirects to form status page', function () {
    $link = VisaFormLink::create([
        'name' => 'Klien Selesai',
        'password' => 'password123',
        'max_usage' => 1,
        'used_count' => 1,
        'expired_at' => now()->addDays(5),
        'status' => VisaFormLinkStatus::STATUS_ACTIVE,
    ]);

    $hashid = Hashids::encode($link->id);

    Livewire::test(VisaFormSubmission::class, ['hashid' => $hashid])
        ->assertRedirect(route('form.status', ['hashid' => $hashid]));
});

test('protected magic link prompts for password and unlocks on correct password', function () {
    $link = VisaFormLink::create([
        'name' => 'Budi Santoso',
        'password' => 'rahasia123',
        'max_usage' => 1,
        'expired_at' => now()->addDays(5),
        'status' => VisaFormLinkStatus::STATUS_ACTIVE,
    ]);

    $hashid = Hashids::encode($link->id);

    Livewire::test(VisaFormSubmission::class, ['hashid' => $hashid])
        ->assertSet('linkState', 'valid')
        ->assertSet('isAuthorized', false)
        ->assertSee('Masukkan Kata Sandi')
        ->assertSee('Budi Santoso')
        ->set('inputPassword', 'wrong-pass')
        ->call('verifyPassword')
        ->assertHasErrors('inputPassword')
        ->assertSet('isAuthorized', false)
        ->set('inputPassword', 'rahasia123')
        ->call('verifyPassword')
        ->assertHasNoErrors()
        ->assertSet('isAuthorized', true)
        ->assertDispatched('password-authorized')
        ->assertSee('Portal Visa')
        ->assertSee('Langkah 1: Identitas Diri Pemohon');
});

test('unprotected magic link bypasses password check directly', function () {
    $link = VisaFormLink::create([
        'name' => 'Klien Tanpa Password',
        'password' => null,
        'max_usage' => 1,
        'expired_at' => now()->addDays(5),
        'status' => VisaFormLinkStatus::STATUS_ACTIVE,
    ]);

    $hashid = Hashids::encode($link->id);

    Livewire::test(VisaFormSubmission::class, ['hashid' => $hashid])
        ->assertSet('linkState', 'valid')
        ->assertSet('isAuthorized', true)
        ->assertSee('Portal Visa')
        ->assertDontSee('Masukkan Kata Sandi');
});

test('step advancement dispatches step-advanced and records step history', function () {
    $link = VisaFormLink::create([
        'name' => 'Klien Step Test',
        'password' => null,
        'max_usage' => 1,
        'expired_at' => now()->addDays(5),
        'status' => VisaFormLinkStatus::STATUS_ACTIVE,
    ]);

    $hashid = Hashids::encode($link->id);

    Livewire::test(VisaFormSubmission::class, ['hashid' => $hashid])
        ->call('nextStep', 1, [
            'nama_lengkap' => 'BUDI SANTOSO',
            'tempat_lahir' => 'KOTA SURABAYA',
        ])
        ->assertDispatched('step-advanced', step: 2);
});

test('submitting form increments link used_count and redirects to upload document', function () {
    $link = VisaFormLink::create([
        'name' => 'Klien Final Submit',
        'password' => null,
        'max_usage' => 1,
        'used_count' => 0,
        'expired_at' => now()->addDays(5),
        'status' => VisaFormLinkStatus::STATUS_ACTIVE,
    ]);

    $hashid = Hashids::encode($link->id);

    $test = Livewire::test(VisaFormSubmission::class, ['hashid' => $hashid])
        ->set('nama_lengkap', 'BUDI SANTOSO')
        ->set('jenis_kelamin', 'L')
        ->set('status_kawin', 'kawin')
        ->set('jenis_kelamin_penanggung_jawab', 'P')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertDispatched('clear-form-draft');

    $visaForm = VisaForm::latest('id')->first();
    expect($visaForm)->not->toBeNull();
    $expectedFormHashid = Hashids::encode($visaForm->id);

    $test->assertRedirect(route('form.upload-document', ['hashid' => $expectedFormHashid]));

    expect($link->fresh()->used_count)->toBe(1);
    expect($visaForm->status)->toBe(VisaFormStatus::DRAFT);
    expect($visaForm->jenis_kelamin)->toBe('L');
    expect($visaForm->status_kawin)->toBe('kawin');
    expect($visaForm->jenis_kelamin_penanggung_jawab)->toBe('P');
});

test('restoreDraft restores draft fields into component properties', function () {
    $link = VisaFormLink::create([
        'name' => 'Klien Draft Test',
        'password' => null,
        'max_usage' => 1,
        'expired_at' => now()->addDays(5),
        'status' => VisaFormLinkStatus::STATUS_ACTIVE,
    ]);

    $hashid = Hashids::encode($link->id);

    Livewire::test(VisaFormSubmission::class, ['hashid' => $hashid])
        ->call('restoreDraft', [
            'nama_lengkap' => 'BUDI SANTOSO',
            'tempat_lahir' => 'KOTA SURABAYA',
            'jenis_kelamin' => 'L',
            'status_kawin' => 'kawin',
            'nik_ktp' => '3578010101010001',
            'nomor_paspor' => 'X1234567',
            'bandara_tujuan' => 'Osaka / Kansai (KIX)',
            'nama_kaisha' => 'TOYOTA MOTOR CORP',
            'pernah_kerja_jepang' => 'pernah',
            'periode_kerja_mulai' => '2021-04',
            'periode_kerja_selesai' => '2024-03',
            'jenis_kelamin_penanggung_jawab' => 'P',
        ])
        ->assertSet('nama_lengkap', 'BUDI SANTOSO')
        ->assertSet('tempat_lahir', 'KOTA SURABAYA')
        ->assertSet('jenis_kelamin', 'L')
        ->assertSet('status_kawin', 'kawin')
        ->assertSet('nik_ktp', '3578010101010001')
        ->assertSet('nomor_paspor', 'X1234567')
        ->assertSet('bandara_tujuan', 'Osaka / Kansai (KIX)')
        ->assertSet('nama_kaisha', 'TOYOTA MOTOR CORP')
        ->assertSet('pernah_kerja_jepang', 'pernah')
        ->assertSet('periode_kerja_mulai', '2021-04')
        ->assertSet('periode_kerja_selesai', '2024-03')
        ->assertSet('jenis_kelamin_penanggung_jawab', 'P');
});

test('form renders select for bandara_tujuan, gender, and marital status and excludes country code badges', function () {
    $link = VisaFormLink::create([
        'name' => 'Klien Select Test',
        'password' => null,
        'max_usage' => 1,
        'expired_at' => now()->addDays(5),
        'status' => VisaFormLinkStatus::STATUS_ACTIVE,
    ]);

    $hashid = Hashids::encode($link->id);

    Livewire::test(VisaFormSubmission::class, ['hashid' => $hashid])
        ->assertSeeHtml('<select id="bandara_tujuan"')
        ->assertSeeHtml('value="Haneda (HND)"')
        ->assertSeeHtml('value="Narita (NRT)"')
        ->assertSeeHtml('value="Lainnya"')
        ->assertSeeHtml('<select id="jenis_kelamin"')
        ->assertSeeHtml('<select id="status_kawin"')
        ->assertSeeHtml('<select id="jenis_kelamin_penanggung_jawab"')
        ->assertDontSeeHtml('🇮🇩 +62')
        ->assertDontSeeHtml('🇯🇵 +81');
});
