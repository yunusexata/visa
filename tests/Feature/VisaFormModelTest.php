<?php

use App\Enums\Form\VisaAttachmenStatus;
use App\Enums\Form\VisaAttachmentType;
use App\Enums\Form\VisaFormLinkStatus;
use App\Enums\Form\VisaFormStatus;
use App\Models\Form\VisaForm;
use App\Models\Form\VisaFormAttachment;
use App\Models\Form\VisaFormLink;
use Carbon\CarbonInterface;

test('it can create a visa form with indonesian column attributes', function () {
    $form = VisaForm::create([
        'nama_lengkap' => 'budi santoso',
        'tempat_lahir' => 'surabaya',
        'tanggal_lahir' => '1998-05-15',
        'jenis_kelamin' => 'l',
        'status_kawin' => 'BELUM KAWIN',
        'nik_ktp' => '3578012345670001',
        'alamat_indonesia' => 'Jl. Pemuda No. 10, Surabaya',
        'kode_pos_indonesia' => '60271',
        'telepon_indonesia' => '081234567890',
        'email_aktif' => 'BUDI.SANTOSO@GMAIL.COM',

        'nomor_paspor' => 'c1234567',
        'kantor_paspor' => 'kanim surabaya',
        'tanggal_pengeluaran_paspor' => '2023-01-10',
        'tanggal_habis_berlaku_paspor' => '2033-01-10',

        'nomor_coe' => 'coe-2026-9999',
        'status_kependudukan_jepang' => 'Specified Skilled Worker (SSW / Tokutei Ginou)',
        'lama_stay_jepang' => '1 Tahun',

        'tanggal_kedatangan' => '2026-12-01',
        'bandara_tujuan' => 'Haneda (HND)',
        'maskapai' => 'Garuda Indonesia',
        'nama_kaisha' => 'exata japan co., ltd.',
        'alamat_jepang' => 'Tokyo, Shinjuku',
        'telepon_jepang' => '08012345678',
        'pernah_kerja_jepang' => 'tidak',
        'nama_penanggung_jawab' => 'tanaka kenji',
        'jenis_kelamin_penanggung_jawab' => 'p',
        'alamat_penanggung_jawab' => 'Tokyo, Shinjuku',
        'telepon_penanggung_jawab' => '08098765432',

        'pernyataan_persetujuan' => true,
        'status' => VisaFormStatus::DIAJUKAN,
        'pic_code' => 'PIC-SBY-01',
    ]);

    expect($form)->toBeInstanceOf(VisaForm::class)
        ->and($form->id)->not->toBeNull()
        ->and($form->nik_ktp)->toBe('3578012345670001')
        ->and($form->status)->toBe(VisaFormStatus::DIAJUKAN);

    // Verify uppercase attribute mutation
    expect($form->nama_lengkap)->toBe('BUDI SANTOSO')
        ->and($form->tempat_lahir)->toBe('SURABAYA')
        ->and($form->nomor_paspor)->toBe('C1234567')
        ->and($form->nama_kaisha)->toBe('EXATA JAPAN CO., LTD.')
        ->and($form->jenis_kelamin)->toBe('L')
        ->and($form->jenis_kelamin_penanggung_jawab)->toBe('P');

    // Verify lowercase attribute mutation
    expect($form->email_aktif)->toBe('budi.santoso@gmail.com')
        ->and($form->status_kawin)->toBe('belum kawin');

    // Verify casts
    expect($form->tanggal_lahir)->toBeInstanceOf(CarbonInterface::class)
        ->and($form->tanggal_lahir->format('Y-m-d'))->toBe('1998-05-15')
        ->and($form->pernyataan_persetujuan)->toBeTrue();
});

test('it belongs to a visa form link and can be accessed from link', function () {
    $link = VisaFormLink::create([
        'name' => 'Budi Santoso',
        'password' => null,
        'max_usage' => 1,
        'expired_at' => now()->addDays(5),
        'status' => VisaFormLinkStatus::STATUS_ACTIVE,
    ]);

    $form = VisaForm::factory()->create([
        'visa_form_link_id' => $link->id,
        'nama_lengkap' => 'BUDI SANTOSO',
    ]);

    expect($form->link)->toBeInstanceOf(VisaFormLink::class)
        ->and($form->link->id)->toBe($link->id)
        ->and($link->visaForms)->toHaveCount(1)
        ->and($link->visaForms->first()->id)->toBe($form->id);
});

test('it can create and associate attachments in bahasa indonesia', function () {
    $form = VisaForm::factory()->create();

    $attachment = VisaFormAttachment::create([
        'visa_form_id' => $form->id,
        'jenis_dokumen' => VisaAttachmentType::KTP,
        'nama_file_asli' => 'KTP_Asli_Budi.jpg',
        'nama_file_tersimpan' => 'ktp_hash_uuid_123.jpg',
        'path' => 'visa-documents/ktp/ktp_hash_uuid_123.jpg',
        'disk' => 'public',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 524288,
        'status' => VisaAttachmenStatus::STATUS_STORED,
        'urutan' => 1,
    ]);

    expect($attachment->visaForm)->toBeInstanceOf(VisaForm::class)
        ->and($attachment->visaForm->id)->toBe($form->id)
        ->and($attachment->jenis_dokumen)->toBe(VisaAttachmentType::KTP)
        ->and($attachment->status)->toBe(VisaAttachmenStatus::STATUS_STORED)
        ->and($form->attachments)->toHaveCount(1)
        ->and($attachment->url)->toContain('visa-documents/ktp/ktp_hash_uuid_123.jpg');
});

test('deleting a visa form cascades to its attachments', function () {
    $form = VisaForm::factory()->create();

    VisaFormAttachment::factory()->count(3)->create([
        'visa_form_id' => $form->id,
    ]);

    expect(VisaFormAttachment::where('visa_form_id', $form->id)->count())->toBe(3);

    $form->forceDelete();

    expect(VisaFormAttachment::where('visa_form_id', $form->id)->count())->toBe(0);
});

test('it filters forms by scopes', function () {
    VisaForm::factory()->create([
        'status' => VisaFormStatus::DIAJUKAN,
        'pic_code' => 'PIC-001',
    ]);

    VisaForm::factory()->create([
        'status' => VisaFormStatus::VERIFIKASI,
        'pic_code' => 'PIC-002',
    ]);

    expect(VisaForm::diajukan()->count())->toBe(1)
        ->and(VisaForm::byPic('PIC-001')->count())->toBe(1)
        ->and(VisaForm::byPic('PIC-002')->count())->toBe(1);
});
