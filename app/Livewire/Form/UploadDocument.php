<?php

namespace App\Livewire\Form;

use App\Enums\Form\VisaAttachmenStatus;
use App\Enums\Form\VisaAttachmentType;
use App\Enums\Form\VisaFormStatus;
use App\Helpers\Alert;
use App\Models\Form\VisaForm;
use App\Models\Form\VisaFormAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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

    public function updated($propertyName): void
    {
        if (isset($this->slotConfig[$propertyName])) {
            $this->processSlot($propertyName);
        }
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

        // Jika slot single (multiple => false), pertahankan hanya file terbaru
        $isMultiple = $this->slotConfig[$type]['multiple'] ?? true;
        if (! $isMultiple && count($this->$type) > 1) {
            $this->$type = [end($this->$type)];
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

        $this->dispatch('attachment-removed', type: $type, index: $index);
    }

    /**
     * Submit seluruh dokumen yang telah diunggah
     */
    public function submitAllDocuments()
    {
        // 0. Double submit guard: jika formulir sudah bukan DRAFT, langsung redirect ke success
        $statusValue = $this->visaForm->status instanceof VisaFormStatus
            ? $this->visaForm->status->value
            : $this->visaForm->status;

        if ($statusValue !== VisaFormStatus::DRAFT->value && $statusValue !== 'DRAFT') {
            return redirect()->route('form.success', ['hashid' => $this->hashid]);
        }

        $slots = ['coe', 'e_ktln', 'ktp', 'paspor', 'tiket', 'pas_photo'];

        // 1. Validasi Pra-Pemrosesan: Nullable, Required, MIME Type, & File Size
        $rules = [];
        $messages = [];

        foreach ($this->slotConfig as $slot => $cfg) {
            $isRequired = ! empty($cfg['required']);
            $rules[$slot] = $isRequired ? ['required', 'array', 'min:1'] : ['nullable', 'array'];
            $messages["{$slot}.required"] = "Berkas {$cfg['label']} wajib diunggah.";
            $messages["{$slot}.min"] = "Berkas {$cfg['label']} wajib memiliki minimal 1 file.";

            $files = $this->$slot ?? [];
            if (is_array($files)) {
                $acceptPdf = ! empty($cfg['accept_pdf']);
                $mimes = $acceptPdf ? 'jpg,jpeg,png,webp,pdf' : 'jpg,jpeg,png,webp';
                $mimetypes = $acceptPdf
                    ? 'image/jpeg,image/png,image/webp,application/pdf'
                    : 'image/jpeg,image/png,image/webp';

                foreach ($files as $idx => $f) {
                    if (! $f) {
                        continue;
                    }

                    // PDF max 2048KB (2MB), Gambar max 10240KB (10MB), Min 1KB
                    $isPdf = strtolower($f->getClientOriginalExtension()) === 'pdf'
                        || $f->getMimeType() === 'application/pdf';
                    $maxKb = $isPdf ? 2048 : 10240;

                    $rules["{$slot}.*"] = ['nullable', 'file', "mimes:{$mimes}", "max:{$maxKb}", 'min:1'];
                    $messages["{$slot}.{$idx}.mimes"] = "Format file {$f->getClientOriginalName()} tidak didukung.";
                    $messages["{$slot}.{$idx}.mimetypes"] = "Tipe MIME file {$f->getClientOriginalName()} tidak valid.";
                    $messages["{$slot}.{$idx}.max"] = $isPdf
                        ? "Ukuran PDF {$f->getClientOriginalName()} melebihi batas 2MB."
                        : "Ukuran file {$f->getClientOriginalName()} melebihi batas 10MB.";
                    $messages["{$slot}.{$idx}.min"] = "File {$f->getClientOriginalName()} rusak atau kosong (0 KB).";
                }
            }
        }
        $validator = Validator::make($this->all(), $rules, $messages);

        if ($validator->fails()) {
            // Custom console log or dd your messages
            Alert::fail($this, 'Gagal upload berkas', $validator->errors()->all()); // or ->messages()

            return;
        }

        $validatedData = $validator->validated();

        // 2. Tentukan target disk penyimpanan dari config/env (DEFAULT_STORE_DISK)
        $disk = config('filesystems.default_store_disk', env('DEFAULT_STORE_DISK', 'public'));

        $typeMapping = [
            'coe' => VisaAttachmentType::COE,
            'e_ktln' => VisaAttachmentType::E_KTLN,
            'ktp' => VisaAttachmentType::KTP,
            'paspor' => VisaAttachmentType::PASPOR,
            'tiket' => VisaAttachmentType::TIKET,
            'pas_photo' => VisaAttachmentType::PAS_PHOTO,
        ];

        try {
            DB::transaction(function () use ($disk, $slots, $typeMapping) {
                foreach ($slots as $slot) {
                    $files = $this->$slot ?? [];
                    if (! is_array($files)) {
                        $files = $files ? [$files] : [];
                    }

                    foreach ($files as $index => $file) {
                        if (! $file) {
                            continue;
                        }

                        // Validasi keberadaan fisik file dan status valid
                        if (! method_exists($file, 'isValid') || ! $file->isValid()) {
                            throw new \RuntimeException("Berkas {$file->getClientOriginalName()} tidak valid atau sesi upload telah kedaluwarsa.");
                        }

                        $originalName = $file->getClientOriginalName();
                        $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION));
                        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
                        $size = $file->getSize() ?: 0;

                        // Simpan file ke disk yang dikonfigurasi
                        $storedPath = $file->store("visa-documents/{$this->visaForm->id}/{$slot}", $disk);
                        $storedFilename = basename($storedPath);

                        // Simpan metadata ke tabel visa_form_attachments menggunakan nama kolom bahasa Inggris

                        VisaFormAttachment::create([
                            'visa_form_id' => $this->visaForm->id,
                            'jenis_dokumen' => $typeMapping[$slot] ?? VisaAttachmentType::COE,
                            'nama_file_asli' => $originalName,
                            'nama_file_tersimpan' => $storedFilename,
                            'path' => $storedPath,
                            'disk' => $disk,
                            'extension' => $extension,
                            'mime_type' => $mimeType,
                            'file_size' => $size,
                            'status' => VisaAttachmenStatus::STATUS_STORED,
                            'urutan' => $index + 1,
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            report($e);
            Alert::fail($this, 'Gagal Menyimpan Berkas', 'Terjadi kesalahan sistem saat menyimpan berkas: '.$e->getMessage());

            return null;
        }

        $this->is_submitted = true;
        $this->dispatch('documents-submitted');

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
