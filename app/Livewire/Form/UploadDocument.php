<?php

namespace App\Livewire\Form;

use App\Enums\Form\VisaAttachmenStatus;
use App\Enums\Form\VisaAttachmentType;
use App\Enums\Form\VisaFormStatus;
use App\Helpers\Alert;
use App\Models\Form\VisaForm;
use App\Models\Form\VisaFormAttachment;
use Livewire\Component;
use Livewire\WithFileUploads;
use Vinkla\Hashids\Facades\Hashids;

class UploadDocument extends Component
{
    use WithFileUploads;

    // Slot berkas dokumen
    public $coe = [];

    public $e_ktln = [];

    public $ktp = [];

    public $paspor = [];

    public $tiket = [];

    public $pas_photo = [];

    // Buffer upload incremental dari Alpine.js (mencegah overwrite saat multiple upload)
    public ?string $incomingSlot = null;

    public $incomingFile = null;

    // Tracking slot yang sudah memiliki file tersimpan
    public array $uploadedTypes = [];

    // Status form akhir
    public bool $is_submitted = false;

    // Reference Hashid & VisaForm
    public string $hashid = '';

    public ?int $formId = null;

    public ?VisaForm $visaForm = null;

    public ?string $clientName = null;

    public ?string $link = null;

    public function mount(string $hashid)
    {
        $this->hashid = $hashid;
        $decoded = Hashids::decode($hashid);
        $this->formId = $decoded[0] ?? null;

        if (! $this->formId) {
            abort(404, 'Formulir pengajuan tidak ditemukan.');
        }

        $form = VisaForm::with('link')->find($this->formId);

        if (! $form) {
            abort(404, 'Formulir pengajuan tidak ditemukan.');
        }

        // Access guard: hanya bisa diakses saat status DRAFT, selain itu redirect ke success page
        $statusValue = $form->status instanceof VisaFormStatus
            ? $form->status->value
            : $form->status;

        if ($statusValue !== VisaFormStatus::DRAFT->value && $statusValue !== 'DRAFT') {
            return redirect()->route('form.success', ['hashid' => $this->hashid]);
        }

        $this->visaForm = $form;
        $this->clientName = $form->nama_lengkap;
        $this->link = $form->visa_form_link_id ? Hashids::encode($form->visa_form_link_id) : null;
    }

    /**
     * Konfigurasi setiap slot berkas dokumen.
     * Default: SEMUA MULTIPLE dan SEMUA OPSIONAL (dapat diubah manual sewaktu-waktu).
     */
    public array $slotConfig = [
        'coe' => [
            'label' => 'Photo COE (Certificate of Eligibility)',
            'description' => 'Dokumen Certificate of Eligibility asli dari imigrasi Jepang.',
            'icon' => 'card_membership',
            'required' => false,
            'multiple' => true,
            'accept_pdf' => true,
        ],
        'e_ktln' => [
            'label' => 'E-KTLN (Tenaga Kerja Luar Negeri)',
            'description' => 'Elektronik Kartu Tenaga Kerja Luar Negeri yang diterbitkan BP2MI.',
            'icon' => 'badge',
            'required' => false,
            'multiple' => true,
            'accept_pdf' => true,
        ],
        'ktp' => [
            'label' => 'KTP Indonesia',
            'description' => 'Foto e-KTP Indonesia asli, terbaca jelas, tidak terpotong dan tidak buram.',
            'icon' => 'credit_card',
            'required' => false,
            'multiple' => true,
            'accept_pdf' => false,
        ],
        'paspor' => [
            'label' => 'Photo Paspor',
            'description' => 'Halaman identitas diri serta halaman visa/stempel kunjungan jika ada.',
            'icon' => 'menu_book',
            'required' => false,
            'multiple' => true,
            'accept_pdf' => true,
        ],
        'tiket' => [
            'label' => 'Berkas Tiket Penerbangan (Opsional)',
            'description' => 'E-tiket penerbangan atau bukti booking tiket ke bandara Jepang.',
            'icon' => 'flight_takeoff',
            'required' => false,
            'multiple' => true,
            'accept_pdf' => true,
        ],
        'pas_photo' => [
            'label' => 'Pas Photo Latar Putih',
            'description' => 'Pas foto formal terbaru dengan pakaian berkerah/kemeja, latar putih polos.',
            'icon' => 'portrait',
            'required' => false,
            'multiple' => true,
            'accept_pdf' => false,
        ],
    ];

    public function getSlotConfig(): array
    {
        return $this->slotConfig;
    }

    public function setSlotRequired(string $slot, bool $required): void
    {
        if (isset($this->slotConfig[$slot])) {
            $this->slotConfig[$slot]['required'] = $required;
        }
    }

    public function setSlotMultiple(string $slot, bool $multiple): void
    {
        if (isset($this->slotConfig[$slot])) {
            $this->slotConfig[$slot]['multiple'] = $multiple;
        }
    }

    // Livewire upload hooks
    public function updatedIncomingFile(): void
    {
        if ($this->incomingFile && $this->incomingSlot) {
            $slot = $this->incomingSlot;
            if (! isset($this->slotConfig[$slot])) {
                return;
            }

            $isMultiple = $this->slotConfig[$slot]['multiple'] ?? true;

            if ($isMultiple) {
                $current = is_array($this->$slot) ? $this->$slot : [];
                $current[] = $this->incomingFile;
                $this->$slot = $current;
            } else {
                $this->$slot = [$this->incomingFile];
            }

            $this->processSlot($slot);
            $this->incomingFile = null;
            $this->incomingSlot = null;
        }
    }

    public function updatedCoe()
    {
        $this->processSlot('coe');
    }

    public function updatedEKtln()
    {
        $this->processSlot('e_ktln');
    }

    public function updatedKtp()
    {
        $this->processSlot('ktp');
    }

    public function updatedPaspor()
    {
        $this->processSlot('paspor');
    }

    public function updatedTiket()
    {
        $this->processSlot('tiket');
    }

    public function updatedPasPhoto()
    {
        $this->processSlot('pas_photo');
    }

    public function processSlot(string $type): void
    {
        $this->handleSlotUploaded($type);
    }

    /**
     * Memvalidasi file-file yang diunggah ke slot tertentu
     */
    public function validateSlotFiles(string $type): void
    {
        $files = $this->$type;
        if (empty($files)) {
            return;
        }

        $fileList = is_array($files) ? $files : [$files];
        $acceptPdf = $this->slotConfig[$type]['accept_pdf'] ?? true;
        $mimes = $acceptPdf ? 'jpg,jpeg,png,webp,pdf' : 'jpg,jpeg,png,webp';

        foreach ($fileList as $index => $file) {
            if (! $file) {
                continue;
            }

            $isPdf = strtolower($file->getClientOriginalExtension()) === 'pdf'
                || $file->getMimeType() === 'application/pdf';

            // PDF max 2MB (2048KB), Image max 10MB (10240KB)
            $maxKb = $isPdf ? 2048 : 10240;

            $rules = [
                "{$type}.{$index}" => ['file', "mimes:{$mimes}", "max:{$maxKb}"],
            ];

            $messages = [
                "{$type}.{$index}.mimes" => "Format file {$file->getClientOriginalName()} tidak didukung.",
                "{$type}.{$index}.max" => $isPdf
                    ? "Ukuran file PDF {$file->getClientOriginalName()} melebihi batas 2MB."
                    : "Ukuran file {$file->getClientOriginalName()} melebihi batas 10MB.",
            ];

            $this->validate($rules, $messages);
        }
    }

    /**
     * Handler saat file selesai diupload ke slot Livewire
     */
    protected function handleSlotUploaded(string $type): void
    {
        // Normalisasi ke array
        if (! is_array($this->$type)) {
            $this->$type = [$this->$type];
        }

        $this->validateSlotFiles($type);

        $files = $this->$type;
        $meta = [];
        foreach ($files as $file) {
            if ($file) {
                $meta[] = [
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size_kb' => round($file->getSize() / 1024, 2),
                ];
            }
        }

        if (! in_array($type, $this->uploadedTypes)) {
            $this->uploadedTypes[] = $type;
        }

        Alert::consoleLog($this, "Attachment Uploaded [{$type}]", [
            'type' => $type,
            'count' => count($files),
            'files' => $meta,
        ]);

        $this->dispatch('attachment-saved', type: $type, count: count($files));
    }

    /**
     * Menghapus file dari slot tertentu
     */
    public function removeFile(string $type, int $index): void
    {
        if (isset($this->$type) && is_array($this->$type)) {
            $current = $this->$type;
            if (isset($current[$index])) {
                array_splice($current, $index, 1);
                $this->$type = $current;
            }

            if (empty($this->$type)) {
                $this->uploadedTypes = array_values(array_diff($this->uploadedTypes, [$type]));
            }
        } elseif (isset($this->$type) && ! is_array($this->$type)) {
            $this->$type = [];
            $this->uploadedTypes = array_values(array_diff($this->uploadedTypes, [$type]));
        }

        Alert::consoleLog($this, "File Removed [{$type}][{$index}]", [
            'type' => $type,
            'index' => $index,
        ]);

        $this->dispatch('attachment-removed', type: $type, index: $index);
    }

    /**
     * Submit seluruh dokumen yang telah diunggah
     */
    public function submitAllDocuments()
    {
        // Cek slot yang wajib tapi belum diupload
        $missingRequired = [];
        foreach ($this->slotConfig as $slot => $cfg) {
            if (! empty($cfg['required'])) {
                $files = $this->$slot ?? [];
                if (empty($files) || (is_array($files) && count(array_filter($files)) === 0)) {
                    $missingRequired[] = $cfg['label'];
                }
            }
        }

        if (! empty($missingRequired)) {
            Alert::fail(
                $this,
                'Dokumen Wajib Belum Lengkap',
                'Harap lengkapi dokumen wajib: '.implode(', ', $missingRequired)
            );

            return null;
        }

        // Simpan setiap berkas ke VisaFormAttachment
        $slots = ['coe', 'e_ktln', 'ktp', 'paspor', 'tiket', 'pas_photo'];
        $typeMapping = [
            'coe' => VisaAttachmentType::COE,
            'e_ktln' => VisaAttachmentType::E_KTLN,
            'ktp' => VisaAttachmentType::KTP,
            'paspor' => VisaAttachmentType::PASPOR,
            'tiket' => VisaAttachmentType::TIKET,
            'pas_photo' => VisaAttachmentType::PAS_PHOTO,
        ];

        foreach ($slots as $slot) {
            $files = $this->$slot ?? [];
            if (! is_array($files)) {
                $files = $files ? [$files] : [];
            }

            foreach ($files as $index => $file) {
                if (! $file) {
                    continue;
                }

                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION);
                $mimeType = $file->getMimeType() ?: 'application/octet-stream';
                $size = $file->getSize() ?: 0;

                // Store file in public disk under visa-documents/{visa_form_id}/{slot}
                $storedPath = $file->store("visa-documents/{$this->visaForm->id}/{$slot}", 'public');
                $storedFilename = basename($storedPath);

                VisaFormAttachment::create([
                    'visa_form_id' => $this->visaForm->id,
                    'jenis_dokumen' => $typeMapping[$slot] ?? VisaAttachmentType::COE,
                    'nama_file_asli' => $originalName,
                    'nama_file_tersimpan' => $storedFilename,
                    'jalur_file' => $storedPath,
                    'disk' => 'public',
                    'ekstensi' => $extension,
                    'tipe_mime' => $mimeType,
                    'ukuran_file' => $size,
                    'status' => VisaAttachmenStatus::STATUS_STORED,
                    'urutan' => $index + 1,
                ]);
            }
        }

        // Update VisaForm status menjadi DIAJUKAN dan tanggal_diajukan
        $this->visaForm->update([
            'status' => VisaFormStatus::DIAJUKAN,
            'tanggal_diajukan' => now(),
        ]);

        $this->is_submitted = true;
        $this->dispatch('documents-submitted');

        Alert::consoleLog($this, [
            'Submit All Documents Success',
            'visaFormId' => $this->visaForm->id,
            'uploadedSlots' => $this->uploadedTypes,
        ]);

        return redirect()->route('form.success', ['hashid' => $this->hashid]);
    }

    public function render()
    {
        return view('livewire.form.upload-document', [
            'slotConfig' => $this->slotConfig,
            'clientName' => $this->clientName,
            'link' => $this->link,
        ])->extends('layouts.fullscreen-layout')->section('content');
    }
}
