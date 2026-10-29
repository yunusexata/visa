<div x-data="{
    confirmSubmitAll() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Penyimpanan Dokumen',
                text: 'Apakah seluruh berkas dokumen pendukung Anda telah diunggah dengan lengkap dan benar? Pastikan dokumen dapat terbaca dengan jelas.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0049FF',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Simpan Seluruh Berkas',
                cancelButtonText: 'Periksa Lagi',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-2xl dark:bg-slate-900 dark:text-white',
                    title: 'text-lg font-bold text-slate-900 dark:text-white',
                    htmlContainer: 'text-xs text-slate-600 dark:text-slate-300'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.submitAllDocuments();
                }
            });
        } else {
            $wire.submitAllDocuments();
        }
    }
}" class="min-h-screen bg-slate-50/70 dark:bg-slate-950 text-slate-800 dark:text-slate-100 py-6 sm:py-10 px-3 sm:px-6 font-outfit antialiased selection:bg-brand-blue selection:text-white">

    <!-- Top Floating Navbar / Header Brand -->
    <header class="max-w-4xl mx-auto mb-6 sm:mb-8">
        <div class="flex items-center justify-between bg-white/80 dark:bg-slate-900/80 backdrop-blur-md px-4 sm:px-6 py-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#0049FF] to-[#001B5E] flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                    <span class="material-symbols-outlined text-2xl">description</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-slate-900 dark:text-white text-base tracking-tight">Portal Visa</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-[#0049FF] dark:bg-blue-950/60 dark:text-blue-300 rounded-full border border-blue-200 dark:border-blue-900">
                            {{ $clientName ?? 'Unggah Dokumen' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pengajuan Berkas Dokumen Visa Jepang</p>
                </div>
            </div>

            <div>
                <a href="{{ $link ? route('form.show', ['hashid' => $link]) : route('example.form') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200/60 dark:border-slate-700 transition-all">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    <span>Kembali ke Formulir</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container Card -->
    <main class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden mb-6">
            
            <!-- Stepper Continuity Bar (Desktop) -->
            <div class="hidden sm:block bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm border-b border-slate-100 dark:border-slate-800 p-4">
                <div class="grid grid-cols-2 gap-3">
                    <!-- Tahap 1: Pengisian Formulir (Selesai) -->
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200/70 dark:border-emerald-800/40">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                            <span class="material-symbols-outlined text-base">check</span>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Tahap 1: Pengisian Formulir (5 Langkah)</div>
                            <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium truncate mt-0.5">Selesai Diisi & Terverifikasi</div>
                        </div>
                    </div>

                    <!-- Tahap 2: Unggah Dokumen Berkas (Aktif) -->
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-blue-50/50 dark:bg-blue-950/40 border-2 border-[#0049FF]/30 dark:border-blue-500/30">
                        <div class="w-8 h-8 rounded-xl bg-[#0049FF] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-md shadow-blue-500/20">
                            <span class="material-symbols-outlined text-base">cloud_upload</span>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-xs font-bold text-slate-900 dark:text-white truncate">Tahap 2: Unggah Berkas Persyaratan</div>
                            <div class="text-[10px] text-[#0049FF] dark:text-blue-300 font-medium truncate mt-0.5">6 Slot Dokumen Berkas Pendukung</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stepper Continuity Bar (Mobile) -->
            <div class="sm:hidden p-4 bg-slate-50/60 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between text-xs font-bold mb-2">
                    <span class="text-[#0049FF] dark:text-blue-400 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">cloud_upload</span>
                        Tahap 2: Unggah Berkas Persyaratan
                    </span>
                    <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1 text-[11px]">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        Formulir Selesai
                    </span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-500 to-[#0049FF] h-full rounded-full transition-all duration-300" style="width: 100%;"></div>
                </div>
            </div>

            <!-- Step Header Banner (matching example-form Step banners) -->
            <div class="flex items-center gap-3 p-5 sm:p-8 pb-5 border-b border-slate-100 dark:border-slate-800">
                <div class="p-2.5 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-[#0049FF] border border-blue-100 dark:border-blue-900">
                    <span class="material-symbols-outlined text-2xl">folder_shared</span>
                </div>
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Langkah 2: Unggah Berkas Persyaratan Visa</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pastikan seluruh dokumen terlihat jelas, terbaca sempurna, dan tidak buram (auto-kompres gambar aktif)</p>
                </div>
            </div>

            <!-- Grid Slot Upload Dokumen (2 Kolom Desktop, 1 Kolom Mobile) -->
            <div class="p-5 sm:p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach($slotConfig as $slotKey => $cfg)
                        <div wire:key="slot-container-{{ $slotKey }}">
                            <x-form.upload-zone
                                :type="$slotKey"
                                :label="$cfg['label']"
                                :description="$cfg['description']"
                                :icon="$cfg['icon']"
                                :required="$cfg['required']"
                                :multiple="$cfg['multiple']"
                                :acceptPdf="$cfg['accept_pdf']"
                            />
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Bottom Action Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 sm:p-8 bg-slate-50/70 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400 w-full sm:w-auto">
                    <span class="material-symbols-outlined text-[18px] text-emerald-500">check_circle</span>
                    <span>
                        <strong class="text-slate-900 dark:text-white font-bold" x-text="$wire.uploadedTypes.length">0</strong> dari 6 slot dokumen telah memiliki berkas
                    </span>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ $link ? route('form.show', ['hashid' => $link]) : route('example.form') }}"
                       class="w-full sm:w-auto text-center px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        Kembali ke Formulir
                    </a>

                    <button type="button"
                            @click="confirmSubmitAll()"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs sm:text-sm font-bold shadow-lg shadow-emerald-600/25 active:scale-98 transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="submitAllDocuments" class="material-symbols-outlined text-lg">send</span>
                        <span wire:loading wire:target="submitAllDocuments" class="material-symbols-outlined text-lg animate-spin">sync</span>
                        <span wire:loading.remove wire:target="submitAllDocuments">Simpan Seluruh Dokumen</span>
                        <span wire:loading wire:target="submitAllDocuments">Menyimpan...</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- Footer Notice -->
        <footer class="mt-6 text-center text-xs text-slate-400 dark:text-slate-500">
            &copy; {{ date('Y') }} PT Sumber Rezeki Exata Indonesia. Seluruh hak cipta dilindungi undang-undang.
        </footer>
    </main>
</div>
