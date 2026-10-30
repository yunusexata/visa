<?php

namespace App\Livewire\Form;

use App\Enums\Form\VisaFormLinkStatus;
use App\Models\Form\VisaForm;
use App\Models\Form\VisaFormLink;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Vinkla\Hashids\Facades\Hashids;

class VisaFormStatusPage extends Component
{
    public string $hashid = '';

    public ?int $linkId = null;

    public ?VisaFormLink $link = null;

    public string $statusType = 'not_found'; // 'not_found', 'expired', 'exhausted', 'inactive', 'valid'

    /**
     * @var Collection<int, VisaForm>
     */
    public $submissions = [];

    public function mount(string $hashid): void
    {
        $this->hashid = $hashid;
        $decoded = Hashids::decode($hashid);
        $this->linkId = $decoded[0] ?? null;

        if (! $this->linkId) {
            $this->statusType = 'not_found';

            return;
        }

        $this->link = VisaFormLink::with([
            'creator',
            'visaForms' => fn ($q) => $q->withCount('attachments')->latest('created_at'),
        ])->find($this->linkId);

        if (! $this->link) {
            $this->statusType = 'not_found';

            return;
        }

        $this->submissions = $this->link->visaForms;

        // 1. Cek jika tautan kadaluarsa
        if (($this->link->expired_at && $this->link->expired_at->isPast()) || $this->link->status === VisaFormLinkStatus::STATUS_EXPIRED) {
            $this->statusType = 'expired';

            return;
        }

        // 2. Cek jika kuota penggunaan sudah tercapai / status SUCCESS (Exhausted / Max Submission)
        $statusValue = $this->link->status instanceof VisaFormLinkStatus ? $this->link->status->value : $this->link->status;
        if ($this->link->used_count >= $this->link->max_usage || $statusValue === VisaFormLinkStatus::STATUS_SUCCESS->value) {
            $this->statusType = 'exhausted';

            return;
        }

        // 3. Cek jika status tautan nonaktif
        if ($statusValue === VisaFormLinkStatus::STATUS_INACTIVE->value || ($statusValue !== VisaFormLink::STATUS_ACTIVE && $statusValue !== 'ACTIVE')) {
            $this->statusType = 'inactive';

            return;
        }

        // 4. Jika tautan masih valid & memiliki kuota
        $this->statusType = 'valid';
    }

    /**
     * URL WhatsApp PIC / Admin dengan pesan otomatis terpersonalisasi
     */
    public function getWhatsappUrlProperty(): string
    {
        $phone = $this->link?->creator?->no_whatsapp
            ?: config('app.support_whatsapp', '6281234567890');
        $phone = preg_replace('/[^0-9]/', '', (string) $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        $clientName = $this->link?->name ?: 'Kandidat';

        $context = match ($this->statusType) {
            'expired' => "Tautan formulir visa {$clientName} telah kedaluwarsa. Mohon informasi perpanjangan batas pengisian.",
            'exhausted' => "Pengisian formulir visa {$clientName} telah mencapai batas kuota pengajuan.",
            'inactive' => "Tautan formulir visa {$clientName} berstatus tidak aktif. Mohon bantuan aktivasinya.",
            default => "Tautan formulir visa tidak dapat diakses (Kode Tautan: {$this->hashid}). Mohon bantuan informasi tautan yang valid.",
        };

        $message = "Halo Admin Exata Indonesia, {$context} Terima kasih.";

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }

    /**
     * Detail konfigurasi visual status berdasarkan statusType
     *
     * @return array{
     *     code: string,
     *     title: string,
     *     subtitle: string,
     *     badge_label: string,
     *     badge_color: string,
     *     image_light: string,
     *     image_dark: string,
     *     icon: string
     * }
     */
    public function getMetaProperty(): array
    {
        return match ($this->statusType) {
            'not_found' => [
                'code' => '404',
                'title' => 'Tautan Formulir Tidak Ditemukan',
                'subtitle' => 'Maaf, tautan formulir visa yang Anda tuju tidak terdaftar di sistem kami, salah ketik, atau telah dipindahkan.',
                'badge_label' => 'Link Tidak Ditemukan',
                'badge_color' => 'red',
                'image_light' => '/images/error/404.svg',
                'image_dark' => '/images/error/404-dark.svg',
                'icon' => 'link-slash',
            ],
            'expired' => [
                'code' => '410',
                'title' => 'Masa Berlaku Pengisian Telah Berakhir',
                'subtitle' => 'Tautan pengisian formulir ini memiliki batas waktu dan telah kedaluwarsa pada '.($this->link?->expired_at ? $this->link->expired_at->translatedFormat('d F Y, H:i') : '-').'.',
                'badge_label' => 'Masa Berlaku Habis',
                'badge_color' => 'amber',
                'image_light' => '/images/error/503.svg',
                'image_dark' => '/images/error/503-dark.svg',
                'icon' => 'clock',
            ],
            'exhausted' => [
                'code' => '429',
                'title' => 'Batas Pengisian Formulir Telah Tercapai',
                'subtitle' => 'Tautan ini telah digunakan untuk menyelesaikan pengajuan formulir aplikasi visa (kuota terpakai: '.($this->link?->used_count ?? 1).' dari '.($this->link?->max_usage ?? 1).' pengajuan).',
                'badge_label' => 'Batas Kuota Tercapai',
                'badge_color' => 'emerald',
                'image_light' => '/images/error/503.svg',
                'image_dark' => '/images/error/503-dark.svg',
                'icon' => 'check-circle',
            ],
            'inactive' => [
                'code' => '403',
                'title' => 'Tautan Formulir Dinonaktifkan',
                'subtitle' => 'Tautan pengajuan formulir ini saat ini berstatus nonaktif oleh administrator.',
                'badge_label' => 'Tautan Nonaktif',
                'badge_color' => 'zinc',
                'image_light' => '/images/error/500.svg',
                'image_dark' => '/images/error/500-dark.svg',
                'icon' => 'no-symbol',
            ],
            default => [
                'code' => '200',
                'title' => 'Tautan Formulir Masih Aktif',
                'subtitle' => 'Tautan formulir ini masih dalam masa aktif dan dapat digunakan untuk pengisian aplikasi visa.',
                'badge_label' => 'Tautan Aktif',
                'badge_color' => 'blue',
                'image_light' => '/images/error/503.svg',
                'image_dark' => '/images/error/503-dark.svg',
                'icon' => 'check',
            ],
        };
    }

    public function render()
    {
        return view('livewire.form.visa-form-status-page', [
            'meta' => $this->meta,
            'whatsappUrl' => $this->whatsappUrl,
        ])->extends('layouts.fullscreen-layout')->section('content');
    }
}
