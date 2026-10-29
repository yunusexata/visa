<?php

use App\Helpers\Alert;
use App\Livewire\Form\UploadDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

test('upload document page is accessible and renders livewire component', function () {
    $response = $this->get(route('form.upload-document'));

    $response->assertOk();
    $response->assertSeeLivewire(UploadDocument::class);
});

test('example upload alias redirects to form.upload-document', function () {
    $response = $this->get('/example/upload');

    $response->assertRedirect(route('form.upload-document'));
});

test('all slots default to multiple true and required false', function () {
    $component = Livewire::test(UploadDocument::class);
    $config = $component->instance()->getSlotConfig();

    $expectedSlots = ['coe', 'e_ktln', 'ktp', 'paspor', 'tiket', 'pas_photo'];

    foreach ($expectedSlots as $slot) {
        expect($config)->toHaveKey($slot);
        expect($config[$slot]['multiple'])->toBeTrue("Slot [{$slot}] should default to multiple = true");
        expect($config[$slot]['required'])->toBeFalse("Slot [{$slot}] should default to required = false");
    }
});

test('component renders all 6 document slots and badges', function () {
    Livewire::test(UploadDocument::class)
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

    Livewire::test(UploadDocument::class)
        ->set('coe', [$fakeFile])
        ->call('processSlot', 'coe')
        ->assertHasErrors(['coe.0']);
});

test('backend validation accepts valid jpg image and dispatches attachment-saved and consoleLog', function () {
    $imageFile = UploadedFile::fake()->image('coe_dokumen.jpg', 800, 600)->size(400);

    Livewire::test(UploadDocument::class)
        ->set('coe', [$imageFile])
        ->call('processSlot', 'coe')
        ->assertHasNoErrors()
        ->assertDispatched('attachment-saved', type: 'coe', count: 1)
        ->assertDispatched(Alert::EVENT_CONSOLE_LOG);
});

test('backend validation accepts pdf file under 2mb for tiket slot', function () {
    $pdfFile = UploadedFile::fake()->create('tiket_flight.pdf', 1500, 'application/pdf');

    Livewire::test(UploadDocument::class)
        ->set('tiket', [$pdfFile])
        ->call('processSlot', 'tiket')
        ->assertHasNoErrors()
        ->assertDispatched('attachment-saved', type: 'tiket', count: 1);
});

test('backend validation rejects pdf file over 2mb', function () {
    // 2.5MB = 2560KB (melebihi batas 2048KB)
    $largePdf = UploadedFile::fake()->create('tiket_jumbo.pdf', 2600, 'application/pdf');

    Livewire::test(UploadDocument::class)
        ->set('tiket', [$largePdf])
        ->call('processSlot', 'tiket')
        ->assertHasErrors(['tiket.0']);
});

test('backend validation rejects pdf on image-only slot like ktp', function () {
    $pdfOnKtp = UploadedFile::fake()->create('ktp.pdf', 300, 'application/pdf');

    Livewire::test(UploadDocument::class)
        ->set('ktp', [$pdfOnKtp])
        ->call('processSlot', 'ktp')
        ->assertHasErrors(['ktp.0']);
});

test('buffer incomingSlot and incomingFile appends to slot without overwriting previous uploads', function () {
    $file1 = UploadedFile::fake()->image('paspor_hal_1.jpg')->size(350);
    $file2 = UploadedFile::fake()->image('paspor_hal_2.jpg')->size(420);

    $component = Livewire::test(UploadDocument::class)
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

test('removeFile removes file from slot and dispatches attachment-removed', function () {
    $file1 = UploadedFile::fake()->image('paspor_hal_1.jpg')->size(300);
    $file2 = UploadedFile::fake()->image('paspor_hal_2.jpg')->size(300);

    $component = Livewire::test(UploadDocument::class)
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
    Livewire::test(UploadDocument::class)
        ->call('submitAllDocuments')
        ->assertHasNoErrors()
        ->assertSet('is_submitted', true)
        ->assertDispatched('documents-submitted')
        ->assertDispatched('SwalInfo');
});

test('submitAllDocuments blocks and alerts if required slot has no uploaded files', function () {
    $component = Livewire::test(UploadDocument::class);

    // Ubah slot coe menjadi required via Livewire call agar tersimpan di state component
    $component->call('setSlotRequired', 'coe', true);

    $component->call('submitAllDocuments')
        ->assertSet('is_submitted', false)
        ->assertNotDispatched('documents-submitted')
        ->assertDispatched('SwalInfo'); // Alert::fail dispatches SwalInfo
});

