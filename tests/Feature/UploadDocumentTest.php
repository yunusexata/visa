<?php

use App\Enums\Form\VisaAttachmenStatus;
use App\Enums\Form\VisaAttachmentType;
use App\Enums\Form\VisaFormStatus;
use App\Helpers\Alert;
use App\Livewire\Form\UploadDocument;
use App\Models\Form\VisaForm;
use App\Models\Form\VisaFormAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Vinkla\Hashids\Facades\Hashids;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');

    $this->visaForm = VisaForm::factory()->create([
        'status' => VisaFormStatus::DRAFT,
    ]);
    $this->hashid = Hashids::encode($this->visaForm->id);
});

test('upload document page is accessible and renders livewire component', function () {
    $response = $this->get(route('form.upload-document', ['hashid' => $this->hashid]));

    $response->assertOk();
    $response->assertSeeLivewire(UploadDocument::class);
});

test('example upload alias redirects to example-form', function () {
    $response = $this->get('/example/upload');

    $response->assertRedirect(route('example-form'));
});

test('all slots default to multiple true and required false', function () {
    $component = Livewire::test(UploadDocument::class, ['hashid' => $this->hashid]);
    $config = $component->instance()->getSlotConfig();

    $expectedSlots = ['coe', 'e_ktln', 'ktp', 'paspor', 'tiket', 'pas_photo'];

    foreach ($expectedSlots as $slot) {
        expect($config)->toHaveKey($slot);
        expect($config[$slot]['multiple'])->toBeTrue("Slot [{$slot}] should default to multiple = true");
        expect($config[$slot]['required'])->toBeFalse("Slot [{$slot}] should default to required = false");
    }
});

test('component renders all 6 document slots and badges', function () {
    Livewire::test(UploadDocument::class, ['hashid' => $this->hashid])
        ->assertOk()
        ->assertSee('Photo COE')
        ->assertSee('E-KTLN')
        ->assertSee('KTP Indonesia')
        ->assertSee('Photo Paspor')
        ->assertSee('Berkas Tiket')
        ->assertSee('Pas Photo Latar Putih')
        ->assertSee('auto-kompres')
        ->assertSee('Simpan Seluruh Dokumen');
});

test('backend validation rejects unsupported mime types', function () {
    $fakeFile = UploadedFile::fake()->create('malicious.exe', 500, 'application/x-msdownload');

    Livewire::test(UploadDocument::class, ['hashid' => $this->hashid])
        ->set('coe', [$fakeFile])
        ->call('processSlot', 'coe')
        ->assertHasErrors(['coe.0']);
});

test('backend validation accepts valid jpg image and dispatches attachment-saved and consoleLog', function () {
    $imageFile = UploadedFile::fake()->image('coe_dokumen.jpg', 800, 600)->size(400);

    Livewire::test(UploadDocument::class, ['hashid' => $this->hashid])
        ->set('coe', [$imageFile])
        ->call('processSlot', 'coe')
        ->assertHasNoErrors()
        ->assertDispatched('attachment-saved', type: 'coe', count: 1)
        ->assertDispatched(Alert::EVENT_CONSOLE_LOG);
});

test('backend validation accepts pdf file under 2mb for tiket slot', function () {
    $pdfFile = UploadedFile::fake()->create('tiket_flight.pdf', 1500, 'application/pdf');

    Livewire::test(UploadDocument::class, ['hashid' => $this->hashid])
        ->set('tiket', [$pdfFile])
        ->call('processSlot', 'tiket')
        ->assertHasNoErrors()
        ->assertDispatched('attachment-saved', type: 'tiket', count: 1);
});

test('backend validation rejects pdf file over 2mb', function () {
    // 2.5MB = 2560KB (melebihi batas 2048KB)
    $largePdf = UploadedFile::fake()->create('tiket_jumbo.pdf', 2600, 'application/pdf');

    Livewire::test(UploadDocument::class, ['hashid' => $this->hashid])
        ->set('tiket', [$largePdf])
        ->call('processSlot', 'tiket')
        ->assertHasErrors(['tiket.0']);
});

test('backend validation rejects pdf on image-only slot like ktp', function () {
    $pdfOnKtp = UploadedFile::fake()->create('ktp.pdf', 300, 'application/pdf');

    Livewire::test(UploadDocument::class, ['hashid' => $this->hashid])
        ->set('ktp', [$pdfOnKtp])
        ->call('processSlot', 'ktp')
        ->assertHasErrors(['ktp.0']);
});

test('buffer incomingSlot and incomingFile appends to slot without overwriting previous uploads', function () {
    $file1 = UploadedFile::fake()->image('paspor_hal_1.jpg')->size(350);
    $file2 = UploadedFile::fake()->image('paspor_hal_2.jpg')->size(420);

    $component = Livewire::test(UploadDocument::class, ['hashid' => $this->hashid])
        ->set('incomingSlot', 'paspor')
        ->set('incomingFile', $file1)
        ->assertDispatched('attachment-saved', type: 'paspor', count: 1);

    // Upload file kedua ke slot yang sama
    $component
        ->set('incomingSlot', 'paspor')
        ->set('incomingFile', $file2)
        ->assertDispatched('attachment-saved', type: 'paspor', count: 2);

    $pasporFiles = $component->get('paspor');
    expect($pasporFiles)->toBeArray();
    expect(count($pasporFiles))->toBe(2);
});

test('slot array preserves multiple uploaded files directly on coe slot', function () {
    $file1 = UploadedFile::fake()->image('coe_page1.jpg')->size(350);
    $file2 = UploadedFile::fake()->image('coe_page2.jpg')->size(420);
    $file3 = UploadedFile::fake()->image('coe_page3.jpg')->size(380);

    $component = Livewire::test(UploadDocument::class, ['hashid' => $this->hashid])
        ->set('coe', [$file1, $file2, $file3])
        ->call('processSlot', 'coe')
        ->assertDispatched('attachment-saved', type: 'coe', count: 3);

    $coeFiles = $component->get('coe');
    expect($coeFiles)->toBeArray();
    expect(count($coeFiles))->toBe(3);
    expect($component->get('uploadedTypes'))->toContain('coe');
});

test('removeFile removes file from slot and dispatches attachment-removed', function () {
    $file1 = UploadedFile::fake()->image('paspor_hal_1.jpg')->size(300);
    $file2 = UploadedFile::fake()->image('paspor_hal_2.jpg')->size(300);

    $component = Livewire::test(UploadDocument::class, ['hashid' => $this->hashid])
        ->set('paspor', [$file1, $file2])
        ->call('processSlot', 'paspor')
        ->assertSet('uploadedTypes', ['paspor']);

    // Hapus file pertama (index 0)
    $component->call('removeFile', 'paspor', 0)
        ->assertDispatched('attachment-removed', type: 'paspor', index: 0);

    $remaining = $component->get('paspor');
    expect(count($remaining))->toBe(1);

    // Hapus file sisa (index 0)
    $component->call('removeFile', 'paspor', 0);
    expect($component->get('uploadedTypes'))->not()->toContain('paspor');
});

test('submitAllDocuments succeeds when all slots are optional by default', function () {
    Livewire::test(UploadDocument::class, ['hashid' => $this->hashid])
        ->call('submitAllDocuments')
        ->assertHasNoErrors()
        ->assertSet('is_submitted', true)
        ->assertDispatched('documents-submitted')
        ->assertRedirect(route('form.success', ['hashid' => $this->hashid]));

    expect($this->visaForm->fresh()->status)->toBe(VisaFormStatus::DIAJUKAN);
});

test('submitAllDocuments blocks and alerts if required slot has no uploaded files', function () {
    $component = Livewire::test(UploadDocument::class, ['hashid' => $this->hashid]);

    // Ubah slot coe menjadi required via Livewire call agar tersimpan di state component
    $component->call('setSlotRequired', 'coe', true);

    $component->call('submitAllDocuments')
        ->assertSet('is_submitted', false)
        ->assertNotDispatched('documents-submitted')
        ->assertDispatched('SwalInfo'); // Alert::fail dispatches SwalInfo
});

test('submitAllDocuments stores files to configured DEFAULT_STORE_DISK and creates VisaFormAttachment records', function () {
    config(['filesystems.default_store_disk' => 'public']);

    $coeFile = UploadedFile::fake()->image('coe_asli.jpg', 600, 600)->size(500);
    $pasporFile = UploadedFile::fake()->image('paspor_scan.png', 800, 600)->size(800);
    $tiketPdf = UploadedFile::fake()->create('tiket_flight.pdf', 1200, 'application/pdf');

    Livewire::test(UploadDocument::class, ['hashid' => $this->hashid])
        ->set('coe', [$coeFile])
        ->set('paspor', [$pasporFile])
        ->set('tiket', [$tiketPdf])
        ->call('submitAllDocuments')
        ->assertHasNoErrors()
        ->assertSet('is_submitted', true)
        ->assertDispatched('documents-submitted')
        ->assertRedirect(route('form.success', ['hashid' => $this->hashid]));

    expect($this->visaForm->fresh()->status)->toBe(VisaFormStatus::DIAJUKAN);

    // Verifikasi record di database dengan kolom standar bahasa Inggris
    $this->assertDatabaseHas('visa_form_attachments', [
        'visa_form_id' => $this->visaForm->id,
        'disk' => 'public',
        'nama_file_asli' => 'coe_asli.jpg',
        'jenis_dokumen' => VisaAttachmentType::COE->value,
        'extension' => 'jpg',
    ]);

    $this->assertDatabaseHas('visa_form_attachments', [
        'visa_form_id' => $this->visaForm->id,
        'disk' => 'public',
        'nama_file_asli' => 'paspor_scan.png',
        'jenis_dokumen' => VisaAttachmentType::PASPOR->value,
        'extension' => 'png',
    ]);

    $this->assertDatabaseHas('visa_form_attachments', [
        'visa_form_id' => $this->visaForm->id,
        'disk' => 'public',
        'nama_file_asli' => 'tiket_flight.pdf',
        'jenis_dokumen' => VisaAttachmentType::TIKET->value,
        'extension' => 'pdf',
    ]);

    $attachment = VisaFormAttachment::where('visa_form_id', $this->visaForm->id)
        ->where('nama_file_asli', 'coe_asli.jpg')
        ->first();

    expect($attachment)->not->toBeNull();
    Storage::disk('public')->assertExists($attachment->path);
    expect($attachment->getUrl())->toBe(Storage::disk('public')->url($attachment->path));
    expect($attachment->url)->toBe($attachment->getUrl());
});

test('VisaFormAttachment getUrl returns direct SUPABASE_PUBLIC_URL for supabase disk', function () {
    config(['filesystems.disks.supabase.public_url' => 'https://xyz.supabase.co/storage/v1/object/public/visa-bucket']);

    $attachment = VisaFormAttachment::create([
        'visa_form_id' => $this->visaForm->id,
        'jenis_dokumen' => VisaAttachmentType::COE,
        'nama_file_asli' => 'coe_supabase.jpg',
        'nama_file_tersimpan' => 'stored_coe.jpg',
        'path' => 'visa-documents/123/coe/stored_coe.jpg',
        'disk' => 'supabase',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 102400,
        'status' => VisaAttachmenStatus::STATUS_STORED,
        'urutan' => 1,
    ]);

    $expectedUrl = 'https://xyz.supabase.co/storage/v1/object/public/visa-bucket/visa-documents/123/coe/stored_coe.jpg';
    expect($attachment->getUrl())->toBe($expectedUrl);
    expect($attachment->url)->toBe($expectedUrl);
});
