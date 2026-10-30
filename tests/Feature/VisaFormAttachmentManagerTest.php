<?php

use App\Enums\Form\VisaAttachmenStatus;
use App\Enums\Form\VisaAttachmentType;
use App\Enums\Form\VisaFormStatus;
use App\Livewire\Form\VisaFormAttachmentManager;
use App\Models\Form\VisaForm;
use App\Models\Form\VisaFormAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');
    Role::firstOrCreate(['name' => User::ROLE_SUPERADMIN]);

    $this->visaForm = VisaForm::factory()->create([
        'nama_lengkap' => 'NARUTO UZUMAKI',
        'status' => VisaFormStatus::DIAJUKAN,
        'nomor_paspor' => 'B1234567',
        'nomor_coe' => 'COE-9999',
    ]);
});

test('guest cannot access visa form attachments page', function () {
    $response = $this->get(route('visa_form.attachments', ['id' => $this->visaForm->id]));

    $response->assertRedirect('/login');
});

test('superadmin can access attachments page and render livewire component', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPERADMIN);

    $response = $this->actingAs($admin)->get(route('visa_form.attachments', ['id' => $this->visaForm->id]));

    $response->assertOk();
    $response->assertSeeLivewire(VisaFormAttachmentManager::class);
    $response->assertSee('NARUTO UZUMAKI');
    $response->assertSee('B1234567');
});

test('attachment manager renders existing attachments', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPERADMIN);

    $attachment = VisaFormAttachment::create([
        'visa_form_id' => $this->visaForm->id,
        'jenis_dokumen' => VisaAttachmentType::PASPOR,
        'nama_file_asli' => 'paspor-asli-naruto.jpg',
        'nama_file_tersimpan' => 'stored_paspor.jpg',
        'path' => "visa-documents/{$this->visaForm->id}/paspor/stored_paspor.jpg",
        'disk' => 'public',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 102400,
        'status' => VisaAttachmenStatus::STATUS_STORED,
        'urutan' => 1,
    ]);

    Livewire::actingAs($admin)
        ->test(VisaFormAttachmentManager::class, ['visaFormId' => $this->visaForm->id])
        ->assertSee('paspor-asli-naruto.jpg')
        ->assertSee('Photo Paspor')
        ->assertSee('Cetak Berkas (Print)')
        ->assertSee('Cetak Dokumen Ini');
});

test('attachment manager shows print button for image files and modal header', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPERADMIN);

    $attachment = VisaFormAttachment::create([
        'visa_form_id' => $this->visaForm->id,
        'jenis_dokumen' => VisaAttachmentType::PASPOR,
        'nama_file_asli' => 'foto_ktp.jpg',
        'nama_file_tersimpan' => 'stored_foto_ktp.jpg',
        'path' => "visa-documents/{$this->visaForm->id}/paspor/stored_foto_ktp.jpg",
        'disk' => 'public',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 102400,
        'status' => VisaAttachmenStatus::STATUS_STORED,
        'urutan' => 1,
    ]);

    Livewire::actingAs($admin)
        ->test(VisaFormAttachmentManager::class, ['visaFormId' => $this->visaForm->id])
        ->assertSee('Cetak Berkas (Print)')
        ->assertSee('Cetak Dokumen Ini');
});

test('attachment manager renders category filter tabs with alpine directives without wire:click', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPERADMIN);

    Livewire::actingAs($admin)
        ->test(VisaFormAttachmentManager::class, ['visaFormId' => $this->visaForm->id])
        ->assertSee("activeCategory = 'all'", false)
        ->assertSee('Semua Dokumen')
        ->assertDontSee('wire:click="setCategory', false);
});

test('attachment manager allows superadmin to upload new attachment', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPERADMIN);

    $fakeFile = UploadedFile::fake()->image('scan-ktp.png', 600, 400)->size(200);

    Livewire::actingAs($admin)
        ->test(VisaFormAttachmentManager::class, ['visaFormId' => $this->visaForm->id])
        ->set('uploadType', 'ktp')
        ->set('uploadedFiles', [$fakeFile])
        ->set('catatan', 'KTP scan jernih')
        ->call('saveUpload')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('visa_form_attachments', [
        'visa_form_id' => $this->visaForm->id,
        'jenis_dokumen' => VisaAttachmentType::KTP->value,
        'nama_file_asli' => 'scan-ktp.png',
        'catatan' => 'KTP scan jernih',
    ]);
});

test('attachment manager allows superadmin to delete attachment', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_SUPERADMIN);

    $disk = 'public';
    $filePath = "visa-documents/{$this->visaForm->id}/coe/test_coe.pdf";
    Storage::disk($disk)->put($filePath, 'fake-pdf-content');

    $attachment = VisaFormAttachment::create([
        'visa_form_id' => $this->visaForm->id,
        'jenis_dokumen' => VisaAttachmentType::COE,
        'nama_file_asli' => 'coe_doc.pdf',
        'nama_file_tersimpan' => 'test_coe.pdf',
        'path' => $filePath,
        'disk' => $disk,
        'extension' => 'pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 5000,
        'status' => VisaAttachmenStatus::STATUS_STORED,
        'urutan' => 1,
    ]);

    expect(Storage::disk($disk)->exists($filePath))->toBeTrue();

    Livewire::actingAs($admin)
        ->test(VisaFormAttachmentManager::class, ['visaFormId' => $this->visaForm->id])
        ->call('deleteAttachment', $attachment->id)
        ->assertDispatched('SwalInfo');

    $this->assertSoftDeleted('visa_form_attachments', [
        'id' => $attachment->id,
    ]);

    expect(Storage::disk($disk)->exists($filePath))->toBeFalse();
});
