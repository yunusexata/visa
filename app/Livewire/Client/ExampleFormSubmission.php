<?php

namespace App\Livewire\Client;

use App\Helpers\Alert;
use App\Models\MasterData\Regency;
use Livewire\Component;

class ExampleFormSubmission extends Component
{
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

    // Step History Storage (simulating DB draft state in Livewire)
    public array $stepHistory = [];

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
            'step'         => $step,
            'completed_at' => now()->toDateTimeString(),
            'data'         => $data,
        ];

        // Global consoleLog helper to inspect data state in Livewire backend
        Alert::consoleLog($this, [
            'event' => 'STEP_ADVANCE',
            'step'  => $step,
            'data'  => $data,
            'time'  => now()->toDateTimeString(),
        ]);

        // Dispatch confirmation event to advance step on frontend
        $this->dispatch('step-advanced', step: $step + 1);
    }

    /**
     * Final submission method
     */
    public function submit(): void
    {
        if (!$this->declaration_agreed) {
            $this->addError('declaration_agreed', 'Harap centang persetujuan keabsahan data sebelum mengirim.');
            Alert::fail($this, 'Persetujuan Diperlukan', 'Harap centang pernyataan kebenaran data formulir.');
            return;
        }

        // Log final submission data state in backend
        Alert::consoleLog($this, [
            'event' => 'FORM_SUBMITTED',
            'time'  => now()->toDateTimeString(),
            'data'  => [
                'nama_lengkap' => $this->nama_lengkap,
                'tempat_lahir' => $this->tempat_lahir,
                'nik_ktp'      => $this->nik_ktp,
                'nomor_paspor' => $this->nomor_paspor,
                'nomor_coe'    => $this->nomor_coe,
                'nama_kaisha'  => $this->nama_kaisha,
            ],
        ]);

        $this->is_submitted = true;

        // Dispatch manual event to clean localStorage only after successful processing
        $this->dispatch('clear-form-draft');

        Alert::success(
            $this,
            'Formulir Berhasil Dikirim!',
            'Data aplikasi visa Anda telah berhasil disimpan sebagai draf/preview pengajuan.'
        );
    }

    public function render()
    {
        return view('livewire.client.example-form-submission')
            ->extends('layouts.fullscreen-layout')
            ->section('content');
    }
}
