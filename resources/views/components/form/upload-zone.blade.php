@props([
    'type',                         // string identifier, misal 'coe', 'ktp', 'paspor'
    'label' => 'Unggah Dokumen',
    'description' => '',
    'icon' => 'upload_file',
    'required' => false,
    'multiple' => true,
    'acceptPdf' => true,
])

@php
    $acceptAttr = $acceptPdf
        ? 'image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.heif,application/pdf'
        : 'image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.heif';
@endphp

<div
    x-data="{
        slotType: '{{ $type }}',
        isRequired: {{ $required ? 'true' : 'false' }},
        isMultiple: {{ $multiple ? 'true' : 'false' }},
        acceptPdf: {{ $acceptPdf ? 'true' : 'false' }},
        isDragging: false,
        files: [],
        generalError: null,

        get allDone() {
            return this.files.length > 0 && this.files.every(f => f.stage === 'done');
        },

        get isBusy() {
            return this.files.some(f => f.stage === 'compressing' || f.stage === 'uploading' || f.stage === 'validating');
        },

        triggerBrowse() {
            if (this.$refs.fileInput) {
                this.$refs.fileInput.click();
            }
        },

        onDragOver(e) {
            e.preventDefault();
            this.isDragging = true;
        },

        onDragLeave(e) {
            this.isDragging = false;
        },

        onDrop(e) {
            e.preventDefault();
            this.isDragging = false;
            if (e.dataTransfer && e.dataTransfer.files) {
                this.handleIncomingFiles(e.dataTransfer.files);
            }
        },

        async handleIncomingFiles(fileList) {
            if (!fileList || fileList.length === 0) return;
            this.generalError = null;

            const list = Array.from(fileList);

            // Jika slot single, hapus file lama sebelum memproses yang baru
            if (!this.isMultiple && list.length > 0) {
                this.files.forEach(f => {
                    if (f.previewUrl && !f.isPdf) {
                        try { URL.revokeObjectURL(f.previewUrl); } catch(e){}
                    }
                });
                this.files = [];
            }

            for (const rawFile of list) {
                await this.processAndUpload(rawFile);
            }

            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        async processAndUpload(rawFile) {
            const fileId = 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 7);
            const isPdf = rawFile.type === 'application/pdf' || rawFile.name.toLowerCase().endsWith('.pdf');

            const entry = {
                id: fileId,
                name: rawFile.name,
                originalSizeKB: Math.round(rawFile.size / 1024),
                finalSizeKB: Math.round(rawFile.size / 1024),
                savingsPct: 0,
                previewUrl: null,
                isPdf: isPdf,
                stage: 'validating',
                stageMessage: 'Memvalidasi format...',
                progress: 15,
                error: null,
                serverIndex: null,
            };

            this.files.push(entry);

            // 1. Cek izin PDF pada slot ini
            if (isPdf && !this.acceptPdf) {
                entry.stage = 'error';
                entry.error = 'Jenis dokumen ini harus berupa foto/gambar (JPG/PNG/WEBP/HEIC), format PDF tidak diperbolehkan.';
                return;
            }

            // 2. Validasi format & ukuran via UploadCompress helper
            if (window.UploadCompress && typeof window.UploadCompress.validateFile === 'function') {
                const validation = window.UploadCompress.validateFile(rawFile);
                if (!validation.valid) {
                    entry.stage = 'error';
                    entry.error = validation.error;
                    return;
                }
            }

            // 3. Auto-compress di browser (Web Worker / HEIC converter)
            entry.stage = 'compressing';
            entry.stageMessage = isPdf ? 'Menyiapkan berkas PDF...' : 'Mengompresi foto otomatis...';
            entry.progress = 40;

            let fileToUpload = rawFile;

            if (window.UploadCompress && typeof window.UploadCompress.processUploadFile === 'function') {
                try {
                    const result = await window.UploadCompress.processUploadFile(rawFile, (status) => {
                        entry.stageMessage = status.message || entry.stageMessage;
                        if (status.progress) entry.progress = status.progress;
                    });

                    fileToUpload = result.file;
                    entry.finalSizeKB = result.finalSizeKB;
                    entry.isPdf = result.isPdf;

                    if (result.isCompressed && entry.originalSizeKB > entry.finalSizeKB) {
                        entry.savingsPct = Math.round((1 - (entry.finalSizeKB / entry.originalSizeKB)) * 100);
                    }

                    if (!entry.isPdf) {
                        entry.previewUrl = URL.createObjectURL(fileToUpload);
                    }
                } catch (err) {
                    entry.stage = 'error';
                    entry.error = err.message || 'Gagal memproses dan mengompresi gambar.';
                    return;
                }
            } else {
                // Fallback jika helper belum termuat
                if (!entry.isPdf) {
                    entry.previewUrl = URL.createObjectURL(rawFile);
                }
            }

            // 4. Auto-upload ke backend Livewire
            entry.stage = 'uploading';
            entry.stageMessage = 'Mengunggah ke server...';
            entry.progress = 10;

            try {
                // Set buffer slot tujuan terlebih dahulu
                await $wire.set('incomingSlot', this.slotType, false);

                // Jalankan upload file
                $wire.upload(
                    'incomingFile',
                    fileToUpload,
                    () => {
                        entry.stage = 'done';
                        entry.stageMessage = 'Berhasil diunggah & tersimpan';
                        entry.progress = 100;
                        entry.serverIndex = this.files.filter(f => f.stage === 'done').length - 1;
                    },
                    (err) => {
                        entry.stage = 'error';
                        entry.stageMessage = 'Gagal mengunggah';
                        entry.error = err || 'Terjadi kesalahan saat mengunggah ke server. Coba ulangi.';
                    },
                    (event) => {
                        if (event && event.detail && typeof event.detail.progress !== 'undefined') {
                            entry.progress = event.detail.progress;
                            entry.stageMessage = `Mengunggah... ${event.detail.progress}%`;
                        }
                    }
                );
            } catch (uploadErr) {
                entry.stage = 'error';
                entry.stageMessage = 'Gagal mengunggah';
                entry.error = uploadErr.message || 'Koneksi terputus saat proses upload.';
            }
        },

        removeFile(fileId) {
            const index = this.files.findIndex(f => f.id === fileId);
            if (index === -1) return;

            const target = this.files[index];
            if (target.previewUrl && !target.isPdf) {
                try { URL.revokeObjectURL(target.previewUrl); } catch(e){}
            }

            // Hitung index file yang berhasil diunggah di Livewire
            let serverIdx = target.serverIndex;
            if (serverIdx === null || serverIdx === undefined) {
                // Cari index di antara file-file yang berstatus done
                const doneFiles = this.files.filter(f => f.stage === 'done');
                serverIdx = doneFiles.indexOf(target);
            }

            if (serverIdx >= 0) {
                $wire.removeFile(this.slotType, serverIdx);
            }

            this.files.splice(index, 1);

            // Re-index sisa file yang done
            let currentDoneIdx = 0;
            this.files.forEach(f => {
                if (f.stage === 'done') {
                    f.serverIndex = currentDoneIdx++;
                }
            });
        }
    }"
    class="bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-7 border border-gray-200/80 dark:border-gray-700/80 shadow-sm transition-all duration-300 hover:shadow-md flex flex-col justify-between"
>
    <!-- Card Header -->
    <div class="mb-5">
        <div class="flex items-start justify-between gap-3 mb-2">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-[#001B5E]/5 dark:bg-blue-950/40 text-[#001B5E] dark:text-blue-400 flex items-center justify-center shrink-0 border border-[#001B5E]/10 dark:border-blue-800/40">
                    <span class="material-symbols-outlined text-[24px]">{{ $icon }}</span>
                </div>
                <div>
                    <h4 class="text-base font-bold text-gray-900 dark:text-white leading-tight">
                        {{ $label }}
                    </h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $description ?: 'Format gambar JPG, PNG, WEBP, HEIC' . ($acceptPdf ? ' atau PDF' : '') }}
                    </p>
                </div>
            </div>

            <!-- Badges -->
            <div class="flex items-center gap-1.5 shrink-0">
                @if($required)
                    <span class="px-2.5 py-0.5 text-[11px] font-semibold tracking-wide uppercase rounded-full bg-[#001B5E] text-white">
                        Wajib
                    </span>
                @else
                    <span class="px-2.5 py-0.5 text-[11px] font-medium tracking-wide uppercase rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                        Opsional
                    </span>
                @endif

                @if($multiple)
                    <span class="hidden sm:inline-flex px-2 py-0.5 text-[10px] font-medium rounded-full bg-blue-50 dark:bg-blue-900/30 text-[#0049FF] dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        Multi-Berkas
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Dropzone Interactive Box -->
    <div
        @dragover="onDragOver($event)"
        @dragleave="onDragLeave($event)"
        @drop="onDrop($event)"
        @click="triggerBrowse()"
        :class="{
            'border-[#0049FF] bg-[#0049FF]/[0.04] scale-[1.01] shadow-inner': isDragging,
            'border-amber-400/80 bg-amber-50/40 dark:bg-amber-950/20': isBusy && !isDragging,
            'border-gray-300 dark:border-gray-700 hover:border-[#0049FF]/70 hover:bg-[#0049FF]/[0.02]': !isDragging && !isBusy
        }"
        class="relative cursor-pointer rounded-2xl border-2 border-dashed p-6 sm:p-7 text-center transition-all duration-200 flex flex-col items-center justify-center gap-3.5 group"
    >
        <input
            type="file"
            x-ref="fileInput"
            @change="handleIncomingFiles($event.target.files)"
            :multiple="isMultiple"
            accept="{{ $acceptAttr }}"
            class="hidden"
        />

        <!-- Center Icon with Pulse Animation on Drag -->
        <div
            :class="isDragging ? 'scale-110 bg-[#0049FF] text-white' : 'bg-gray-100 dark:bg-gray-700/80 text-gray-500 dark:text-gray-300 group-hover:bg-[#0049FF]/10 group-hover:text-[#0049FF]'"
            class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-sm"
        >
            <span class="material-symbols-outlined text-[28px]">
                <template x-if="isDragging">
                    <span>download</span>
                </template>
                <template x-if="!isDragging && !isBusy">
                    <span>cloud_upload</span>
                </template>
                <template x-if="!isDragging && isBusy">
                    <span class="animate-spin">sync</span>
                </template>
            </span>
        </div>

        <div>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                <span class="text-[#0049FF] hover:underline">Klik untuk pilih file</span>
                <span class="text-gray-500 dark:text-gray-400 font-normal"> atau seret ke area ini</span>
            </p>
            <p class="text-[12px] text-gray-500 dark:text-gray-400 mt-1">
                Gambar auto-kompres s.d. <strong>500KB</strong>
                @if($acceptPdf)
                    • PDF maks <strong>2MB</strong>
                @endif
            </p>
        </div>

        <button
            type="button"
            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-semibold text-[#001B5E] dark:text-blue-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 shadow-xs hover:border-[#0049FF] group-hover:text-[#0049FF] transition-colors"
        >
            <span class="material-symbols-outlined text-[16px]">add_photo_alternate</span>
            <span>Telusuri File</span>
        </button>
    </div>

    <!-- Uploaded Files List -->
    <div class="mt-4 space-y-2.5" x-show="files.length > 0" x-cloak>
        <template x-for="file in files" :key="file.id">
            <div
                class="rounded-2xl border p-3.5 transition-all duration-200"
                :class="{
                    'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-800': file.stage === 'done',
                    'bg-amber-50/50 dark:bg-amber-950/20 border-amber-300 dark:border-amber-800': file.stage === 'compressing' || file.stage === 'validating',
                    'bg-blue-50/50 dark:bg-blue-950/20 border-blue-300 dark:border-blue-800': file.stage === 'uploading',
                    'bg-rose-50/60 dark:bg-rose-950/20 border-rose-300 dark:border-rose-800': file.stage === 'error'
                }"
            >
                <div class="flex items-center gap-3">
                    <!-- Thumbnail / PDF Icon -->
                    <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 bg-gray-200 dark:bg-gray-700 border border-black/5 flex items-center justify-center">
                        <template x-if="!file.isPdf && file.previewUrl">
                            <img :src="file.previewUrl" :alt="file.name" class="w-full h-full object-cover" />
                        </template>
                        <template x-if="file.isPdf || (!file.previewUrl && !file.isPdf)">
                            <div class="flex flex-col items-center justify-center text-rose-500">
                                <span class="material-symbols-outlined text-[24px]">picture_as_pdf</span>
                                <span class="text-[9px] font-bold uppercase tracking-wider">PDF</span>
                            </div>
                        </template>
                    </div>

                    <!-- File Details & Status -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-semibold text-gray-900 dark:text-gray-100 truncate" :title="file.name" x-text="file.name"></p>
                            
                            <!-- Stage Tag -->
                            <span
                                class="text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0"
                                :class="{
                                    'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200': file.stage === 'done',
                                    'bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200': file.stage === 'compressing' || file.stage === 'validating',
                                    'bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-200': file.stage === 'uploading',
                                    'bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-200': file.stage === 'error'
                                }"
                                x-text="file.stage === 'done' ? 'Tersimpan' : (file.stage === 'error' ? 'Gagal' : (file.stage === 'compressing' ? 'Kompresi' : 'Upload'))"
                            ></span>
                        </div>

                        <!-- Size & Optimization Info -->
                        <div class="flex items-center gap-2 mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                            <span x-text="file.finalSizeKB + ' KB'"></span>
                            <template x-if="file.savingsPct > 0">
                                <span class="text-emerald-600 dark:text-emerald-400 font-medium">
                                    (Hemat <span x-text="file.savingsPct"></span>%)
                                </span>
                            </template>
                            <span class="text-gray-300 dark:text-gray-600">•</span>
                            <span class="truncate" x-text="file.stageMessage"></span>
                        </div>

                        <!-- Progress Bar during uploading or compressing -->
                        <div
                            class="w-full bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full mt-2 overflow-hidden"
                            x-show="file.stage === 'uploading' || file.stage === 'compressing'"
                        >
                            <div
                                class="h-full rounded-full transition-all duration-300"
                                :class="file.stage === 'uploading' ? 'bg-[#0049FF]' : 'bg-amber-500'"
                                :style="'width: ' + file.progress + '%'"
                            ></div>
                        </div>

                        <!-- Error Message if any -->
                        <p
                            class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-medium"
                            x-show="file.stage === 'error'"
                            x-text="file.error"
                        ></p>
                    </div>

                    <!-- Delete Button -->
                    <button
                        type="button"
                        @click.stop="removeFile(file.id)"
                        class="p-1.5 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors shrink-0"
                        title="Hapus file"
                    >
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

