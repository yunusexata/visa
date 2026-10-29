<?php

use App\Enums\Form\VisaFormLinkStatus;
use App\Livewire\Form\VisaFormSubmission;
use App\Models\Form\VisaFormLink;
use Livewire\Livewire;
use Vinkla\Hashids\Facades\Hashids;

test('invalid hashid displays link not found state', function () {
    Livewire::test(VisaFormSubmission::class, ['hashid' => 'invalid-hash-123'])
        ->assertSet('linkState', 'not_found')
        ->assertSee('Tautan Tidak Dikenal');
});

test('expired magic link displays expired state', function () {
    $link = VisaFormLink::create([
        'name' => 'Klien Kadaluarsa',
        'password' => 'password123',
        'max_usage' => 1,
        'expired_at' => now()->subDay(),
        'status' => VisaFormLinkStatus::STATUS_ACTIVE,
    ]);

    $hashid = Hashids::encode($link->id);

    Livewire::test(VisaFormSubmission::class, ['hashid' => $hashid])
        ->assertSet('linkState', 'expired')
        ->assertSee('Tautan Telah Kadaluarsa');
});

test('exhausted magic link displays exhausted state', function () {
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
        ->assertSet('linkState', 'exhausted')
        ->assertSee('Kuota Pengisian Telah Selesai');
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

    Livewire::test(VisaFormSubmission::class, ['hashid' => $hashid])
        ->set('nama_lengkap', 'BUDI SANTOSO')
        ->set('declaration_agreed', false)
        ->call('submit')
        ->assertHasErrors('declaration_agreed')
        ->set('declaration_agreed', true)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertDispatched('clear-form-draft')
        ->assertRedirect(route('form.upload-document', ['link' => $hashid]));

    expect($link->fresh()->used_count)->toBe(1);
});
