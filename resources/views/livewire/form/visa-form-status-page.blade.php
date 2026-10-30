@php
    $currentYear = date('Y');
@endphp

<div class="relative flex flex-col items-center justify-between min-h-screen p-4 sm:p-6 overflow-x-hidden z-1 bg-gray-50 dark:bg-gray-900 font-outfit text-slate-800 dark:text-slate-100">
    {{-- common grid shape matching Exata 404 page --}}
    <x-common.common-grid-shape />

    <!-- Top Floating Navbar / Header Brand -->
    <header class="w-full max-w-3xl mx-auto pt-2 pb-4">
        <div class="flex items-center justify-between bg-white/90 dark:bg-slate-800/90 backdrop-blur-md px-4 sm:px-6 py-3 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#0049FF] to-[#001B5E] flex items-center justify-center text-white shadow-xs">
                    <span class="material-symbols-outlined text-xl">description</span>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 dark:text-white text-xs sm:text-sm tracking-tight block">Portal Visa Exata</span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400">PT Sumber Rezeki Exata Indonesia</span>
                </div>
            </div>
            <div>
                <flux:badge color="{{ $meta['badge_color'] }}" size="sm" class="font-bold">
                    {{ $meta['badge_label'] }}
                </flux:badge>
            </div>
        </div>
    </header>

    <!-- Centered Hero Status Section (Matching 404 Style) -->
    <main class="w-full max-w-3xl mx-auto my-auto flex flex-col items-center text-center py-6">

        <!-- Error Illustration (404/503/500 SVGs from public/images/error) -->
        <div class="my-4 max-w-[280px] sm:max-w-[360px] mx-auto">
            <img src="{{ $meta['image_light'] }}" alt="{{ $meta['code'] }}" class="dark:hidden mx-auto max-h-56 sm:max-h-64 object-contain drop-shadow-sm" />
            <img src="{{ $meta['image_dark'] }}" alt="{{ $meta['code'] }}" class="hidden dark:block mx-auto max-h-56 sm:max-h-64 object-contain drop-shadow-sm" />
        </div>

        <!-- Heading & Subtitle -->
        <div class="max-w-xl mx-auto px-4">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white leading-snug">
                {{ $meta['title'] }}
            </h2>
            <p class="mt-3 text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ $meta['subtitle'] }}
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            @if($statusType === 'valid')
                <flux:button variant="primary" icon="arrow-right" href="{{ route('form.show', ['hashid' => $hashid]) }}" class="rounded-xl px-5 py-2.5 font-bold">
                    Buka Formulir Pengajuan
                </flux:button>
            @endif

            <a href="{{ $whatsappUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 text-xs sm:text-sm font-bold shadow-sm transition-all duration-200">
                <span class="material-symbols-outlined text-base">chat</span>
                <span>Hubungi Admin via WhatsApp</span>
            </a>
        </div>

        <!-- Link Metadata Card (jika record link tersedia di DB) -->
        @if($link)
            <div class="w-full max-w-xl mx-auto mt-8 text-left bg-white/70 dark:bg-slate-800/60 backdrop-blur-sm rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-4 sm:p-5 shadow-xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">info</span>
                    <span>Informasi Tautan Formulir</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 block text-[10px] uppercase">PIC Penanggung Jawab</span>
                        <span class="font-bold text-gray-900 dark:text-white truncate block">{{ $link->pic_code ?: ($link->creator?->name ?: '-') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 block text-[10px] uppercase">Batas Penggunaan</span>
                        <span class="font-bold text-gray-900 dark:text-white block">{{ $link->used_count }} / {{ $link->max_usage }} Kuota</span>
                    </div>
                    @if($link->expired_at)
                        <div class="sm:col-span-3 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                            <span class="text-gray-500 dark:text-gray-400 text-[10px] uppercase">Waktu Kedaluwarsa:</span>
                            <span class="font-semibold text-nowrap text-gray-800 dark:text-gray-200 ml-1">{{ $link->expired_at->translatedFormat('d F Y, H:i') }} WIB</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- SECTION: DETAIL SUBMISSION DATA (Tampil jika formulir sudah pernah diajukan dari tautan ini) -->
        @if(!empty($submissions) && count($submissions) > 0)
            <div class="w-full max-w-2xl mx-auto mt-10 text-left">
                <div class="flex items-center justify-between mb-4 px-1">
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-xl">folder_shared</span>
                            Data Rincian Pengajuan Formulir
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Formulir visa berikut telah berhasil terdaftar melalui tautan ini:
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach($submissions as $sub)
                        @php
                            $formHashid = \Vinkla\Hashids\Facades\Hashids::encode($sub->id);
                            $statusEnum = $sub->status instanceof \App\Enums\Form\VisaFormStatus
                                ? $sub->status
                                : \App\Enums\Form\VisaFormStatus::fromLabel((string) $sub->status);
                            
                            $statusBadgeColor = match($statusEnum) {
                                \App\Enums\Form\VisaFormStatus::DRAFT => 'zinc',
                                \App\Enums\Form\VisaFormStatus::DIAJUKAN => 'blue',
                                \App\Enums\Form\VisaFormStatus::SIAP_VERIFIKASI => 'amber',
                                \App\Enums\Form\VisaFormStatus::VERIFIKASI, \App\Enums\Form\VisaFormStatus::SELESAI => 'emerald',
                                \App\Enums\Form\VisaFormStatus::DITOLAK => 'red',
                                default => 'zinc',
                            };
                            $statusLabel = $statusEnum?->label() ?? (string) $sub->status;
                        @endphp

                        <flux:card class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200/90 dark:border-gray-700/80 p-5 shadow-xs transition-all hover:shadow-md">
                            <!-- Header Item: No Registrasi & Status -->
                            <div class="flex flex-wrap items-center justify-between gap-2 pb-3 mb-3 border-b border-gray-100 dark:border-gray-700/60">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[#0049FF] text-lg">badge</span>
                                    <span class="font-mono font-extrabold text-sm text-gray-900 dark:text-white">
                                        {{ $sub->nomor_registrasi }}
                                    </span>
                                </div>
                                <flux:badge color="{{ $statusBadgeColor }}" size="sm" class="font-bold">
                                    {{ $statusLabel }}
                                </flux:badge>
                            </div>

                            <!-- Detail Data Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs mb-4">
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500 block text-[10px] uppercase font-semibold">Nama Lengkap Pemohon</span>
                                    <span class="font-bold text-gray-900 dark:text-white text-sm block">{{ $sub->nama_lengkap }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500 block text-[10px] uppercase font-semibold">Waktu Pengajuan</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300 block">
                                        {{ $sub->tanggal_diajukan ? $sub->tanggal_diajukan->translatedFormat('d M Y, H:i') : $sub->created_at->translatedFormat('d M Y, H:i') }} WIB
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500 block text-[10px] uppercase font-semibold">Nomor Paspor / COE</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300 block">
                                        {{ $sub->nomor_paspor ?: '-' }} / {{ $sub->nomor_coe ?: '-' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500 block text-[10px] uppercase font-semibold">Kaisha (Sponsor Jepang)</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300 truncate block">
                                        {{ $sub->nama_kaisha ?: '-' }}
                                    </span>
                                </div>
                                <div class="sm:col-span-2 pt-1 text-gray-500 dark:text-gray-400 flex items-center gap-1.5 text-[11px]">
                                    <span class="material-symbols-outlined text-base text-blue-500">attach_file</span>
                                    <span>Lampiran Dokumen: <strong>{{ $sub->attachments_count ?? 0 }} berkas</strong> terunggah</span>
                                </div>
                            </div>

                            <!-- Quick Action Buttons -->
                            <div class="pt-3 border-t border-gray-100 dark:border-gray-700/60 flex flex-wrap items-center justify-center gap-2">
                                @if($sub->status === \App\Enums\Form\VisaFormStatus::DRAFT || (is_string($sub->status) && $sub->status === 'DRAFT'))
                                    <a href="{{ route('form.upload-document', ['hashid' => $formHashid]) }}"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold shadow-xs transition-all">
                                        <span class="material-symbols-outlined text-base">cloud_upload</span>
                                        <span>Lanjutkan Unggah Dokumen</span>
                                    </a>
                                @else
                                    <a href="{{ route('form.success', ['hashid' => $formHashid]) }}"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-all">
                                        <span class="material-symbols-outlined text-base">verified</span>
                                        <span>Lihat Tanda Bukti Pengajuan</span>
                                    </a>
                                @endif
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            </div>
        @endif
    </main>

    <!-- Footer Copyright (Matching 404 page) -->
    <footer class="w-full text-center py-4 text-xs text-gray-500 dark:text-gray-400">
        &copy; {{ $currentYear }} PT Sumber Rezeki Exata Indonesia. Seluruh hak dilindungi undang-undang.
    </footer>
</div>

