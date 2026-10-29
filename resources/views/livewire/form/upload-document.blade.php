<div>
    <!-- Page Header & Stepper -->
    <div class="relative bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 border-b border-gray-200 dark:border-gray-800 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">
            <!-- Breadcrumb / Stepper Minimalis -->
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-4">
                <a href="{{ route('example.form') }}" class="hover:text-[#0049FF] transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    <span>Formulir Data Pribadi</span>
                </a>
                <span>/</span>
                <span class="text-[#001B5E] dark:text-blue-400 font-semibold">Unggah Dokumen Berkas</span>
            </div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                        Unggah Berkas Persyaratan Visa
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300 max-w-2xl">
                        Unggah dokumen pendukung pengajuan visa Anda.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        <!-- Grid Slot Upload Dokumen (2 Kolom Desktop, 1 Kolom Mobile) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-7">
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

        <!-- Sticky / Floating Bottom Action Bar -->
        <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-emerald-500">check_circle</span>
                <span>
                    <strong class="text-gray-800 dark:text-gray-200" x-text="$wire.uploadedTypes.length">0</strong> dari 6 slot dokumen telah memiliki berkas
                </span>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <a
                    href="{{ route('example.form') }}"
                    class="w-full sm:w-auto text-center px-5 py-3 rounded-2xl text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors"
                >
                    Kembali ke Formulir
                </a>

                <button
                    type="button"
                    wire:click="submitAllDocuments"
                    wire:loading.attr="disabled"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-2xl text-sm font-bold text-white bg-[#001B5E] hover:bg-[#0049FF] focus:ring-4 focus:ring-[#0049FF]/20 shadow-md transition-all duration-200 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="submitAllDocuments" class="material-symbols-outlined text-[18px]">send</span>
                    <span wire:loading wire:target="submitAllDocuments" class="material-symbols-outlined text-[18px] animate-spin">sync</span>
                    <span wire:loading.remove wire:target="submitAllDocuments">Simpan Seluruh Dokumen</span>
                    <span wire:loading wire:target="submitAllDocuments">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
</div>

