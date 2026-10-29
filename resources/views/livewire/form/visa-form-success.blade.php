<div class="min-h-screen bg-slate-50/80 dark:bg-slate-950 text-slate-800 dark:text-slate-100 p-4 sm:p-8 font-sans antialiased selection:bg-[#0049FF] selection:text-white">
    <style>
        @keyframes drawCheck {
            0% { stroke-dashoffset: 48; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes pulseRing {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.2); opacity: 0; }
            100% { transform: scale(0.95); opacity: 0; }
        }
        @keyframes floatCard {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
        }
        .animate-check {
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: drawCheck 0.9s cubic-bezier(0.65, 0, 0.45, 1) 0.35s forwards;
        }
        .animate-ring {
            animation: pulseRing 2.6s cubic-bezier(0.24, 0, 0.38, 1) infinite;
        }
        .animate-float {
            animation: floatCard 4s ease-in-out infinite;
        }
    </style>

    <div class="max-w-2xl mx-auto">
        <!-- Floating Brand Header -->
        <header class="flex items-center justify-between bg-white/90 dark:bg-slate-900/90 backdrop-blur-md px-5 py-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#0049FF] to-[#001B5E] flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                    <span class="material-symbols-outlined text-2xl">verified</span>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 dark:text-white text-sm tracking-tight block">Portal Visa Exata</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">PT Sumber Rezeki Exata Indonesia</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $statusBadge['bg'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $statusBadge['dot'] }}"></span>
                    {{ $statusLabel }}
                </span>
            </div>
        </header>

        <!-- Main Success Showcase Card -->
        <main class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-[0_20px_50px_-15px_rgba(0,27,94,0.07)] dark:shadow-none overflow-hidden">
            <!-- Top Decorative Banner & Animated Icon Area -->
            <div class="relative bg-gradient-to-b from-blue-50/70 via-blue-50/30 to-transparent dark:from-slate-800/40 dark:via-transparent pt-10 pb-6 px-6 text-center overflow-hidden">
                <!-- Animated Checkmark Container with Pulse Rings -->
                <div class="relative inline-flex items-center justify-center mb-5">
                    <div class="absolute w-24 h-24 rounded-full bg-emerald-400/20 dark:bg-emerald-500/20 animate-ring"></div>
                    <div class="relative w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-xl shadow-emerald-500/25">
                        <svg class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path class="animate-check" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-2">
                    Pengajuan Formulir Berhasil Dikirim
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-300 max-w-lg mx-auto leading-relaxed">
                    Seluruh berkas dokumen dan formulir aplikasi visa Jepang Anda telah kami terima secara resmi di sistem pusat Exata.
                </p>
            </div>

            <!-- Stepper Progress Tracker Summary -->
            <div class="px-6 sm:px-8 pt-2 pb-4">
                <div class="grid grid-cols-3 gap-2 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-sm font-bold">check</span>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200 truncate">1. Formulir</div>
                            <div class="text-[9px] text-emerald-600 dark:text-emerald-400 font-medium">Selesai</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-sm font-bold">check</span>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200 truncate">2. Dokumen</div>
                            <div class="text-[9px] text-emerald-600 dark:text-emerald-400 font-medium">
                                {{ $totalAttachments > 0 ? "{$totalAttachments} Berkas" : 'Terunggah' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0 animate-pulse">
                            <span class="material-symbols-outlined text-sm">hourglass_top</span>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-[11px] font-bold text-slate-800 dark:text-slate-200 truncate">3. Verifikasi</div>
                            <div class="text-[9px] text-blue-600 dark:text-blue-400 font-medium">{{ $statusLabel }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applicant & Registration Dossier Card -->
            <div class="p-6 sm:p-8 pt-2 space-y-6">
                <div x-data="{ copied: false }" class="bg-slate-50 dark:bg-slate-800/40 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700/80">
                    <div class="flex items-center justify-between pb-3.5 mb-3.5 border-b border-slate-200/60 dark:border-slate-700/60 text-xs">
                        <span class="text-slate-500 dark:text-slate-400 font-medium">Nomor Registrasi Pengajuan</span>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-[#001B5E] dark:text-blue-300 text-sm tracking-wider">
                                {{ $nomorRegistrasi }}
                            </span>
                            <button type="button"
                                    @click="navigator.clipboard.writeText('{{ $nomorRegistrasi }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="inline-flex items-center gap-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 cursor-pointer"
                                    title="Salin Nomor Registrasi">
                                <span class="material-symbols-outlined text-base" x-show="!copied">content_copy</span>
                                <span class="material-symbols-outlined text-base text-emerald-500" x-show="copied" style="display: none;">check</span>
                                <span x-show="copied" class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400" style="display: none;">Tersalin!</span>
                            </button>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <dt class="text-slate-400 dark:text-slate-400 mb-0.5">Nama Pemohon</dt>
                            <dd class="font-bold text-slate-900 dark:text-white text-sm uppercase">
                                {{ $namaLengkap }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 dark:text-slate-400 mb-0.5">Waktu Pengajuan</dt>
                            <dd class="font-medium text-slate-700 dark:text-slate-200">
                                {{ $tanggalDiajukan }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 dark:text-slate-400 mb-0.5">Status Pengajuan</dt>
                            <dd class="inline-flex items-center gap-1.5 font-bold text-blue-600 dark:text-blue-400">
                                <span class="w-2 h-2 rounded-full {{ $statusBadge['dot'] }}"></span>
                                {{ strtoupper($statusLabel) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 dark:text-slate-400 mb-0.5">PIC Pendamping</dt>
                            <dd class="font-mono font-semibold text-slate-700 dark:text-slate-300">
                                {{ $picInfo['display'] }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Collapsible Rincian Dokumen & Kaisha -->
                <div x-data="{ expanded: false }" class="bg-white dark:bg-slate-900/60 rounded-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden">
                    <button type="button"
                            @click="expanded = !expanded"
                            class="w-full flex items-center justify-between p-4 text-left hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-lg text-[#0049FF]">folder_open</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Rincian Data & Lampiran Berkas</span>
                            @if($totalAttachments > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-950 text-[#0049FF] dark:text-blue-300">
                                    {{ $totalAttachments }} Berkas
                                </span>
                            @endif
                        </div>
                        <span class="material-symbols-outlined text-base text-slate-400 transition-transform duration-200"
                              :class="{ 'rotate-180': expanded }">
                            expand_more
                        </span>
                    </button>

                    <div x-show="expanded"
                         x-collapse
                         class="p-4 pt-1 border-t border-slate-100 dark:border-slate-800 space-y-4 text-xs">
                        <!-- Ringkasan Data Paspor & Kaisha -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-slate-50/60 dark:bg-slate-800/30 rounded-xl">
                            <div>
                                <span class="text-slate-400 block text-[10px]">NIK KTP (Indonesia)</span>
                                <span class="font-mono font-medium text-slate-800 dark:text-slate-200">{{ $maskedNik }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px]">Nomor Paspor</span>
                                <span class="font-mono font-medium text-slate-800 dark:text-slate-200">
                                    {{ $maskedPaspor }} <span class="text-[10px] text-slate-400">(Exp: {{ $tanggalPasporHabis }})</span>
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px]">Nomor COE & Kependudukan</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ $visaForm->nomor_coe ?: '-' }} • {{ $visaForm->status_kependudukan_jepang ?: '-' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px]">Kaisha / Penempatan di Jepang</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ $visaForm->nama_kaisha ?: '-' }} (Tujuan: {{ $visaForm->bandara_tujuan ?: '-' }})
                                </span>
                            </div>
                        </div>

                        <!-- Daftar Berkas Terlampir -->
                        @if(count($attachmentsSummary) > 0)
                            <div>
                                <div class="text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-2">Berkas Dokumen Terunggah:</div>
                                <div class="space-y-1.5">
                                    @foreach($attachmentsSummary as $att)
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-700/60">
                                            <div class="flex items-center gap-2 overflow-hidden">
                                                <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-950 text-[#0049FF] dark:text-blue-300 flex items-center justify-center shrink-0">
                                                    <span class="material-symbols-outlined text-sm">description</span>
                                                </span>
                                                <div class="overflow-hidden">
                                                    <div class="font-bold text-slate-800 dark:text-slate-200 truncate text-[11px]">{{ $att['label'] }}</div>
                                                    <div class="text-[10px] text-slate-400 truncate">{{ $att['nama_asli'] }}</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <span class="text-[10px] font-mono text-slate-400">{{ $att['ukuran'] }}</span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                    {{ $att['status'] }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Official Notice Box -->
                <div class="flex items-start gap-3 p-4 rounded-2xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50 text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
                    <span class="material-symbols-outlined text-xl text-[#0049FF] shrink-0 mt-0.5">info</span>
                    <div>
                        <strong class="text-slate-900 dark:text-white block mb-0.5">Langkah Verifikasi Selanjutnya:</strong>
                        Tim verifikator dokumen Exata sedang meninjau kelengkapan berkas Anda. Pemberitahuan status dan klarifikasi dokumen akan dikabarkan melalui nomor WhatsApp <strong class="text-slate-900 dark:text-white">{{ $visaForm->telepon_indonesia ?: '-' }}</strong> atau email resmi <strong class="text-slate-900 dark:text-white">{{ $visaForm->email_aktif ?: '-' }}</strong>.
                    </div>
                </div>

                <!-- Action Buttons (Hidden on Print) -->
                <div class="no-print pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ $whatsappUrl }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-lg shadow-emerald-600/20 transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-lg">chat</span>
                        Hubungi WhatsApp PIC / Admin
                    </a>
                </div>
            </div>

            <!-- Footer Notice -->
            <footer class="bg-slate-50/60 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 py-3.5 text-center text-xs text-slate-400 dark:text-slate-500">
                &copy; {{ date('Y') }} PT Sumber Rezeki Exata Indonesia. Seluruh hak cipta dilindungi undang-undang.
            </footer>
        </main>
    </div>
</div>
