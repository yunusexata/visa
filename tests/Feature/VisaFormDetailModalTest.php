<?php

use App\Enums\Form\VisaFormStatus;
use App\Livewire\Form\Datatable;
use App\Livewire\Form\VisaFormDetailModal;
use App\Models\Form\VisaForm;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => User::ROLE_SUPERADMIN]);
});

test('detail modal opens with open-visa-form-detail event and loads data', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole(User::ROLE_SUPERADMIN);

    $form = VisaForm::factory()->create([
        'nama_lengkap' => 'KIRITO KAZUTO',
        'status' => VisaFormStatus::DIAJUKAN,
        'nomor_paspor' => 'PASSPORT123',
        'pic_code' => 'PIC-777',
        'jenis_kelamin' => 'L',
        'status_kawin' => 'kawin',
        'jenis_kelamin_penanggung_jawab' => 'P',
    ]);

    Livewire::actingAs($superadmin)
        ->test(VisaFormDetailModal::class)
        ->assertSet('isOpen', false)
        ->dispatch('open-visa-form-detail', id: $form->id)
        ->assertSet('isOpen', true)
        ->assertSet('visaFormId', $form->id)
        ->assertSet('status', VisaFormStatus::DIAJUKAN->value)
        ->assertSet('nama_lengkap', 'KIRITO KAZUTO')
        ->assertSet('nomor_paspor', 'PASSPORT123')
        ->assertSet('pic_code', 'PIC-777')
        ->assertSet('jenis_kelamin', 'L')
        ->assertSet('status_kawin', 'kawin')
        ->assertSet('jenis_kelamin_penanggung_jawab', 'P');
});

test('detail modal updates visa form status and applicant data and dispatches refresh', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole(User::ROLE_SUPERADMIN);

    $form = VisaForm::factory()->create([
        'nama_lengkap' => 'ASUNA YUUKI',
        'status' => VisaFormStatus::DIAJUKAN,
        'nomor_paspor' => 'P9999999',
        'jenis_kelamin' => 'P',
        'status_kawin' => 'belum kawin',
        'jenis_kelamin_penanggung_jawab' => 'L',
    ]);

    Livewire::actingAs($superadmin)
        ->test(VisaFormDetailModal::class)
        ->dispatch('open-visa-form-detail', id: $form->id)
        ->set('status', VisaFormStatus::SIAP_VERIFIKASI->value)
        ->set('nama_lengkap', 'ASUNA KIRIGAYA')
        ->set('jenis_kelamin', 'P')
        ->set('status_kawin', 'kawin')
        ->set('jenis_kelamin_penanggung_jawab', 'L')
        ->call('save')
        ->assertSet('isOpen', false)
        ->assertDispatched('datatable-refresh')
        ->assertDispatched('SwalInformation');

    $form->refresh();
    expect($form->status)->toBe(VisaFormStatus::SIAP_VERIFIKASI)
        ->and($form->nama_lengkap)->toBe('ASUNA KIRIGAYA')
        ->and($form->jenis_kelamin)->toBe('P')
        ->and($form->status_kawin)->toBe('kawin')
        ->and($form->jenis_kelamin_penanggung_jawab)->toBe('L');
});

test('visa form datatable renders edit button and does not render visibility button', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole(User::ROLE_SUPERADMIN);

    $form = VisaForm::factory()->create([
        'nama_lengkap' => 'SITAMA HERO',
        'status' => VisaFormStatus::VERIFIKASI,
    ]);

    Livewire::actingAs($superadmin)
        ->test(Datatable::class)
        ->assertSee('edit')
        ->assertSeeHtml("wire:click=\"\$dispatch('open-visa-form-detail', { id: {$form->id} })\"")
        ->assertDontSeeHtml('>visibility</span>')
        ->assertDontSee('Lihat Tanda Terima');
});

test('detail modal container has higher z-index than sidebar and sweetalert alert has highest z-index', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole(User::ROLE_SUPERADMIN);

    $form = VisaForm::factory()->create();

    Livewire::actingAs($superadmin)
        ->test(VisaFormDetailModal::class)
        ->dispatch('open-visa-form-detail', id: $form->id)
        ->assertSeeHtml('z-[100000]')
        ->assertSeeHtml('!z-[110000]');
});

test('detail modal normalizes real input for regency, phone codes, month inputs, and employment history', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole(User::ROLE_SUPERADMIN);

    $form = VisaForm::factory()->create([
        'tempat_lahir' => 'KABUPATEN BANJAR',
        'telepon_indonesia' => '0812-3456-7890',
        'telepon_jepang' => '03-3423-1111',
        'telepon_penanggung_jawab' => '080-1234-5678',
        'status_kependudukan_jepang' => 'Student (Ryugaku)',
        'lama_stay_jepang' => '1 Tahun',
        'bandara_tujuan' => 'Narita (NRT)',
        'pernah_kerja_jepang' => 'ya',
        'periode_kerja_mulai' => '2026-0700',
        'periode_kerja_selesai' => '2026-1200',
    ]);

    $component = Livewire::actingAs($superadmin)
        ->test(VisaFormDetailModal::class)
        ->dispatch('open-visa-form-detail', id: $form->id);

    // Verify phone retains number and hyphen format
    $component->assertSet('telepon_indonesia', '0812-3456-7890')
        ->assertSet('telepon_jepang', '03-3423-1111')
        ->assertSet('telepon_penanggung_jawab', '080-1234-5678')
        // Verify pernah kerja auto-select normalization
        ->assertSet('pernah_kerja_jepang', 'pernah')
        // Verify month input format (YYYY-MM)
        ->assertSet('periode_kerja_mulai', '2026-07')
        ->assertSet('periode_kerja_selesai', '2026-12')
        // Verify selects
        ->assertSet('status_kependudukan_jepang', 'Student (Ryugaku)')
        ->assertSet('lama_stay_jepang', '1 Tahun')
        ->assertSet('bandara_tujuan', 'Narita (NRT)')
        // Verify HTML does not contain country code badges (+62 and +81)
        ->assertDontSeeHtml('🇮🇩 +62')
        ->assertDontSeeHtml('🇯🇵 +81')
        ->assertSeeHtml('type="month"');

    // Test loadRegencies method
    expect($component->instance()->loadRegencies())->toBeArray();
});

test('detail modal validates phone numbers allow only digits and hyphens', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole(User::ROLE_SUPERADMIN);

    $form = VisaForm::factory()->create();

    $component = Livewire::actingAs($superadmin)
        ->test(VisaFormDetailModal::class)
        ->dispatch('open-visa-form-detail', id: $form->id);

    // Invalid phone with letters or invalid symbols
    $component->set('telepon_indonesia', '0812ABCD')
        ->call('save')
        ->assertHasErrors(['telepon_indonesia']);

    // Valid phone with digits and hyphens
    $component->set('telepon_indonesia', '0812-3456-7890')
        ->set('telepon_jepang', '03-3423-1111')
        ->set('telepon_penanggung_jawab', '080-1234-5678')
        ->call('save')
        ->assertHasNoErrors(['telepon_indonesia', 'telepon_jepang', 'telepon_penanggung_jawab']);
});
