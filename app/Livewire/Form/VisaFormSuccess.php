<?php

namespace App\Livewire\Form;

use App\Enums\Form\VisaAttachmenStatus;
use App\Enums\Form\VisaAttachmentType;
use App\Enums\Form\VisaFormStatus;
use App\Models\Form\VisaForm;
use Livewire\Component;
use Vinkla\Hashids\Facades\Hashids;

class VisaFormSuccess extends Component
{
    public string $hashid = '';

    public ?VisaForm $visaForm = null;

    public function mount(string $hashid)
    {
        $this->hashid = $hashid;
        $decoded = Hashids::decode($hashid);
        $id = $decoded[0] ?? null;

        if (! $id) {
            abort(404, 'Data pengajuan visa tidak ditemukan.');
        }

        // Eager load seluruh relasi yang dibutuhkan untuk mencegah query N+1
        $form = VisaForm::with([
            'link.creator',
            'attachments' => fn ($q) => $q->orderBy('urutan')->orderBy('created_at'),
        ])->find($id);

        if (! $form) {
            abort(404, 'Data pengajuan visa tidak ditemukan.');
        }

        $this->visaForm = $form;
    }

    /**
     * URL WhatsApp PIC / Admin dengan pesan otomatis terpersonalisasi
     */
    public function getWhatsappUrlProperty(): string
    {
        $phone = $this->visaForm->link?->creator?->no_whatsapp
            ?: ($this->visaForm->creator?->no_whatsapp ?: config('app.support_whatsapp', '6281234567890'));
        $phone = preg_replace('/[^0-9]/', '', (string) $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        $registrationNumber = $this->visaForm->nomor_registrasi;
        $fullName = $this->visaForm->nama_lengkap;
        $message = "Halo Admin Exata Indonesia, saya telah menyelesaikan pengajuan formulir visa dengan Nomor Registrasi: {$registrationNumber} atas nama {$fullName}. Mohon informasi proses verifikasinya. Terima kasih.";

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }

    /**
     * Konfigurasi badge dan warna status riil
     */
    public function getStatusBadgeProperty(): array
    {
        $status = $this->visaForm->status instanceof VisaFormStatus
            ? $this->visaForm->status
            : VisaFormStatus::fromLabel((string) $this->visaForm->status);

        return match ($status) {
            VisaFormStatus::DRAFT => [
                'label' => 'Draft',
                'bg' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700',
                'dot' => 'bg-slate-400',
            ],
            VisaFormStatus::DIAJUKAN => [
                'label' => 'Diajukan',
                'bg' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/60',
                'dot' => 'bg-blue-500 animate-pulse',
            ],
            VisaFormStatus::SIAP_VERIFIKASI => [
                'label' => 'Siap Verifikasi',
                'bg' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
                'dot' => 'bg-amber-500 animate-pulse',
            ],
            VisaFormStatus::VERIFIKASI => [
                'label' => 'Terverifikasi',
                'bg' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/60',
                'dot' => 'bg-emerald-500',
            ],
            VisaFormStatus::SELESAI => [
                'label' => 'Selesai',
                'bg' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/60',
                'dot' => 'bg-emerald-500',
            ],
            VisaFormStatus::DITOLAK => [
                'label' => 'Ditolak',
                'bg' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800/60',
                'dot' => 'bg-rose-500',
            ],
            default => [
                'label' => 'Diajukan',
                'bg' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/60',
                'dot' => 'bg-blue-500 animate-pulse',
            ],
        };
    }

    /**
     * Masking NIK untuk perlindungan privasi data pribadi
     */
    public function getMaskedNikProperty(): string
    {
        $nik = (string) ($this->visaForm->nik_ktp ?? '');
        if (strlen($nik) >= 16) {
            return substr($nik, 0, 4).'********'.substr($nik, -4);
        }

        return $nik ?: '-';
    }

    /**
     * Masking Nomor Paspor
     */
    public function getMaskedPasporProperty(): string
    {
        $paspor = (string) ($this->visaForm->nomor_paspor ?? '');
        if (strlen($paspor) >= 6) {
            return substr($paspor, 0, 2).'****'.substr($paspor, -2);
        }

        return $paspor ?: '-';
    }

    /**
     * Informasi PIC Pendamping
     */
    public function getPicInfoProperty(): array
    {
        $code = $this->visaForm->link?->pic_code ?? $this->visaForm->pic_code ?? 'EXATA-PUSAT';
        $name = $this->visaForm->link?->creator?->name ?? null;

        return [
            'code' => $code,
            'name' => $name,
            'display' => $name ? "{$code} ({$name})" : $code,
        ];
    }

    /**
     * Ringkasan seluruh lampiran berkas yang diunggah
     */
    public function getAttachmentsSummaryProperty(): array
    {
        $attachments = $this->visaForm->attachments ?? collect();

        return $attachments->map(function ($att) {
            $label = $att->jenis_dokumen instanceof VisaAttachmentType
                ? $att->jenis_dokumen->label()
                : ($att->jenis_dokumen ?: 'Dokumen Pendukung');

            $sizeKb = $att->ukuran_file ? round($att->ukuran_file / 1024, 1) : 0;
            $sizeDisplay = $sizeKb >= 1024
                ? round($sizeKb / 1024, 2).' MB'
                : "{$sizeKb} KB";

            return [
                'id' => $att->id,
                'jenis' => $att->jenis_dokumen instanceof VisaAttachmentType ? $att->jenis_dokumen->value : (string) $att->jenis_dokumen,
                'label' => $label,
                'nama_asli' => $att->nama_file_asli,
                'ukuran' => $sizeDisplay,
                'ekstensi' => strtoupper((string) ($att->ekstensi ?: 'FILE')),
                'status' => $att->status instanceof VisaAttachmenStatus ? $att->status->label() : 'Tersimpan',
            ];
        })->all();
    }

    public function getTotalAttachmentsProperty(): int
    {
        return $this->visaForm->attachments?->count() ?? 0;
    }

    public function render()
    {
        $tanggalDiajukan = $this->visaForm->tanggal_diajukan
            ? $this->visaForm->tanggal_diajukan->translatedFormat('d F Y, H:i').' WIB'
            : ($this->visaForm->created_at ? $this->visaForm->created_at->translatedFormat('d F Y, H:i').' WIB' : '-');

        $tanggalKedatangan = $this->visaForm->tanggal_kedatangan
            ? $this->visaForm->tanggal_kedatangan->translatedFormat('d F Y')
            : '-';

        $tanggalPasporHabis = $this->visaForm->tanggal_habis_berlaku_paspor
            ? $this->visaForm->tanggal_habis_berlaku_paspor->translatedFormat('d F Y')
            : '-';

        return view('livewire.form.visa-form-success', [
            'visaForm' => $this->visaForm,
            'nomorRegistrasi' => $this->visaForm->nomor_registrasi,
            'namaLengkap' => $this->visaForm->nama_lengkap,
            'tanggalDiajukan' => $tanggalDiajukan,
            'statusBadge' => $this->statusBadge,
            'statusLabel' => $this->statusBadge['label'],
            'maskedNik' => $this->maskedNik,
            'maskedPaspor' => $this->maskedPaspor,
            'picInfo' => $this->picInfo,
            'picCode' => $this->picInfo['code'],
            'attachmentsSummary' => $this->attachmentsSummary,
            'totalAttachments' => $this->totalAttachments,
            'tanggalKedatangan' => $tanggalKedatangan,
            'tanggalPasporHabis' => $tanggalPasporHabis,
            'whatsappUrl' => $this->whatsappUrl,
        ])->extends('layouts.fullscreen-layout')->section('content');
    }
}
