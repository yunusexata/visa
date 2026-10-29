<?php

use App\Enums\Form\VisaAttachmenStatus;
use App\Enums\Form\VisaAttachmentType;
use App\Enums\Form\VisaFormStatus;
use App\Livewire\Form\UploadDocument;
use App\Livewire\Form\VisaFormSuccess;
use App\Models\Form\VisaForm;
use App\Models\Form\VisaFormAttachment;
use App\Models\Form\VisaFormLink;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Vinkla\Hashids\Facades\Hashids;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');
});

test('user can access upload document route when visa form status is draft', function () {
    $visaForm = VisaForm::factory()->create([
        'status' => VisaFormStatus::DRAFT,
    ]);

    $hashid = Hashids::encode($visaForm->id);

    $response = $this->get(route('form.upload-document', ['hashid' => $hashid]));

    $response->assertOk();
    $response->assertSeeLivewire(UploadDocument::class);
    $response->assertSee($visaForm->nama_lengkap);
});

test('accessing upload document route when visa form is not draft redirects to success page', function () {
    $visaForm = VisaForm::factory()->create([
        'status' => VisaFormStatus::DIAJUKAN,
    ]);

    $hashid = Hashids::encode($visaForm->id);

    $response = $this->get(route('form.upload-document', ['hashid' => $hashid]));

    $response->assertRedirect(route('form.success', ['hashid' => $hashid]));
});

test('accessing upload document route with invalid hashid returns 404', function () {
    $response = $this->get('/form/upload-document/invalid-hash-999');

    $response->assertNotFound();
});

test('success page displays dossier card, applicant name, registration number, and whatsapp button', function () {
    $user = User::factory()->create([
        'username' => 'pic_test_user',
        'no_whatsapp' => '081299887766',
    ]);

    $this->actingAs($user);

    $link = VisaFormLink::create([
        'name' => 'Klien Success Test',
        'max_usage' => 1,
        'created_by' => $user->id,
        'pic_code' => 'PIC-SUB-01',
    ]);
    $link->created_by = $user->id;
    $link->saveQuietly();

    auth()->logout();

    $visaForm = VisaForm::factory()->create([
        'visa_form_link_id' => $link->id,
        'nama_lengkap' => 'AHMAD KURNIAWAN',
        'nomor_registrasi' => 'VISA-20261029-9999',
        'nik_ktp' => '3578012345670001',
        'nomor_paspor' => 'C9876543',
        'nama_kaisha' => 'HONDA MOTORS JAPAN',
        'telepon_indonesia' => '081234567890',
        'email_aktif' => 'ahmad@example.com',
        'status' => VisaFormStatus::DIAJUKAN,
        'tanggal_diajukan' => now(),
    ]);

    // Tambahkan 2 lampiran berkas dokumen
    VisaFormAttachment::create([
        'visa_form_id' => $visaForm->id,
        'jenis_dokumen' => VisaAttachmentType::COE,
        'nama_file_asli' => 'coe_ahmad.pdf',
        'nama_file_tersimpan' => 'coe_stored.pdf',
        'jalur_file' => 'visa-documents/1/coe.pdf',
        'disk' => 'public',
        'ekstensi' => 'pdf',
        'tipe_mime' => 'application/pdf',
        'ukuran_file' => 102400,
        'status' => VisaAttachmenStatus::STATUS_STORED,
        'urutan' => 1,
    ]);

    VisaFormAttachment::create([
        'visa_form_id' => $visaForm->id,
        'jenis_dokumen' => VisaAttachmentType::PASPOR,
        'nama_file_asli' => 'paspor_ahmad.jpg',
        'nama_file_tersimpan' => 'paspor_stored.jpg',
        'jalur_file' => 'visa-documents/1/paspor.jpg',
        'disk' => 'public',
        'ekstensi' => 'jpg',
        'tipe_mime' => 'image/jpeg',
        'ukuran_file' => 204800,
        'status' => VisaAttachmenStatus::STATUS_STORED,
        'urutan' => 2,
    ]);

    $hashid = Hashids::encode($visaForm->id);

    $response = $this->get(route('form.success', ['hashid' => $hashid]));

    $response->assertOk();
    $response->assertSeeLivewire(VisaFormSuccess::class);
    $response->assertSee('VISA-20261029-9999');
    $response->assertSee('AHMAD KURNIAWAN');
    $response->assertSee('PIC-SUB-01');
    $response->assertSee('3578********0001');
    $response->assertSee('HONDA MOTORS JAPAN');
    $response->assertSee('2 Berkas');
    $response->assertSee('Photo COE');
    $response->assertSee('Photo Paspor');
    $response->assertSee('081234567890');
    $response->assertSee('ahmad@example.com');
    $response->assertSee('Pengajuan Formulir Berhasil Dikirim');
    $response->assertSee('Hubungi WhatsApp PIC / Admin');
    $response->assertSee('wa.me/6281299887766');
});

test('submitAllDocuments persists uploaded files to database and transitions status to DIAJUKAN', function () {
    $visaForm = VisaForm::factory()->create([
        'status' => VisaFormStatus::DRAFT,
    ]);

    $hashid = Hashids::encode($visaForm->id);

    $fakeCoe = UploadedFile::fake()->image('coe_asli.jpg', 600, 800)->size(500);
    $fakePaspor = UploadedFile::fake()->image('paspor_scan.jpg', 600, 800)->size(600);

    Livewire::test(UploadDocument::class, ['hashid' => $hashid])
        ->set('coe', [$fakeCoe])
        ->set('paspor', [$fakePaspor])
        ->call('submitAllDocuments')
        ->assertRedirect(route('form.success', ['hashid' => $hashid]));

    // Assert status updated to DIAJUKAN
    $freshForm = $visaForm->fresh();
    expect($freshForm->status)->toBe(VisaFormStatus::DIAJUKAN)
        ->and($freshForm->tanggal_diajukan)->not->toBeNull();

    // Assert attachments saved to database
    $attachments = VisaFormAttachment::where('visa_form_id', $visaForm->id)->get();
    expect($attachments->count())->toBe(2);

    $coeAttachment = $attachments->firstWhere('jenis_dokumen', VisaAttachmentType::COE);
    expect($coeAttachment)->not->toBeNull()
        ->and($coeAttachment->nama_file_asli)->toBe('coe_asli.jpg')
        ->and($coeAttachment->status)->toBe(VisaAttachmenStatus::STATUS_STORED)
        ->and(Storage::disk('public')->exists($coeAttachment->jalur_file))->toBeTrue();

    $pasporAttachment = $attachments->firstWhere('jenis_dokumen', VisaAttachmentType::PASPOR);
    expect($pasporAttachment)->not->toBeNull()
        ->and($pasporAttachment->nama_file_asli)->toBe('paspor_scan.jpg')
        ->and(Storage::disk('public')->exists($pasporAttachment->jalur_file))->toBeTrue();
});
