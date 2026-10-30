<div
    x-data="{
        activeCategory: 'all',
        categoryCounts: {{ json_encode($categoryCounts) }},
        totalFiles: {{ $attachments->count() }},
        previewOpen: false,
        previewUrl: '',
        previewTitle: '',
        previewType: 'image',
        previewZoom: 1,
        openPreview(url, title, type) {
            this.previewUrl = url;
            this.previewTitle = title || 'Pratinjau Dokumen';
            this.previewType = type || 'image';
            this.previewZoom = 1;
            this.previewOpen = true;
            document.body.classList.add('preview-modal-open');
        },
        closePreview() {
            this.previewOpen = false;
            document.body.classList.remove('preview-modal-open');
            this.previewUrl = '';
            this.previewTitle = '';
            this.previewZoom = 1;
        },
        zoomIn() {
            if (this.previewZoom < 3) this.previewZoom = +(this.previewZoom + 0.25).toFixed(2);
        },
        zoomOut() {
            if (this.previewZoom > 0.5) this.previewZoom = +(this.previewZoom - 0.25).toFixed(2);
        },
        resetZoom() {
            this.previewZoom = 1;
        },
        printImage(url, title) {
            if (typeof window.printAttachmentImage === 'function') {
                window.printAttachmentImage(url, title);
            }
        },
        confirmDelete(id, name) {
            Swal.fire({
                title: 'Hapus Berkas?',
                text: 'Anda yakin ingin menghapus berkas ' + name + '? Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.deleteAttachment(id);
                }
            });
        }
    }"
    @keydown.escape.window="closePreview()"
    class="space-y-6"
>
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('visa_form.index') }}"
               class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400 transition mb-2">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Kembali ke Data Permohonan Visa</span>
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                    Kelola Berkas Lampiran
                </h1>
                @php
                    $statusEnum = $visaForm->status instanceof \App\Enums\Form\VisaFormStatus
                        ? $visaForm->status
                        : \App\Enums\Form\VisaFormStatus::fromLabel((string) $visaForm->status);
                    $badgeColor = match($statusEnum) {
                        \App\Enums\Form\VisaFormStatus::DRAFT => 'zinc',
                        \App\Enums\Form\VisaFormStatus::DIAJUKAN => 'blue',
                        \App\Enums\Form\VisaFormStatus::SIAP_VERIFIKASI => 'amber',
                        \App\Enums\Form\VisaFormStatus::VERIFIKASI, \App\Enums\Form\VisaFormStatus::SELESAI => 'emerald',
                        \App\Enums\Form\VisaFormStatus::DITOLAK => 'red',
                        default => 'zinc',
                    };
                @endphp
                <flux:badge variant="pill" size="sm" color="{{ $badgeColor }}">
                    {{ $statusEnum?->label() ?? (string) $visaForm->status }}
                </flux:badge>
            </div>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                Kelola, unggah berkas baru, dan pratinjau seluruh dokumen permohonan visa untuk pemohon ini.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="subtle" size="sm" icon="arrow-path" wire:click="loadVisaForm">
                Segarkan
            </flux:button>
        </div>
    </div>

    <!-- Applicant Summary Card -->
    <div class="rounded-xl border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Col 1: Nama & No Reg -->
            <div>
                <span class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Nama Pemohon</span>
                <div class="text-base font-semibold text-gray-900 dark:text-white mt-0.5">
                    {{ $visaForm->nama_lengkap }}
                </div>
                <div class="font-mono text-xs text-brand-600 dark:text-brand-400 mt-0.5">
                    {{ $visaForm->nomor_registrasi }}
                </div>
            </div>

            <!-- Col 2: Paspor & COE -->
            <div>
                <span class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Dokumen Identitas</span>
                <div class="text-sm text-gray-800 dark:text-zinc-200 mt-0.5">
                    <span class="font-medium">Paspor:</span> {{ $visaForm->nomor_paspor ?: '-' }}
                </div>
                <div class="text-sm text-gray-800 dark:text-zinc-200 mt-0.5">
                    <span class="font-medium">COE:</span> {{ $visaForm->nomor_coe ?: '-' }}
                </div>
            </div>

            <!-- Col 3: Kaisha & Tanggal -->
            <div>
                <span class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Kaisha / Penerima</span>
                <div class="text-sm font-medium text-gray-800 dark:text-zinc-200 mt-0.5 truncate" title="{{ $visaForm->nama_kaisha }}">
                    {{ $visaForm->nama_kaisha ?: '-' }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Kedatangan: {{ $visaForm->tanggal_kedatangan ? $visaForm->tanggal_kedatangan->format('d/m/Y') : '-' }}
                </div>
            </div>

            <!-- Col 4: Statistik Berkas -->
            @php
                $totalFiles = $visaForm->attachments->count();
                $totalSize = $visaForm->attachments->sum('file_size');
            @endphp
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-zinc-800/60 rounded-lg p-3 border border-gray-100 dark:border-zinc-800">
                <div class="w-10 h-10 rounded-lg bg-brand-100 dark:bg-brand-950/60 flex items-center justify-center text-brand-600 dark:text-brand-400 shrink-0">
                    <span class="material-symbols-outlined text-2xl">folder</span>
                </div>
                <div class="min-w-0">
                    <div class="text-sm font-bold text-gray-900 dark:text-white">
                        {{ $totalFiles }} Berkas Terlampir
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                        Total Ukuran: {{ $this->formatBytes($totalSize) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Section Card -->
    @if ($isCanUpdate)
        <div class="rounded-xl border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-brand-600 dark:text-brand-400">cloud_upload</span>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Unggah Berkas Baru</h2>
            </div>

            <form wire:submit.prevent="saveUpload" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Kategori Berkas -->
                    <div>
                        <label for="uploadType" class="block text-xs font-medium text-gray-700 dark:text-zinc-300 mb-1.5">
                            Jenis Dokumen <span class="text-rose-500">*</span>
                        </label>
                        <flux:select wire:model="uploadType" id="uploadType">
                            @foreach ($attachmentTypes as $type)
                                <flux:select.option value="{{ $type->value }}">
                                    {{ $type->label() }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <!-- Input Berkas -->
                    <div>
                        <label for="uploadedFiles" class="block text-xs font-medium text-gray-700 dark:text-zinc-300 mb-1.5">
                            Pilih File (Bisa Multiple) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="file"
                            id="uploadedFiles"
                            wire:model="uploadedFiles"
                            multiple
                            accept=".jpg,.jpeg,.png,.webp,.pdf"
                            class="block w-full text-xs text-gray-500 dark:text-zinc-400
                                   file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0
                                   file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700
                                   hover:file:bg-brand-100 dark:file:bg-brand-950/60 dark:file:text-brand-300
                                   border border-gray-300 dark:border-zinc-700 rounded-lg p-1.5 cursor-pointer bg-white dark:bg-zinc-800"
                        />
                        <div class="text-[11px] text-gray-400 mt-1">
                            Format: JPG, PNG, WEBP (Max 10MB) atau PDF (Max 2MB).
                        </div>
                    </div>

                    <!-- Catatan Opsional -->
                    <div>
                        <label for="catatan" class="block text-xs font-medium text-gray-700 dark:text-zinc-300 mb-1.5">
                            Catatan Berkas (Opsional)
                        </label>
                        <flux:input wire:model="catatan" id="catatan" placeholder="Contoh: Halaman tambahan paspor..." />
                    </div>
                </div>

                <!-- Livewire File Upload Loading State -->
                <div wire:loading wire:target="uploadedFiles" class="text-xs text-brand-600 dark:text-brand-400 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                    <span>Mengunggah file ke browser...</span>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-zinc-800">
                    <flux:button type="submit" variant="primary" icon="cloud-arrow-up" wire:loading.attr="disabled" wire:target="saveUpload, uploadedFiles">
                        Simpan & Unggah Berkas
                    </flux:button>
                </div>
            </form>
        </div>
    @endif

    <!-- Category Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-gray-200 dark:border-zinc-800">
        <button
            type="button"
            @click="activeCategory = 'all'"
            :class="activeCategory === 'all'
                ? 'bg-brand-600 text-white shadow-sm'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700'"
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-medium whitespace-nowrap transition cursor-pointer"
        >
            <span>Semua</span>
            <span
                :class="activeCategory === 'all'
                    ? 'bg-white/20 text-white'
                    : 'bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-zinc-300'"
                class="px-1.5 py-0.5 rounded-full text-[10px]"
            >
                {{ $attachments->count() }}
            </span>
        </button>

        @foreach ($attachmentTypes as $type)
            @php
                $count = $categoryCounts[$type->value] ?? 0;
            @endphp
            <button
                type="button"
                @click="activeCategory = '{{ $type->value }}'"
                :class="activeCategory === '{{ $type->value }}'
                    ? 'bg-brand-600 text-white shadow-sm'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700'"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-medium whitespace-nowrap transition cursor-pointer"
            >
                <span>{{ $type->label() }}</span>
                <span
                    :class="activeCategory === '{{ $type->value }}'
                        ? 'bg-white/20 text-white'
                        : 'bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-zinc-300'"
                    class="px-1.5 py-0.5 rounded-full text-[10px]"
                >
                    {{ $count }}
                </span>
            </button>
        @endforeach
    </div>

    <!-- Document Cards Grid -->
    <div>
        <!-- Empty State per Kategori -->
        <div
            x-show="(activeCategory === 'all' && totalFiles === 0) || (activeCategory !== 'all' && (categoryCounts[activeCategory] || 0) === 0)"
            x-cloak
            class="rounded-xl border border-dashed border-gray-300 dark:border-zinc-800 p-12 text-center bg-gray-50/50 dark:bg-zinc-900/50"
        >
            <span class="material-symbols-outlined text-4xl text-gray-400 dark:text-gray-500 mb-2">folder_off</span>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-zinc-200">Tidak ada berkas pada kategori ini</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Silakan unggah berkas baru menggunakan form unggah di atas.
            </p>
        </div>

        @if ($attachments->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($attachments as $att)
                    @php
                        $ext = strtolower($att->extension ?: pathinfo($att->nama_file_asli, PATHINFO_EXTENSION));
                        $isPdf = $ext === 'pdf' || $att->mime_type === 'application/pdf';
                        $url = $att->url;
                        $jenisValue = $att->jenis_dokumen instanceof \App\Enums\Form\VisaAttachmentType
                            ? $att->jenis_dokumen->value
                            : (string) $att->jenis_dokumen;
                        $jenisLabel = $att->jenis_dokumen instanceof \App\Enums\Form\VisaAttachmentType
                            ? $att->jenis_dokumen->label()
                            : \App\Enums\Form\VisaAttachmentType::tryFrom((string) $att->jenis_dokumen)?->label() ?? (string) $att->jenis_dokumen;
                    @endphp
                    <div
                        x-show="activeCategory === 'all' || activeCategory === '{{ $jenisValue }}'"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="group rounded-xl border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                    >
                        <!-- Preview Thumbnail Area -->
                        <div
                            class="relative h-44 w-full bg-slate-900 flex items-center justify-center overflow-hidden cursor-pointer select-none"
                            @click="openPreview('{{ $url }}', '{{ addslashes($att->nama_file_asli) }}', '{{ $isPdf ? 'pdf' : 'image' }}')"
                            title="Klik untuk pratinjau fullscreen"
                        >
                            @if (! $isPdf && $url)
                                <img
                                    src="{{ $url }}"
                                    alt="{{ $att->nama_file_asli }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                    loading="lazy"
                                />
                            @else
                                <div class="flex flex-col items-center justify-center p-4 text-center">
                                    
                                <iframe
                                    src="{{  $url }}"
                                    class="w-full h-full border-0 bg-white"
                                    style="width: 100% !important; height: 100% !important; min-height: 100% !important; border: none !important;"
                                    title="PDF Viewer"
                                    allowfullscreen
                                ></iframe>
                                    {{-- <span class="text-xs font-semibold text-rose-300 uppercase tracking-wider">Dokumen PDF</span> --}}
                                </div>
                            @endif

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/20 backdrop-blur-md text-white text-xs font-semibold border border-white/30">
                                    <span class="material-symbols-outlined text-base">visibility</span>
                                    <span>Pratinjau Fullscreen</span>
                                </span>
                            </div>

                            <!-- Document Type Badge Overlay -->
                            <div class="absolute top-2 left-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider shadow-sm {{ $isPdf ? 'bg-rose-950/80 text-rose-300 border border-rose-700/50' : 'bg-slate-950/80 text-indigo-300 border border-indigo-700/50' }}">
                                    {{ $jenisLabel }}
                                </span>
                            </div>

                            <!-- Ext Badge Overlay -->
                            <div class="absolute top-2 right-2">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-mono font-bold uppercase bg-black/60 text-white backdrop-blur-sm">
                                    {{ $ext }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body & Metadata -->
                        <div class="p-3.5 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-white truncate" title="{{ $att->nama_file_asli }}">
                                    {{ $att->nama_file_asli }}
                                </h4>
                                <div class="flex items-center gap-2 text-[11px] text-gray-500 dark:text-zinc-400 mt-1 font-mono">
                                    <span>{{ $this->formatBytes($att->file_size) }}</span>
                                    <span>•</span>
                                    <span>{{ $att->created_at ? $att->created_at->format('d/m/Y H:i') : '-' }}</span>
                                </div>
                                @if ($att->catatan)
                                    <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1.5 italic line-clamp-2" title="{{ $att->catatan }}">
                                        "{{ $att->catatan }}"
                                    </p>
                                @endif
                            </div>

                            <!-- Card Action Buttons -->
                            <div class="flex items-center justify-between gap-1 pt-3 mt-3 border-t border-gray-100 dark:border-zinc-800/80">
                                <div class="flex items-center gap-1">
                                    <!-- View Preview Button -->
                                    <button
                                        type="button"
                                        @click="openPreview('{{ $url }}', '{{ addslashes($att->nama_file_asli) }}', '{{ $isPdf ? 'pdf' : 'image' }}')"
                                        class="inline-flex items-center justify-center p-1.5 rounded-lg text-gray-600 hover:text-brand-600 hover:bg-brand-50 dark:text-zinc-400 dark:hover:text-brand-400 dark:hover:bg-brand-950/40 transition cursor-pointer"
                                        title="Pratinjau Fullscreen"
                                    >
                                        <span class="material-symbols-outlined text-base">fullscreen</span>
                                    </button>

                                    <!-- Direct Print Button (Only for Non-PDF files) -->
                                    @if (! $isPdf && $url)
                                        <button
                                            type="button"
                                            @click="printImage('{{ $url }}', '{{ addslashes($att->nama_file_asli) }}')"
                                            class="inline-flex items-center justify-center p-1.5 rounded-lg text-gray-600 hover:text-amber-600 hover:bg-amber-50 dark:text-zinc-400 dark:hover:text-amber-400 dark:hover:bg-amber-950/40 transition cursor-pointer"
                                            title="Cetak Berkas (Print)"
                                        >
                                            <span class="material-symbols-outlined text-base">print</span>
                                        </button>
                                    @endif

                                    <!-- Download Button -->
                                    <button
                                        type="button"
                                        wire:click="downloadAttachment({{ $att->id }})"
                                        class="inline-flex items-center justify-center p-1.5 rounded-lg text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 dark:text-zinc-400 dark:hover:text-emerald-400 dark:hover:bg-emerald-950/40 transition cursor-pointer"
                                        title="Unduh Berkas"
                                    >
                                        <span class="material-symbols-outlined text-base">download</span>
                                    </button>

                                    <!-- Open Direct Tab -->
                                    @if ($url)
                                        <a
                                            href="{{ $url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center justify-center p-1.5 rounded-lg text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 dark:text-zinc-400 dark:hover:text-indigo-400 dark:hover:bg-indigo-950/40 transition cursor-pointer"
                                            title="Buka URL Berkas di Tab Baru"
                                        >
                                            <span class="material-symbols-outlined text-base">open_in_new</span>
                                        </a>
                                    @endif
                                </div>

                                <!-- Delete Button -->
                                @if ($isCanDelete)
                                    <button
                                        type="button"
                                        @click="confirmDelete({{ $att->id }}, '{{ addslashes($att->nama_file_asli) }}')"
                                        class="inline-flex items-center justify-center p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:text-rose-400 dark:hover:bg-rose-950/40 transition cursor-pointer"
                                        title="Hapus Berkas"
                                    >
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Unified Document & Image Preview Modal (Replicated from resources/views/livewire/example/attachment.blade.php) -->
    <template x-teleport="body">
        <div
            id="gensen-preview-modal"
            x-show="previewOpen"
            x-cloak
            :style="!previewOpen ? 'display: none !important;' : 'display: flex !important;'"
            role="dialog"
            aria-modal="true"
        >
            <!-- Top Header Bar (Full 100vw width, sleek solid dark styling) -->
            <div class="gensen-modal-header">
                <!-- Left: Badge & Title -->
                <div class="flex items-center gap-3 min-w-0">
                    <span x-show="previewType === 'pdf'" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider text-rose-300 border border-rose-500/30 shadow-sm" style="background-color: rgba(244, 63, 94, 0.2); border-color: rgba(244, 63, 94, 0.4);">
                        <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                        <span>PDF</span>
                    </span>
                    <span x-show="previewType === 'image'" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider text-indigo-300 border border-indigo-500/30 shadow-sm" style="background-color: rgba(99, 102, 241, 0.2); border-color: rgba(99, 102, 241, 0.4);">
                        <span class="material-symbols-outlined text-base">image</span>
                        <span>IMAGE</span>
                    </span>
                    <h2
                        x-text="previewTitle"
                        class="text-sm sm:text-base font-semibold text-white truncate max-w-[40vw] sm:max-w-[65vw]"
                        :title="previewTitle"
                        style="color: #ffffff !important; margin: 0 !important;"
                    ></h2>
                </div>

                <!-- Right: Action Controls -->
                <div class="flex items-center gap-2 shrink-0">
                    <!-- Image Zoom Controls (only shown for images) -->
                    <div
                        x-show="previewType === 'image'"
                        :style="previewType === 'image' ? 'display: flex !important;' : 'display: none !important;'"
                        class="gensen-zoom-controls"
                    >
                        <button
                            type="button"
                            @click="zoomOut()"
                            :disabled="previewZoom <= 0.5"
                            class="w-8 h-8 inline-flex items-center justify-center rounded-md hover:bg-white/15 disabled:opacity-30 disabled:hover:bg-transparent transition-all text-white border-0 bg-transparent cursor-pointer"
                            title="Perkecil (-)"
                        >
                            <span class="material-symbols-outlined text-base">zoom_out</span>
                        </button>
                        <span
                            x-text="Math.round(previewZoom * 100) + '%'"
                            @click="resetZoom()"
                            class="px-2 text-xs font-mono font-medium cursor-pointer text-white hover:text-indigo-300"
                            title="Reset Zoom (100%)"
                        ></span>
                        <button
                            type="button"
                            @click="zoomIn()"
                            :disabled="previewZoom >= 3"
                            class="w-8 h-8 inline-flex items-center justify-center rounded-md hover:bg-white/15 disabled:opacity-30 disabled:hover:bg-transparent transition-all text-white border-0 bg-transparent cursor-pointer"
                            title="Perbesar (+)"
                        >
                            <span class="material-symbols-outlined text-base">zoom_in</span>
                        </button>
                    </div>

                    <!-- Direct Print Button (Only for Non-PDF files) -->
                    <button
                        type="button"
                        x-show="previewType !== 'pdf'"
                        @click="printImage(previewUrl, previewTitle)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-all border shadow-sm cursor-pointer hover:bg-white/20"
                        style="background-color: rgba(255, 255, 255, 0.12); border-color: rgba(255, 255, 255, 0.2); color: #ffffff !important;"
                        title="Cetak Dokumen Ini"
                    >
                        <span class="material-symbols-outlined text-base">print</span>
                        <span class="hidden md:inline">Cetak</span>
                    </button>

                    <!-- Open in New Tab -->
                    <a
                        :href="previewUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-all border shadow-sm cursor-pointer"
                        style="background-color: rgba(255, 255, 255, 0.12); border-color: rgba(255, 255, 255, 0.2); color: #ffffff !important; text-decoration: none;"
                        title="Buka dokumen di tab baru"
                    >
                        <span class="material-symbols-outlined text-base">open_in_new</span>
                        <span class="hidden md:inline">Buka Tab Baru</span>
                    </a>

                    <!-- Close Button -->
                    <button
                        type="button"
                        @click="closePreview()"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-white transition-all border shadow-sm hover:bg-rose-600 cursor-pointer"
                        style="background-color: rgba(255, 255, 255, 0.12); border-color: rgba(255, 255, 255, 0.2); color: #ffffff !important;"
                        title="Tutup (Esc)"
                    >
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
            </div>

            <!-- Modal Content Body (Full 100vw x remaining 100vh height) -->
            <div class="gensen-modal-body">
                <!-- Image Viewer with Zoom & Pan -->
                <div
                    x-show="previewType === 'image'"
                    :style="previewType === 'image' ? 'display: flex !important;' : 'display: none !important;'"
                    class="gensen-image-stage"
                >
                    <img
                        x-show="previewType === 'image'"
                        :src="previewType === 'image' ? previewUrl : ''"
                        :alt="previewTitle"
                        class="max-w-full max-h-full object-contain rounded-lg shadow-2xl transition-transform duration-150 ease-out select-none"
                        :style="'transform: scale(' + previewZoom + '); transform-origin: center center; max-height: calc(100vh - 7rem); max-width: calc(100vw - 3rem);'"
                    />
                    <!-- Floating Hint -->
                    <div class="gensen-floating-hint">
                        <span class="gensen-dot-pulse"></span>
                        <span x-text="previewTitle" class="max-w-[200px] sm:max-w-[400px] truncate"></span>
                        <span style="opacity: 0.3;">|</span>
                        <span class="hidden sm:inline">Gunakan zoom atau tekan <kbd class="gensen-kbd">ESC</kbd> untuk menutup</span>
                        <span class="sm:hidden">Tekan ESC untuk tutup</span>
                    </div>
                </div>

                <!-- PDF Viewer (100% full height & width native iframe) -->
                <div
                    x-show="previewType === 'pdf'"
                    :style="previewType === 'pdf' ? 'display: flex !important;' : 'display: none !important;'"
                    class="gensen-pdf-stage"
                >
                    <iframe
                        :src="previewType === 'pdf' ? previewUrl : ''"
                        class="w-full h-full border-0 bg-white"
                        style="width: 100% !important; height: 100% !important; min-height: 100% !important; border: none !important;"
                        title="PDF Viewer"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </div>
    </template>
</div>

@push('css')
    <style>
        body.preview-modal-open {
            overflow: hidden !important;
        }
        body.preview-modal-open #kt_app_header,
        body.preview-modal-open #kt_app_sidebar,
        body.preview-modal-open .app-header,
        body.preview-modal-open .app-sidebar,
        body.preview-modal-open #kt_app_wrapper {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            z-index: -1 !important;
            display: none !important;
        }
        #gensen-preview-modal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
            z-index: 2147483647 !important;
            background-color: #020617 !important;
            color: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        #gensen-preview-modal[style*="display: none"] {
            display: none !important;
        }
        #gensen-preview-modal:not([style*="display: none"]) {
            display: flex !important;
            flex-direction: column !important;
        }
        #gensen-preview-modal .gensen-modal-header {
            height: 56px !important;
            min-height: 56px !important;
            max-height: 56px !important;
            background-color: #0f172a !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 1.5rem !important;
            width: 100vw !important;
            box-sizing: border-box !important;
            flex-shrink: 0 !important;
            z-index: 30 !important;
        }
        #gensen-preview-modal .gensen-modal-body {
            flex: 1 1 0% !important;
            width: 100vw !important;
            height: calc(100vh - 56px) !important;
            max-height: calc(100vh - 56px) !important;
            background-color: #020617 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            overflow: hidden !important;
            position: relative !important;
            box-sizing: border-box !important;
        }
        #gensen-preview-modal .gensen-zoom-controls {
            align-items: center !important;
            gap: 0.25rem !important;
            background-color: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 0.5rem !important;
            padding: 0.125rem !important;
        }
        #gensen-preview-modal .gensen-zoom-controls[style*="display: none"] {
            display: none !important;
        }
        #gensen-preview-modal .gensen-zoom-controls:not([style*="display: none"]) {
            display: flex !important;
        }
        @media (max-width: 640px) {
            #gensen-preview-modal .gensen-zoom-controls {
                display: none !important;
            }
        }
        #gensen-preview-modal .gensen-image-stage {
            width: 100% !important;
            height: 100% !important;
            align-items: center !important;
            justify-content: center !important;
            overflow: auto !important;
            padding: 1.5rem !important;
            box-sizing: border-box !important;
            background: radial-gradient(circle at center, #0f172a 0%, #020617 100%) !important;
            position: relative !important;
        }
        #gensen-preview-modal .gensen-image-stage[style*="display: none"] {
            display: none !important;
        }
        #gensen-preview-modal .gensen-image-stage:not([style*="display: none"]) {
            display: flex !important;
        }
        #gensen-preview-modal .gensen-pdf-stage {
            width: 100% !important;
            height: 100% !important;
            background-color: #0f172a !important;
            flex-direction: column !important;
            box-sizing: border-box !important;
        }
        #gensen-preview-modal .gensen-pdf-stage[style*="display: none"] {
            display: none !important;
        }
        #gensen-preview-modal .gensen-pdf-stage:not([style*="display: none"]) {
            display: flex !important;
        }
        #gensen-preview-modal .gensen-pdf-stage iframe {
            width: 100% !important;
            height: 100% !important;
            min-height: 100% !important;
            border: none !important;
            background-color: #ffffff !important;
        }
        #gensen-preview-modal .gensen-floating-hint {
            position: absolute !important;
            bottom: 1.25rem !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            padding: 0.375rem 1rem !important;
            border-radius: 9999px !important;
            font-size: 0.75rem !important;
            font-weight: 500 !important;
            letter-spacing: 0.025em !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.625rem !important;
            pointer-events: none !important;
            user-select: none !important;
            background-color: rgba(15, 23, 42, 0.9) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #cbd5e1 !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
        }
        #gensen-preview-modal .gensen-dot-pulse {
            width: 8px !important;
            height: 8px !important;
            border-radius: 50% !important;
            background-color: #34d399 !important;
            display: inline-block !important;
            animation: pulse-dot 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite !important;
        }
        #gensen-preview-modal .gensen-kbd {
            padding: 0.125rem 0.375rem !important;
            border-radius: 0.25rem !important;
            font-size: 10px !important;
            font-family: monospace !important;
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.15) !important;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        window.printAttachmentImage = function(url, title) {
            if (!url) return;

            let printFrame = document.getElementById('printAttachmentIframe');
            if (!printFrame) {
                printFrame = document.createElement('iframe');
                printFrame.id = 'printAttachmentIframe';
                printFrame.style.position = 'fixed';
                printFrame.style.right = '0';
                printFrame.style.bottom = '0';
                printFrame.style.width = '0';
                printFrame.style.height = '0';
                printFrame.style.border = '0';
                printFrame.style.visibility = 'hidden';
                document.body.appendChild(printFrame);
            }

            const doc = printFrame.contentWindow.document;
            doc.head.innerHTML = '';
            doc.body.innerHTML = '';

            const style = doc.createElement('style');
            style.textContent = '@page { size: auto; margin: 10mm; } body { margin: 0; padding: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #ffffff; } img { max-width: 100%; max-height: 98vh; object-fit: contain; display: block; }';
            doc.head.appendChild(style);

            const targetImg = doc.createElement('img');
            targetImg.id = 'printImgTarget';
            targetImg.src = url;
            doc.body.appendChild(targetImg);

            const execPrint = () => {
                try {
                    printFrame.contentWindow.focus();
                    printFrame.contentWindow.print();
                } catch (e) {
                    console.error('Print error:', e);
                }
            };

            if (targetImg.complete) {
                setTimeout(execPrint, 250);
            } else {
                targetImg.onload = () => setTimeout(execPrint, 250);
                targetImg.onerror = () => {
                    alert('Gagal memuat berkas gambar untuk dicetak.');
                };
            }
        };
    </script>
@endpush

