<?php

namespace App\Livewire\Form;

use App\Enums\Form\VisaFormLinkStatus;
use App\Enums\Form\VisaFormStatus;
use App\Helpers\Alert;
use App\Models\Form\VisaForm;
use App\Models\Form\VisaFormLink;
use App\Models\MasterData\Regency;
use Illuminate\Support\Str;
use Livewire\Component;
use Vinkla\Hashids\Facades\Hashids;

class VisaFormSubmission extends Component
{
    // Magic Link Parameters & State
    public string $hashid = '';

    public ?int $linkId = null;

    public string $linkState = 'valid'; // 'valid', 'not_found', 'expired', 'exhausted'

    public bool $isAuthorized = false;

    public string $inputPassword = '';

    // Link Metadata for Display
    public string $applicantName = '';

    public string $picCode = '';

    public string $expiredAtFormatted = '';

    // Step 1: Data Identitas Diri (Wajib)
    public $nama_lengkap = '';

    public $tempat_lahir = '';

    public $tanggal_lahir = '';

    public $nik_ktp = '';

    public $alamat_indonesia = '';

    public $kode_pos_indonesia = '';

    public $telepon_indonesia = '';

    public $email_aktif = '';

    // Step 2: Data di Paspor
    public $nomor_paspor = '';

    public $kantor_paspor = '';

    public $tanggal_pengeluaran_paspor = '';

    public $tanggal_habis_berlaku_paspor = '';

    // Step 3: Certificate of Eligibility (COE)
    public $nomor_coe = '';

    public $status_kependudukan_jepang = 'Specified Skilled Worker (SSW / Tokutei Ginou)';

    public $lama_stay_jepang = '1 Tahun';

    // Step 4: Data Lainnya & Kaisha di Jepang
    public $tanggal_kedatangan = '';

    public $bandara_tujuan = 'Haneda (HND)';

    public $bandara_lainnya = '';

    public $maskapai = '';

    public $nama_kaisha = '';

    public $alamat_jepang = '';

    public $kode_pos_jepang = '';

    public $telepon_jepang = '';

    public $pernah_kerja_jepang = 'tidak';

    public $periode_kerja_mulai = '';

    public $periode_kerja_selesai = '';

    public $nama_penanggung_jawab = '';

    public $alamat_penanggung_jawab = '';

    public $telepon_penanggung_jawab = '';

    public bool $declaration_agreed = false;

    public $is_submitted = false;

    // Step History Storage
    public array $stepHistory = [];

    public function mount(string $hashid): void
    {
        $this->hashid = $hashid;
        $decoded = Hashids::decode($hashid);
        $this->linkId = $decoded[0] ?? null;

        if (! $this->linkId) {
            $this->linkState = 'not_found';

            return;
        }

        $link = VisaFormLink::find($this->linkId);

        if (! $link) {
            $this->linkState = 'not_found';

            return;
        }

        // Check if expired
        if ($link->expired_at && $link->expired_at->isPast()) {
            $this->linkState = 'expired';

            return;
        }

        // Check if already reached max usage
        if ($link->used_count >= $link->max_usage || $link->status === VisaFormLinkStatus::STATUS_SUCCESS) {
            $this->linkState = 'exhausted';

            return;
        }

        // Check active status
        $statusValue = $link->status instanceof VisaFormLinkStatus ? $link->status->value : $link->status;
        if ($statusValue === VisaFormLink::STATUS_EXPIRED) {
            $this->linkState = 'expired';

            return;
        }
        if ($statusValue !== VisaFormLink::STATUS_ACTIVE && $statusValue !== 'ACTIVE') {
            $this->linkState = 'exhausted';

            return;
        }

        // Link is valid, set info
        $this->linkState = 'valid';
        $this->applicantName = $link->name ?: 'Klien Terhormat';
        $this->picCode = $link->pic_code ?: '';
        $this->expiredAtFormatted = $link->expired_at ? $link->expired_at->format('d M Y') : '-';

        // Check password protection
        if (empty($link->password)) {
            $this->isAuthorized = true;
        } else {
            $this->isAuthorized = false;
        }
    }

    /**
     * Password verification action
     */
    public function verifyPassword(): void
    {
        $this->validate([
            'inputPassword' => 'required|string',
        ], [
            'inputPassword.required' => 'Harap masukkan kata sandi formulir.',
        ]);

        $link = VisaFormLink::find($this->linkId);

        if (! $link) {
            $this->linkState = 'not_found';

            return;
        }

        if (trim($this->inputPassword) === trim($link->password)) {
            $this->isAuthorized = true;
            $this->resetValidation();
            $this->dispatch('password-authorized');
        } else {
            $this->addError('inputPassword', 'Kata sandi yang Anda masukkan salah. Silakan coba lagi.');
            Alert::fail($this, 'Kata Sandi Salah', 'Kata sandi tidak cocok. Periksa kembali atau hubungi admin.');
        }
    }

    /**
     * Deferred loading method for regencies list
     */
    public function loadRegencies(): array
    {
        return Regency::orderBy('name')->pluck('name')->toArray();
    }

    /**
     * Step advancement action with consoleLog tracking
     */
    public function nextStep(int $step, array $data = []): void
    {
        // Record step data in stepHistory
        $this->stepHistory[$step] = [
            'step' => $step,
            'completed_at' => now()->toDateTimeString(),
            'data' => $data,
        ];

        // Global consoleLog helper to inspect data state in Livewire backend
        Alert::consoleLog($this, [
            'event' => 'STEP_ADVANCE',
            'hashid' => $this->hashid,
            'step' => $step,
            'data' => $data,
            'time' => now()->toDateTimeString(),
        ]);

        // Dispatch confirmation event to advance step on frontend
        $this->dispatch('step-advanced', step: $step + 1);
    }

    /**
     * Final submission method
     */
    public function submit()
    {
        if (! $this->declaration_agreed) {
            $this->addError('declaration_agreed', 'Harap centang persetujuan keabsahan data sebelum mengirim.');
            Alert::fail($this, 'Persetujuan Diperlukan', 'Harap centang pernyataan kebenaran data formulir.');

            return null;
        }

        $this->resetErrorBag('declaration_agreed');

        $link = VisaFormLink::find($this->linkId);

        if (! $link) {
            $this->linkState = 'not_found';

            return null;
        }

        // Verify link is still valid prior to recording
        if ($link->expired_at && $link->expired_at->isPast()) {
            $this->linkState = 'expired';
            Alert::fail($this, 'Tautan Kadaluarsa', 'Masa aktif tautan pengajuan telah habis.');

            return null;
        }

        if ($link->used_count >= $link->max_usage) {
            $this->linkState = 'exhausted';
            Alert::fail($this, 'Kuota Habis', 'Tautan ini telah mencapai batas maksimum pengisian.');

            return null;
        }

        // Simpan data formulir visa pemohon ke database dengan status DRAFT
        $visaForm = VisaForm::create([
            'visa_form_link_id' => $link->id,
            'nomor_registrasi' => 'VISA-'.date('Ymd').'-'.strtoupper(Str::random(4)),
            'status' => VisaFormStatus::DRAFT,
            'pic_code' => $link->pic_code,

            // Step 1: Identitas Diri
            'nama_lengkap' => $this->nama_lengkap ?: 'PEMOHON VISA',
            'tempat_lahir' => $this->tempat_lahir ?: 'SURABAYA',
            'tanggal_lahir' => $this->tanggal_lahir ?: now()->subYears(20)->toDateString(),
            'nik_ktp' => $this->nik_ktp ?: '3578000000000000',
            'alamat_indonesia' => $this->alamat_indonesia ?: '-',
            'kode_pos_indonesia' => $this->kode_pos_indonesia,
            'telepon_indonesia' => $this->telepon_indonesia ?: '081200000000',
            'email_aktif' => $this->email_aktif ?: 'klien@exata.site',

            // Step 2: Data Paspor
            'nomor_paspor' => $this->nomor_paspor ?: 'C0000000',
            'kantor_paspor' => $this->kantor_paspor ?: 'KANIM SURABAYA',
            'tanggal_pengeluaran_paspor' => $this->tanggal_pengeluaran_paspor ?: now()->toDateString(),
            'tanggal_habis_berlaku_paspor' => $this->tanggal_habis_berlaku_paspor ?: now()->addYears(5)->toDateString(),

            // Step 3: COE
            'nomor_coe' => $this->nomor_coe ?: 'COE-0000-0000',
            'status_kependudukan_jepang' => $this->status_kependudukan_jepang,
            'lama_stay_jepang' => $this->lama_stay_jepang,

            // Step 4: Rencana Kedatangan & Kaisha di Jepang
            'tanggal_kedatangan' => $this->tanggal_kedatangan ?: now()->addMonth()->toDateString(),
            'bandara_tujuan' => $this->bandara_tujuan,
            'bandara_lainnya' => $this->bandara_lainnya,
            'maskapai' => $this->maskapai ?: '-',
            'nama_kaisha' => $this->nama_kaisha ?: '-',
            'alamat_jepang' => $this->alamat_jepang ?: '-',
            'kode_pos_jepang' => $this->kode_pos_jepang,
            'telepon_jepang' => $this->telepon_jepang ?: '08000000000',
            'pernah_kerja_jepang' => $this->pernah_kerja_jepang,
            'periode_kerja_mulai' => $this->periode_kerja_mulai,
            'periode_kerja_selesai' => $this->periode_kerja_selesai,
            'nama_penanggung_jawab' => $this->nama_penanggung_jawab ?: '-',
            'alamat_penanggung_jawab' => $this->alamat_penanggung_jawab ?: '-',
            'telepon_penanggung_jawab' => $this->telepon_penanggung_jawab ?: '08000000000',
            'tanggal_diajukan' => now(),
        ]);

        // Increment usage count in database
        $link->incrementUsedCount();

        // Log final submission data state in backend
        Alert::consoleLog($this, [
            'event' => 'CLIENT_FORM_SUBMITTED',
            'hashid' => $this->hashid,
            'visa_form_id' => $visaForm->id,
            'nomor_registrasi' => $visaForm->nomor_registrasi,
            'link_id' => $link->id,
            'applicant' => $visaForm->nama_lengkap,
            'time' => now()->toDateTimeString(),
        ]);

        $this->is_submitted = true;

        // Dispatch manual event to clean localStorage only after successful processing
        $this->dispatch('clear-form-draft');

        // Encode ID dari VisaForm langsung sebagai parameter path rute upload dokumen
        $formHashid = Hashids::encode($visaForm->id);

        return redirect()->route('form.upload-document', ['hashid' => $formHashid]);
    }

    public function render()
    {
        return view('livewire.form.visa-form-submission')
            ->extends('layouts.fullscreen-layout')
            ->section('content');
    }
}
