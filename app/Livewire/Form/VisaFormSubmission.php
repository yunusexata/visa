<?php

namespace App\Livewire\Form;

use App\Enums\Form\VisaFormLinkStatus;
use App\Helpers\Alert;
use App\Models\Form\VisaFormLink;
use App\Models\MasterData\Regency;
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

    // Step 5: Submission & Declaration
    public $declaration_agreed = false;

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

        // Increment usage count in database
        $link->incrementUsedCount();

        // Log final submission data state in backend
        Alert::consoleLog($this, [
            'event' => 'CLIENT_FORM_SUBMITTED',
            'hashid' => $this->hashid,
            'link_id' => $link->id,
            'applicant' => $link->name,
            'pic_code' => $link->pic_code,
            'used_count' => $link->used_count,
            'max_usage' => $link->max_usage,
            'time' => now()->toDateTimeString(),
            'data' => [
                'nama_lengkap' => $this->nama_lengkap,
                'tempat_lahir' => $this->tempat_lahir,
                'nik_ktp' => $this->nik_ktp,
                'nomor_paspor' => $this->nomor_paspor,
                'nomor_coe' => $this->nomor_coe,
                'nama_kaisha' => $this->nama_kaisha,
                'email_aktif' => $this->email_aktif,
                'telepon_indonesia' => $this->telepon_indonesia,
            ],
        ]);

        $this->is_submitted = true;

        // Dispatch manual event to clean localStorage only after successful processing
        $this->dispatch('clear-form-draft');

        // Redirect to Upload Document UI with magic link reference
        return redirect()->route('form.upload-document', ['link' => $this->hashid]);
    }

    public function render()
    {
        return view('livewire.form.visa-form-submission')
            ->extends('layouts.fullscreen-layout')
            ->section('content');
    }
}
