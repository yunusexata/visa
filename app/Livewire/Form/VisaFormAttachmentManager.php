<?php

namespace App\Livewire\Form;

use App\Enums\Form\VisaAttachmenStatus;
use App\Enums\Form\VisaAttachmentType;
use App\Helpers\Alert;
use App\Models\Form\VisaForm;
use App\Models\Form\VisaFormAttachment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;

class VisaFormAttachmentManager extends Component
{
    use WithFileUploads;

    public int $visaFormId;

    public ?VisaForm $visaForm = null;

    public string $uploadType = 'paspor';

    public $uploadedFiles = [];

    public ?string $catatan = null;

    public bool $isCanUpdate = false;

    public bool $isCanDelete = false;

    public function mount(int $visaFormId): void
    {
        $this->visaFormId = $visaFormId;
        $this->loadVisaForm();

        /** @var User|null $user */
        $user = Auth::user();
        $this->isCanUpdate = $user ? ($user->hasPermissionTo('visa_form.update') || $user->hasRole(User::ROLE_SUPERADMIN)) : false;
        $this->isCanDelete = $user ? ($user->hasPermissionTo('visa_form.delete') || $user->hasRole(User::ROLE_SUPERADMIN)) : false;
    }

    public function loadVisaForm(): void
    {
        $this->visaForm = VisaForm::with(['attachments' => function ($query) {
            $query->orderBy('jenis_dokumen')->orderBy('urutan');
        }])->findOrFail($this->visaFormId);
    }

    public function saveUpload(): void
    {
        if (! $this->isCanUpdate) {
            Alert::fail($this, 'Akses Ditolak', 'Anda tidak memiliki hak akses untuk mengunggah berkas.');

            return;
        }

        if (empty($this->uploadedFiles)) {
            Alert::fail($this, 'Gagal Unggah', 'Silakan pilih berkas yang ingin diunggah terlebih dahulu.');

            return;
        }

        $files = is_array($this->uploadedFiles) ? $this->uploadedFiles : [$this->uploadedFiles];
        $rules = [];
        $messages = [];

        foreach ($files as $index => $file) {
            if (! $file) {
                continue;
            }

            $isPdf = strtolower($file->getClientOriginalExtension()) === 'pdf' || $file->getMimeType() === 'application/pdf';
            $maxKb = $isPdf ? 2048 : 10240;

            $rules["uploadedFiles.{$index}"] = ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', "max:{$maxKb}"];
            $messages["uploadedFiles.{$index}.mimes"] = "Format file {$file->getClientOriginalName()} tidak didukung (gunakan JPG, PNG, WEBP, atau PDF).";
            $messages["uploadedFiles.{$index}.max"] = $isPdf
                ? "Ukuran PDF {$file->getClientOriginalName()} melebihi batas 2MB."
                : "Ukuran gambar {$file->getClientOriginalName()} melebihi batas 10MB.";
        }

        $validator = Validator::make(['uploadedFiles' => $files], $rules, $messages);

        if ($validator->fails()) {
            Alert::fail($this, 'Validasi Gagal', implode("\n", $validator->errors()->all()));

            return;
        }

        $disk = config('filesystems.default_store_disk', env('DEFAULT_STORE_DISK', 'public'));
        $targetEnum = VisaAttachmentType::tryFrom($this->uploadType) ?? VisaAttachmentType::PASPOR;

        try {
            DB::transaction(function () use ($files, $disk, $targetEnum) {
                $maxUrutan = (int) VisaFormAttachment::where('visa_form_id', $this->visaFormId)
                    ->where('jenis_dokumen', $targetEnum->value)
                    ->max('urutan') ?: 0;

                foreach ($files as $file) {
                    $originalName = $file->getClientOriginalName();
                    $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION));
                    $mimeType = $file->getMimeType() ?: 'application/octet-stream';
                    $size = $file->getSize() ?: 0;

                    $storedPath = $file->store("visa-documents/{$this->visaFormId}/{$targetEnum->value}", $disk);
                    $storedFilename = basename($storedPath);

                    $maxUrutan++;

                    VisaFormAttachment::create([
                        'visa_form_id' => $this->visaFormId,
                        'jenis_dokumen' => $targetEnum,
                        'nama_file_asli' => $originalName,
                        'nama_file_tersimpan' => $storedFilename,
                        'path' => $storedPath,
                        'disk' => $disk,
                        'extension' => $extension,
                        'mime_type' => $mimeType,
                        'file_size' => $size,
                        'status' => VisaAttachmenStatus::STATUS_STORED,
                        'catatan' => $this->catatan,
                        'urutan' => $maxUrutan,
                    ]);
                }
            });

            $count = count($files);
            $this->uploadedFiles = [];
            $this->catatan = null;
            $this->loadVisaForm();

            Alert::success($this, 'Berhasil Diunggah', "{$count} berkas lampiran berhasil disimpan.");
        } catch (\Throwable $e) {
            report($e);
            Alert::fail($this, 'Terjadi Kesalahan', 'Gagal mengunggah berkas: '.$e->getMessage());
        }
    }

    public function deleteAttachment(int $attachmentId): void
    {
        if (! $this->isCanDelete) {
            Alert::fail($this, 'Akses Ditolak', 'Anda tidak memiliki hak akses untuk menghapus berkas.');

            return;
        }

        $attachment = VisaFormAttachment::where('visa_form_id', $this->visaFormId)
            ->findOrFail($attachmentId);

        try {
            if (! empty($attachment->path)) {
                $disk = $attachment->disk ?: config('filesystems.default_store_disk', env('DEFAULT_STORE_DISK', 'public'));
                if (Storage::disk($disk)->exists($attachment->path)) {
                    Storage::disk($disk)->delete($attachment->path);
                }
            }

            $attachment->delete();
            $this->loadVisaForm();

            Alert::success($this, 'Berhasil Dihapus', 'Berkas lampiran berhasil dihapus.');
        } catch (\Throwable $e) {
            report($e);
            Alert::fail($this, 'Gagal Menghapus', 'Terjadi kesalahan saat menghapus berkas: '.$e->getMessage());
        }
    }

    public function downloadAttachment(int $attachmentId)
    {
        $attachment = VisaFormAttachment::where('visa_form_id', $this->visaFormId)
            ->findOrFail($attachmentId);

        $disk = $attachment->disk ?: config('filesystems.default_store_disk', env('DEFAULT_STORE_DISK', 'public'));

        if (! Storage::disk($disk)->exists($attachment->path)) {
            Alert::fail($this, 'Berkas Tidak Ditemukan', 'File fisik tidak ditemukan pada penyimpanan.');

            return null;
        }

        return Storage::disk($disk)->download($attachment->path, $attachment->nama_file_asli);
    }

    public function formatBytes(?int $bytes, int $precision = 2): string
    {
        if (! $bytes || $bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }

    public function render()
    {
        $categoryCounts = $this->visaForm->attachments->groupBy(function ($item) {
            return $item->jenis_dokumen instanceof VisaAttachmentType
                ? $item->jenis_dokumen->value
                : (string) $item->jenis_dokumen;
        })->map->count();

        return view('livewire.form.visa-form-attachment-manager', [
            'attachments' => $this->visaForm->attachments ?? collect(),
            'categoryCounts' => $categoryCounts,
            'attachmentTypes' => VisaAttachmentType::cases(),
        ]);
    }
}
