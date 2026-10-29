<div x-data="{
    step: 1,
    totalSteps: 5,
    stepsMeta: [
        { id: 1, title: 'Data Diri', subtitle: 'Identitas & KTP', icon: 'badge' },
        { id: 2, title: 'Data Paspor', subtitle: 'Masa Berlaku & Kantor', icon: 'travel_explore' },
        { id: 3, title: 'Dokumen COE', subtitle: 'Eligibility & Izin Stay', icon: 'verified' },
        { id: 4, title: 'Tujuan Jepang', subtitle: 'Kaisha & Sponsor', icon: 'flight_takeoff' },
        { id: 5, title: 'Konfirmasi', subtitle: 'Review & Kirim', icon: 'task_alt' }
    ],
    draftRestored: false,

    init() {
        this.loadDraft();

        // Listen for backend confirmation of step advance
        window.addEventListener('step-advanced', (e) => {
            if (e.detail && e.detail.step) {
                this.step = e.detail.step;
            } else {
                this.step++;
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
            this.saveDraft();
        });

        // Listen for backend confirmation to clear draft after successful submit
        window.addEventListener('clear-form-draft', () => {
            localStorage.removeItem('visa_form_draft_{{ $hashid }}');
            this.draftRestored = false;
        });
    },

    loadDraft() {
        try {
            const raw = localStorage.getItem('visa_form_draft_{{ $hashid }}');
            if (!raw) return;
            const data = JSON.parse(raw);
            if (data && typeof data === 'object') {
                Object.keys(data).forEach(key => {
                    if (key !== '_step' && data[key] !== null && data[key] !== undefined && key in $wire) {
                        $wire.set(key, data[key]);
                    }
                });
                if (data._step && data._step >= 1 && data._step <= this.totalSteps) {
                    this.step = data._step;
                }
                this.draftRestored = true;
                setTimeout(() => this.draftRestored = false, 4000);
            }
        } catch (e) {
            console.error('Failed to load draft from localStorage:', e);
        }
    },

    saveDraft() {
        try {
            const payload = {
                _step: this.step,
                nama_lengkap: $wire.nama_lengkap || '',
                tempat_lahir: $wire.tempat_lahir || '',
                tanggal_lahir: $wire.tanggal_lahir || '',
                nik_ktp: $wire.nik_ktp || '',
                alamat_indonesia: $wire.alamat_indonesia || '',
                kode_pos_indonesia: $wire.kode_pos_indonesia || '',
                telepon_indonesia: $wire.telepon_indonesia || '',
                email_aktif: $wire.email_aktif || '',
                nomor_paspor: $wire.nomor_paspor || '',
                kantor_paspor: $wire.kantor_paspor || '',
                tanggal_pengeluaran_paspor: $wire.tanggal_pengeluaran_paspor || '',
                tanggal_habis_berlaku_paspor: $wire.tanggal_habis_berlaku_paspor || '',
                nomor_coe: $wire.nomor_coe || '',
                status_kependudukan_jepang: $wire.status_kependudukan_jepang || '',
                lama_stay_jepang: $wire.lama_stay_jepang || '',
                tanggal_kedatangan: $wire.tanggal_kedatangan || '',
                bandara_tujuan: $wire.bandara_tujuan || '',
                maskapai: $wire.maskapai || '',
                nama_kaisha: $wire.nama_kaisha || '',
                alamat_jepang: $wire.alamat_jepang || '',
                kode_pos_jepang: $wire.kode_pos_jepang || '',
                telepon_jepang: $wire.telepon_jepang || '',
                pernah_kerja_jepang: $wire.pernah_kerja_jepang || 'tidak',
                periode_kerja_mulai: $wire.periode_kerja_mulai || '',
                periode_kerja_selesai: $wire.periode_kerja_selesai || '',
                nama_penanggung_jawab: $wire.nama_penanggung_jawab || '',
                alamat_penanggung_jawab: $wire.alamat_penanggung_jawab || '',
                telepon_penanggung_jawab: $wire.telepon_penanggung_jawab || ''
            };
            localStorage.setItem('visa_form_draft_{{ $hashid }}', JSON.stringify(payload));
        } catch (e) {
            console.error('Failed to save draft to localStorage:', e);
        }
    },

    getStepPayload(currentStep) {
        if (currentStep === 1) {
            return {
                nama_lengkap: $wire.nama_lengkap,
                tempat_lahir: $wire.tempat_lahir,
                tanggal_lahir: $wire.tanggal_lahir,
                nik_ktp: $wire.nik_ktp,
                alamat_indonesia: $wire.alamat_indonesia,
                kode_pos_indonesia: $wire.kode_pos_indonesia,
                telepon_indonesia: $wire.telepon_indonesia,
                email_aktif: $wire.email_aktif
            };
        } else if (currentStep === 2) {
            return {
                nomor_paspor: $wire.nomor_paspor,
                kantor_paspor: $wire.kantor_paspor,
                tanggal_pengeluaran_paspor: $wire.tanggal_pengeluaran_paspor,
                tanggal_habis_berlaku_paspor: $wire.tanggal_habis_berlaku_paspor
            };
        } else if (currentStep === 3) {
            return {
                nomor_coe: $wire.nomor_coe,
                status_kependudukan_jepang: $wire.status_kependudukan_jepang,
                lama_stay_jepang: $wire.lama_stay_jepang
            };
        } else if (currentStep === 4) {
            return {
                tanggal_kedatangan: $wire.tanggal_kedatangan,
                bandara_tujuan: $wire.bandara_tujuan,
                maskapai: $wire.maskapai,
                nama_kaisha: $wire.nama_kaisha,
                alamat_jepang: $wire.alamat_jepang,
                kode_pos_jepang: $wire.kode_pos_jepang,
                telepon_jepang: $wire.telepon_jepang,
                pernah_kerja_jepang: $wire.pernah_kerja_jepang,
                periode_kerja_mulai: $wire.periode_kerja_mulai,
                periode_kerja_selesai: $wire.periode_kerja_selesai,
                nama_penanggung_jawab: $wire.nama_penanggung_jawab,
                alamat_penanggung_jawab: $wire.alamat_penanggung_jawab,
                telepon_penanggung_jawab: $wire.telepon_penanggung_jawab
            };
        }
        return {};
    },

    advanceStep() {
        if (this.step < this.totalSteps) {
            $wire.nextStep(this.step, this.getStepPayload(this.step));
        }
    },

    prevStep() {
        if (this.step > 1) {
            this.step--;
            window.scrollTo({ top: 0, behavior: 'smooth' });
            this.saveDraft();
        }
    },

    goToStep(target) {
        if (target >= 1 && target <= this.totalSteps) {
            this.step = target;
            window.scrollTo({ top: 0, behavior: 'smooth' });
            this.saveDraft();
        }
    },

    confirmSubmit() {

        Swal.fire({
            icon: 'question',
            title: 'Konfirmasi Pengiriman Formulir',
            text: 'Apakah seluruh data yang Anda isi sudah sesuai dan benar? Setelah dikirim, data tidak dapat diubah dan Anda akan melanjutkan ke tahap unggah dokumen berkas.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim Formulir',
            cancelButtonText: 'Periksa Lagi',
            confirmButtonColor: '#001B5E',
            cancelButtonColor: '#6B7280',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.submit();
            }
        });
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
                            {{ $applicantName ?: 'Pengajuan Visa' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pengajuan Berkas Dokumen Visa Jepang</p>
                </div>
            </div>
        </div>

        <!-- Draft Restored Toast Notification -->
        <div x-show="draftRestored" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             style="display: none;"
             class="mt-3 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 px-4 py-2.5 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                <span class="material-symbols-outlined text-base text-emerald-600">history</span>
                Draf formulir Anda berhasil dipulihkan dari penyimpanan lokal.
            </div>
            <button type="button" @click="draftRestored = false" class="text-emerald-500 hover:text-emerald-700">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    </header>

    <!-- Main Container Card -->
    <main class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-[0_10px_35px_-5px_rgba(0,27,94,0.07)] overflow-hidden">
            
            <!-- Stepper Progress Header -->
            <div class="border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50 p-4 sm:p-6">
                <!-- Mobile Stepper Info -->
                <div class="sm:hidden mb-3">
                    <div class="flex items-center justify-between text-xs font-semibold mb-2">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1">
                            <span class="material-symbols-outlined text-base text-[#0049FF]" x-text="stepsMeta[step - 1].icon"></span>
                            <span x-text="stepsMeta[step - 1].title"></span>
                        </span>
                        <span class="text-[#0049FF] font-bold" x-text="`Langkah ${step} dari ${totalSteps}`"></span>
                    </div>
                    <!-- Animated Progress Bar -->
                    <div class="h-2 w-full bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#0049FF] to-[#465fff] transition-all duration-300 ease-out rounded-full"
                             :style="`width: ${(step / totalSteps) * 100}%`"></div>
                    </div>
                </div>

                <!-- Desktop Stepper Pill Tabs -->
                <div class="hidden sm:grid grid-cols-5 gap-2 relative">
                    <template x-for="(s, index) in stepsMeta" :key="s.id">
                        <button type="button" 
                                @click="goToStep(s.id)"
                                class="flex items-center gap-3 p-2.5 rounded-2xl text-left transition-all duration-200 relative group cursor-pointer"
                                :class="step === s.id 
                                    ? 'bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700' 
                                    : (step > s.id ? 'hover:bg-slate-100/60 dark:hover:bg-slate-800/40 opacity-90' : 'hover:bg-slate-100/40 dark:hover:bg-slate-800/20 opacity-50')">
                            
                            <!-- Step Number / Icon Badge -->
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold shrink-0 transition-colors"
                                 :class="step === s.id 
                                    ? 'bg-[#0049FF] text-white shadow-sm shadow-blue-500/30 ring-2 ring-blue-500/20' 
                                    : (step > s.id ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400')">
                                <template x-if="step > s.id">
                                    <span class="material-symbols-outlined text-base">check</span>
                                </template>
                                <template x-if="step <= s.id">
                                    <span x-text="s.id"></span>
                                </template>
                            </div>

                            <div class="overflow-hidden">
                                <div class="text-xs font-bold truncate leading-tight"
                                     :class="step === s.id ? 'text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-400'" 
                                     x-text="s.title"></div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 truncate mt-0.5" 
                                     x-text="s.subtitle"></div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Form Content Area (Autosave triggers on change & blur only) -->
            <form @submit.prevent="confirmSubmit()" 
                  @change="saveDraft()" 
                  @blur.capture="saveDraft()"
                  class="p-5 sm:p-8">

                <!-- STEP 1: WAJIB ISI DATA DI BAWAH INI (Identitas & Kontak) -->
                <div x-show="step === 1" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-6">

                    <!-- Step Header Banner -->
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="p-2.5 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-[#0049FF] border border-blue-100 dark:border-blue-900">
                            <span class="material-symbols-outlined text-2xl">badge</span>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Langkah 1: Identitas Diri Pemohon</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Pastikan data di bawah ini sesuai dengan KTP dan Akta Kelahiran resmi</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <!-- 1. Nama Lengkap -->
                        <div class="md:col-span-2">
                            <label for="nama_lengkap" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">person</span>
                                <input type="text" 
                                       id="nama_lengkap" 
                                       wire:model="nama_lengkap" 
                                       placeholder="Sesuai KTP / Paspor (contoh: BUDI SANTOSO)"
                                       required
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none uppercase placeholder:normal-case placeholder:text-slate-400">
                            </div>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Gunakan huruf balok / kapital tanpa singkatan gelar</p>
                        </div>

                        <!-- 2a. Tempat Lahir (Reusable Select Regency Component with Select2-style Alpine Search) -->
                        <div>
                            <label for="tempat_lahir" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Tempat Lahir <span class="text-rose-500">*</span>
                            </label>
                            <x-form.select-regency wire:model="tempat_lahir" id="tempat_lahir" placeholder="Cari kabupaten/kota kelahiran..." />
                        </div>

                        <!-- 2b. Tanggal Lahir -->
                        <div>
                            <label for="tanggal_lahir" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Tanggal Lahir <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">calendar_month</span>
                                <input type="date" 
                                       id="tanggal_lahir" 
                                       wire:model="tanggal_lahir" 
                                       required
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <!-- 3. NIK KTP Indonesia -->
                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="nik_ktp" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                    NIK KTP Indonesia (16 Digit) <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[11px] font-mono px-2 py-0.5 rounded-md font-semibold"
                                      :class="$wire.nik_ktp && $wire.nik_ktp.length === 16 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'"
                                      x-text="`${($wire.nik_ktp ? $wire.nik_ktp.length : 0)} / 16 Digit`"></span>
                            </div>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">fingerprint</span>
                                <input type="text" 
                                       id="nik_ktp" 
                                       wire:model.live="nik_ktp" 
                                       inputmode="numeric"
                                       maxlength="16"
                                       x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '')"
                                       placeholder="16 digit angka KTP (contoh: 3578012345670001)"
                                       required
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono tracking-widest focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <!-- 17. Alamat Lengkap Indonesia & Kode Pos -->
                        <div class="md:col-span-2">
                            <label for="alamat_indonesia" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Alamat Lengkap di Indonesia <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative mb-2">
                                <textarea id="alamat_indonesia" 
                                          wire:model="alamat_indonesia" 
                                          rows="2"
                                          placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten, Provinsi"
                                          required
                                          class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none resize-none"></textarea>
                            </div>
                            <label for="alamat_indonesia" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Kode Pos <span class="text-rose-500">*</span>
                            </label>
                            <div class="w-full sm:w-1/2">
                                <input type="text" 
                                       id="kode_pos_indonesia" 
                                       wire:model="kode_pos_indonesia" 
                                       inputmode="numeric"
                                       maxlength="5"
                                       placeholder="Kode Pos Indonesia (5 Digit, cth: 60123)"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <!-- 18. Nomor Telepon Indonesia -->
                        <div>
                            <label for="telepon_indonesia" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nomor Telepon / WhatsApp Indonesia <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex rounded-xl shadow-xs border border-slate-200 dark:border-slate-700 overflow-hidden focus-within:border-[#0049FF] focus-within:ring-4 focus-within:ring-blue-500/10 transition-all">
                                <span class="inline-flex items-center gap-1 px-3.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs border-r border-slate-200 dark:border-slate-700">
                                    🇮🇩 +62
                                </span>
                                <input type="tel" 
                                       id="telepon_indonesia" 
                                       wire:model="telepon_indonesia" 
                                       inputmode="numeric"
                                       x-on:input="$el.value = $el.value.replace(/^[0]+/, '').replace(/[^0-9]/g, '')"
                                       placeholder="81234567890 (tanpa awalan 0)"
                                       required
                                       class="flex-1 px-4 py-3 bg-white dark:bg-slate-800 text-sm font-medium outline-none">
                            </div>
                        </div>

                        <!-- 19. Email Aktif -->
                        <div>
                            <label for="email_aktif" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Email Aktif <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">mail</span>
                                <input type="email" 
                                       id="email_aktif" 
                                       wire:model="email_aktif" 
                                       placeholder="alamat.email@gmail.com"
                                       required
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- STEP 2: DATA DI PASPOR -->
                <div x-show="step === 2" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-6">

                    <!-- Step Header Banner -->
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="p-2.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900">
                            <span class="material-symbols-outlined text-2xl">travel_explore</span>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Langkah 2: Data di Paspor</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Pastikan paspor masih berlaku minimal 6 bulan sebelum jadwal keberangkatan</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <!-- 4. Nomor Paspor -->
                        <div>
                            <label for="nomor_paspor" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Nomor Paspor <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">book</span>
                                <input type="text" 
                                       id="nomor_paspor" 
                                       wire:model="nomor_paspor" 
                                       x-on:input="$el.value = $el.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                                       placeholder="Contoh: C1234567 / B1234567"
                                       required
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono tracking-widest uppercase focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <!-- 5. Kantor Yang Mengeluarkan Paspor -->
                        <div>
                            <label for="kantor_paspor" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Kantor Pengeluar Paspor <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">account_balance</span>
                                <input type="text" 
                                       id="kantor_paspor" 
                                       wire:model="kantor_paspor" 
                                       list="daftar_kantor_imigrasi"
                                       placeholder="Contoh: KANIM SURABAYA / JAKARTA SELATAN"
                                       required
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm uppercase focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                                <datalist id="daftar_kantor_imigrasi">
                                    <option value="KANIM KELAS I KHUSUS TPI SOEKARNO HATTA">
                                    <option value="KANIM KELAS I KHUSUS TPI SURABAYA">
                                    <option value="KANIM KELAS I KHUSUS TPI JAKARTA SELATAN">
                                    <option value="KANIM KELAS I KHUSUS TPI MEDAN">
                                    <option value="KANIM KELAS I KHUSUS TPI NGURAH RAI">
                                    <option value="KANIM KELAS I TPI BANDUNG">
                                    <option value="KANIM KELAS I TPI SEMARANG">
                                    <option value="KANIM KELAS I TPI YOGYAKARTA">
                                </datalist>
                            </div>
                        </div>

                        <!-- 6. Tanggal Pengeluaran Paspor -->
                        <div>
                            <label for="tanggal_pengeluaran_paspor" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Tanggal Pengeluaran Paspor <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">event_available</span>
                                <input type="date" 
                                       id="tanggal_pengeluaran_paspor" 
                                       wire:model="tanggal_pengeluaran_paspor" 
                                       required
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <!-- 7. Tanggal Habis Berlaku Paspor -->
                        <div>
                            <label for="tanggal_habis_berlaku_paspor" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Tanggal Habis Berlaku Paspor <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">event_busy</span>
                                <input type="date" 
                                       id="tanggal_habis_berlaku_paspor" 
                                       wire:model="tanggal_habis_berlaku_paspor" 
                                       required
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                            <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">info</span> Masa berlaku minimal disarankan 6-12 bulan ke depan
                            </p>
                        </div>

                    </div>
                </div>

                <!-- STEP 3: ISI DATA BERDASARKAN COE (CERTIFICATE OF ELIGIBILITY) -->
                <div x-show="step === 3" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-6">

                    <!-- Step Header Banner -->
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="p-2.5 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900">
                            <span class="material-symbols-outlined text-2xl">verified</span>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Langkah 3: Data Certificate of Eligibility (COE)</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Salin informasi sesuai yang tercantum pada lembar dokumen COE Jepang Anda</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <!-- 8. Nomor COE -->
                        <div class="md:col-span-2">
                            <label for="nomor_coe" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nomor COE (Certificate of Eligibility) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">confirmation_number</span>
                                <input type="text" 
                                       id="nomor_coe" 
                                       wire:model="nomor_coe" 
                                       x-on:input="$el.value = $el.value.toUpperCase()"
                                       placeholder="Nomor COE yang tertera pada pojok kanan atas lembar COE"
                                       required
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono tracking-wider focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <!-- 9. Status Kependudukan Di Jepang -->
                        <div>
                            <label for="status_kependudukan_jepang" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Status Kependudukan di Jepang (Visa Type) <span class="text-rose-500">*</span>
                            </label>
                            <select id="status_kependudukan_jepang" 
                                    wire:model="status_kependudukan_jepang" 
                                    required
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                                <option value="Specified Skilled Worker (SSW / Tokutei Ginou)">Specified Skilled Worker (SSW / Tokutei Ginou)</option>
                                <option value="Technical Intern Training (TITP / Gino Jisshusei)">Technical Intern Training (Gino Jisshusei / Magang)</option>
                                <option value="Engineer / Specialist in Humanities / Int Services (Gijinkoku)">Engineer / Specialist in Humanities (Gijinkoku)</option>
                                <option value="Student (Ryugaku)">Student (Ryugaku / Pelajar)</option>
                                <option value="Dependent (Kazoku Taizai)">Dependent (Kazoku Taizai / Keluarga)</option>
                                <option value="Business Manager">Business Manager</option>
                                <option value="Lainnya">Status Lainnya</option>
                            </select>
                        </div>

                        <!-- 10. Berapa Lama Stay Di Jepang -->
                        <div>
                            <label for="lama_stay_jepang" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Masa Tinggal di Jepang (Period of Stay) <span class="text-rose-500">*</span>
                            </label>
                            <select id="lama_stay_jepang" 
                                    wire:model="lama_stay_jepang" 
                                    required
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                                <option value="1 Tahun">1 Tahun (1 Year)</option>
                                <option value="3 Tahun">3 Tahun (3 Years)</option>
                                <option value="5 Tahun">5 Tahun (5 Years)</option>
                                <option value="6 Bulan">6 Bulan (6 Months)</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- STEP 4: DATA LAINNYA (Kedatangan, Kaisha, Sponsor di Jepang) -->
                <div x-show="step === 4" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-6">

                    <!-- Step Header Banner -->
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="p-2.5 rounded-2xl bg-orange-50 dark:bg-orange-950/60 text-[#FF4800] border border-orange-100 dark:border-orange-900">
                            <span class="material-symbols-outlined text-2xl">flight_takeoff</span>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Langkah 4: Rencana Kedatangan & Tempat Kerja di Jepang</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Informasi penerbangan, data Kaisha (Perusahaan), dan penanggung jawab di Jepang</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <!-- 11. Tanggal Kedatangan di Jepang -->
                        <div>
                            <label for="tanggal_kedatangan" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Tanggal Kedatangan di Jepang <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">flight_land</span>
                                <input type="date" 
                                       id="tanggal_kedatangan" 
                                       wire:model="tanggal_kedatangan" 
                                       required
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <!-- 13. Maskapai -->
                        <div>
                            <label for="maskapai" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Maskapai Penerbangan <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">airlines</span>
                                <input type="text" 
                                       id="maskapai" 
                                       wire:model="maskapai" 
                                       placeholder="Contoh: Garuda Indonesia / ANA / JAL / Scoot"
                                       required
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <!-- 12. Masuk Bandara Jepang -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                Bandara Kedatangan di Jepang <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                                @foreach([
                                    'Haneda (HND)' => 'Haneda',
                                    'Narita (NRT)' => 'Narita',
                                    'Fukuoka (FUK)' => 'Fukuoka',
                                    'Osaka / Kansai (KIX)' => 'Osaka',
                                    'Lainnya' => 'Lainnya'
                                ] as $val => $label)
                                    <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition-all text-center"
                                           :class="$wire.bandara_tujuan === '{{ $val }}' 
                                                ? 'bg-blue-50/80 dark:bg-blue-950/60 border-[#0049FF] text-[#0049FF] font-bold shadow-xs' 
                                                : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300'">
                                        <input type="radio" 
                                               wire:model.live="bandara_tujuan" 
                                               value="{{ $val }}" 
                                               class="sr-only">
                                        <span class="text-xs">{{ $label }}</span>
                                        <span class="text-[10px] opacity-75 font-mono">{{ Str::between($val, '(', ')') ?: '' }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- 14. Nama Kaisha di Jepang -->
                        <div class="md:col-span-2">
                            <label for="nama_kaisha" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nama Kaisha (Perusahaan / Lembaga Penerima) di Jepang <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">corporate_fare</span>
                                <input type="text" 
                                       id="nama_kaisha" 
                                       wire:model="nama_kaisha" 
                                       placeholder="Contoh: KABUSHIKI KAISHA EXATA JAPAN"
                                       required
                                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium uppercase focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <!-- 15. Alamat Lengkap Apato / Kaisha di Jepang + Kode Pos -->
                        <div class="md:col-span-2">
                            <label for="alamat_jepang" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Alamat Lengkap Apato / Kaisha di Jepang + Kode Pos <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative mb-2">
                                <textarea id="alamat_jepang" 
                                          wire:model="alamat_jepang" 
                                          rows="2"
                                          placeholder="Prefecture, City, Ward, Street address, Building/Apartment name & room number"
                                          required
                                          class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none resize-none"></textarea>
                            </div>
                            <div class="w-full sm:w-1/2">
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-mono text-sm">〒</span>
                                    <input type="text" 
                                           id="kode_pos_jepang" 
                                           wire:model="kode_pos_jepang" 
                                           placeholder="Kode Pos Jepang (cth: 100-0001)"
                                           class="w-full pl-8 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                                </div>
                            </div>
                        </div>

                        <!-- 16. Nomor Telepon Jepang -->
                        <div>
                            <label for="telepon_jepang" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nomor Telepon Kaisha / Kontak di Jepang <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex rounded-xl shadow-xs border border-slate-200 dark:border-slate-700 overflow-hidden focus-within:border-[#0049FF] focus-within:ring-4 focus-within:ring-blue-500/10 transition-all">
                                <span class="inline-flex items-center gap-1 px-3.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs border-r border-slate-200 dark:border-slate-700">
                                    🇯🇵 +81
                                </span>
                                <input type="tel" 
                                       id="telepon_jepang" 
                                       wire:model="telepon_jepang" 
                                       inputmode="numeric"
                                       x-on:input="$el.value = $el.value.replace(/^[0]+/, '').replace(/[^0-9]/g, '')"
                                       placeholder="8012345678 (tanpa awalan 0)"
                                       required
                                       class="flex-1 px-4 py-3 bg-white dark:bg-slate-800 text-sm font-medium outline-none">
                            </div>
                        </div>

                        <!-- 20. Sebelumnya Pernah Kerja di Jepang -->
                        <div class="md:col-span-2 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700/80 space-y-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Apakah Anda Sebelumnya Pernah Bekerja / Tinggal di Jepang? <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    <input type="radio" 
                                           wire:model.live="pernah_kerja_jepang" 
                                           value="tidak" 
                                           class="text-[#0049FF] focus:ring-blue-500">
                                    Tidak Pernah (Pertama Kali)
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    <input type="radio" 
                                           wire:model.live="pernah_kerja_jepang" 
                                           value="pernah" 
                                           class="text-[#0049FF] focus:ring-blue-500">
                                    Ya, Pernah Bekerja Sebelumnya
                                </label>
                            </div>

                            <!-- Expandable Periode Kerja jika pernah -->
                            <div x-show="$wire.pernah_kerja_jepang === 'pernah'"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="pt-3 border-t border-slate-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Dari Bulan / Tahun:</span>
                                    <input type="month" 
                                           wire:model="periode_kerja_mulai" 
                                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:border-[#0049FF] outline-none">
                                </div>
                                <div>
                                    <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Sampai Bulan / Tahun:</span>
                                    <input type="month" 
                                           wire:model="periode_kerja_selesai" 
                                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs focus:border-[#0049FF] outline-none">
                                </div>
                            </div>
                        </div>

                        <!-- 21. Nama Penanggung Jawab di Jepang -->
                        <div class="md:col-span-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">shield_person</span>
                                Data Penanggung Jawab (Guarantor / Supervisor) di Jepang
                            </h3>
                        </div>

                        <div>
                            <label for="nama_penanggung_jawab" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nama Penanggung Jawab di Jepang <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nama_penanggung_jawab" 
                                   wire:model="nama_penanggung_jawab" 
                                   placeholder="Nama Orang / PIC / Representative di Jepang"
                                   required
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                        </div>

                        <!-- 23. Nomor Telepon Penanggung Jawab -->
                        <div>
                            <label for="telepon_penanggung_jawab" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nomor Telepon Penanggung Jawab <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex rounded-xl shadow-xs border border-slate-200 dark:border-slate-700 overflow-hidden focus-within:border-[#0049FF] focus-within:ring-4 focus-within:ring-blue-500/10 transition-all">
                                <span class="inline-flex items-center gap-1 px-3.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs border-r border-slate-200 dark:border-slate-700">
                                    🇯🇵 +81
                                </span>
                                <input type="tel" 
                                       id="telepon_penanggung_jawab" 
                                       wire:model="telepon_penanggung_jawab" 
                                       inputmode="numeric"
                                       x-on:input="$el.value = $el.value.replace(/^[0]+/, '').replace(/[^0-9]/g, '')"
                                       placeholder="Nomor Telepon Penanggung Jawab"
                                       required
                                       class="flex-1 px-4 py-3 bg-white dark:bg-slate-800 text-sm font-medium outline-none">
                            </div>
                        </div>

                        <!-- 22. Alamat Penanggung Jawab di Jepang -->
                        <div class="md:col-span-2">
                            <label for="alamat_penanggung_jawab" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Alamat Penanggung Jawab di Jepang <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="alamat_penanggung_jawab" 
                                   wire:model="alamat_penanggung_jawab" 
                                   placeholder="Alamat kantor / tempat tinggal penanggung jawab di Jepang"
                                   required
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                        </div>

                    </div>
                </div>

                <!-- STEP 5: REVIEW DATA & KONFIRMASI AKHIR -->
                <div x-show="step === 5" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-6">

                    <!-- Step Header Banner -->
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="p-2.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900">
                            <span class="material-symbols-outlined text-2xl">task_alt</span>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">Langkah 5: Ringkasan & Konfirmasi Pengajuan</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Silakan tinjau seluruh data Anda. Klik tombol "Ubah" jika ada perbaikan sebelum dikirimkan.</p>
                        </div>
                    </div>

                    <!-- Summary Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Card 1: Data Identitas -->
                        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 relative group">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-extrabold uppercase text-[#0049FF] flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">badge</span> Identitas Diri
                                </span>
                                <button type="button" @click="goToStep(1)" class="text-xs font-bold text-slate-500 hover:text-[#0049FF] flex items-center gap-0.5">
                                    <span class="material-symbols-outlined text-sm">edit</span> Ubah
                                </button>
                            </div>
                            <dl class="space-y-1.5 text-xs">
                                <div><dt class="text-slate-400">Nama Lengkap:</dt><dd class="font-bold text-slate-800 dark:text-white" x-text="$wire.nama_lengkap || '-'"></dd></div>
                                <div><dt class="text-slate-400">Tempat, Tanggal Lahir:</dt><dd class="font-medium" x-text="`${$wire.tempat_lahir || '-'}, ${$wire.tanggal_lahir || '-'}`"></dd></div>
                                <div><dt class="text-slate-400">NIK KTP:</dt><dd class="font-mono font-medium" x-text="$wire.nik_ktp || '-'"></dd></div>
                                <div><dt class="text-slate-400">Telepon & Email:</dt><dd class="font-medium" x-text="`+62 ${$wire.telepon_indonesia || '-'} • ${$wire.email_aktif || '-'}`"></dd></div>
                            </dl>
                        </div>

                        <!-- Card 2: Paspor -->
                        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 relative group">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-extrabold uppercase text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">travel_explore</span> Paspor
                                </span>
                                <button type="button" @click="goToStep(2)" class="text-xs font-bold text-slate-500 hover:text-indigo-600 flex items-center gap-0.5">
                                    <span class="material-symbols-outlined text-sm">edit</span> Ubah
                                </button>
                            </div>
                            <dl class="space-y-1.5 text-xs">
                                <div><dt class="text-slate-400">Nomor Paspor:</dt><dd class="font-mono font-bold text-slate-800 dark:text-white" x-text="$wire.nomor_paspor || '-'"></dd></div>
                                <div><dt class="text-slate-400">Kantor Penerbit:</dt><dd class="font-medium" x-text="$wire.kantor_paspor || '-'"></dd></div>
                                <div><dt class="text-slate-400">Tgl Dikeluarkan:</dt><dd class="font-medium" x-text="$wire.tanggal_pengeluaran_paspor || '-'"></dd></div>
                                <div><dt class="text-slate-400">Habis Berlaku:</dt><dd class="font-medium" x-text="$wire.tanggal_habis_berlaku_paspor || '-'"></dd></div>
                            </dl>
                        </div>

                        <!-- Card 3: COE -->
                        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 relative group">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-extrabold uppercase text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">verified</span> Dokumen COE
                                </span>
                                <button type="button" @click="goToStep(3)" class="text-xs font-bold text-slate-500 hover:text-amber-600 flex items-center gap-0.5">
                                    <span class="material-symbols-outlined text-sm">edit</span> Ubah
                                </button>
                            </div>
                            <dl class="space-y-1.5 text-xs">
                                <div><dt class="text-slate-400">Nomor COE:</dt><dd class="font-mono font-bold text-slate-800 dark:text-white" x-text="$wire.nomor_coe || '-'"></dd></div>
                                <div><dt class="text-slate-400">Status Kependudukan:</dt><dd class="font-medium" x-text="$wire.status_kependudukan_jepang || '-'"></dd></div>
                                <div><dt class="text-slate-400">Masa Tinggal:</dt><dd class="font-medium" x-text="$wire.lama_stay_jepang || '-'"></dd></div>
                            </dl>
                        </div>

                        <!-- Card 4: Kaisha & Sponsor -->
                        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 relative group">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-extrabold uppercase text-orange-600 dark:text-orange-400 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">flight_takeoff</span> Rencana & Kaisha
                                </span>
                                <button type="button" @click="goToStep(4)" class="text-xs font-bold text-slate-500 hover:text-orange-600 flex items-center gap-0.5">
                                    <span class="material-symbols-outlined text-sm">edit</span> Ubah
                                </button>
                            </div>
                            <dl class="space-y-1.5 text-xs">
                                <div><dt class="text-slate-400">Kedatangan:</dt><dd class="font-medium" x-text="`${$wire.tanggal_kedatangan || '-'} (${$wire.bandara_tujuan || '-'})`"></dd></div>
                                <div><dt class="text-slate-400">Maskapai:</dt><dd class="font-medium" x-text="$wire.maskapai || '-'"></dd></div>
                                <div><dt class="text-slate-400">Nama Kaisha:</dt><dd class="font-bold text-slate-800 dark:text-white" x-text="$wire.nama_kaisha || '-'"></dd></div>
                                <div><dt class="text-slate-400">Penanggung Jawab:</dt><dd class="font-medium" x-text="`${$wire.nama_penanggung_jawab || '-'} (+81 ${$wire.telepon_penanggung_jawab || '-'})`"></dd></div>
                            </dl>
                        </div>

                    </div>

                </div>

                <!-- Step Navigation Action Buttons (Both Desktop & Mobile Friendly) -->
                <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                    
                    <!-- Back Button -->
                    <button type="button" 
                            x-show="step > 1" 
                            @click="prevStep()" 
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-bold transition-all">
                        <span class="material-symbols-outlined text-lg">arrow_back</span>
                        Sebelumnya
                    </button>
                    
                    <div x-show="step === 1" class="text-xs text-slate-400 italic">
                        Langkah 1 dari 5
                    </div>

                    <!-- Next or Submit Button -->
                    <div class="flex items-center gap-2">
                        <!-- Next Step Button (Step 1-4) -->
                        <button type="button" 
                                x-show="step < totalSteps" 
                                @click="advanceStep()" 
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#0049FF] hover:bg-blue-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-blue-500/20 active:scale-98 transition-all">
                            Lanjutkan
                            <span class="material-symbols-outlined text-lg">arrow_forward</span>
                        </button>

                        <!-- Final Submit Button (Step 5) -->
                        <button type="submit" 
                                x-show="step === totalSteps" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-7 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs sm:text-sm font-bold shadow-lg shadow-emerald-600/25 active:scale-98 transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                            <span wire:loading.remove class="material-symbols-outlined text-lg">send</span>
                            <span wire:loading class="material-symbols-outlined text-lg animate-spin">progress_activity</span>
                            <span wire:loading.remove>Kirim Formulir</span>
                            <span wire:loading>Menyimpan...</span>
                        </button>
                    </div>

                </div>

            </form>

        </div>

        <!-- Footer Notice -->
        <footer class="mt-6 text-center text-xs text-slate-400 dark:text-slate-500">
            &copy; {{ date('Y') }} PT Sumber Rezeki Exata Indonesia. Seluruh hak cipta dilindungi undang-undang.
        </footer>
    </main>


    <!-- Link State & Password Gate Overlays -->
    @if ($linkState !== 'valid')
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center px-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-md w-full p-8 text-center">
                @if ($linkState === 'not_found')
                    <div class="w-16 h-16 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-500 border border-rose-100 dark:border-rose-900 flex items-center justify-center mx-auto mb-5">
                        <span class="material-symbols-outlined text-3xl">link_off</span>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white mb-2">Tautan Tidak Dikenal</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Tautan magic link tidak valid atau tidak ditemukan. Pastikan Anda membuka tautan yang dikirimkan oleh admin.</p>
                @elseif ($linkState === 'expired')
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-500 border border-amber-100 dark:border-amber-900 flex items-center justify-center mx-auto mb-5">
                        <span class="material-symbols-outlined text-3xl">schedule</span>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white mb-2">Tautan Telah Kadaluarsa</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Masa aktif tautan pengajuan ini telah berakhir. Silakan hubungi admin untuk mendapatkan tautan baru.</p>
                @elseif ($linkState === 'exhausted')
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 flex items-center justify-center mx-auto mb-5">
                        <span class="material-symbols-outlined text-3xl">block</span>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white mb-2">Kuota Pengisian Telah Selesai</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Formulir ini sudah pernah diisi atau kuota pengisian telah habis. Hubungi admin jika ada kendala.</p>
                @endif
            </div>
        </div>
    @elseif (! $isAuthorized)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center px-4"
             x-data="{ showPass: false }">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-sm w-full p-8">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-[#0049FF] border border-blue-100 dark:border-blue-900 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-3xl">lock</span>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white mb-1">Masukkan Kata Sandi</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Formulir ini dilindungi kata sandi.
                        @if ($applicantName)
                            <br>Selamat datang, <strong class="text-slate-700 dark:text-slate-200">{{ $applicantName }}</strong>.
                        @endif
                    </p>
                </div>

                <form wire:submit.prevent="verifyPassword" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Kata Sandi Formulir
                        </label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'"
                                   wire:model="inputPassword"
                                   placeholder="Masukkan kata sandi..."
                                   autofocus
                                   class="w-full pl-4 pr-11 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-[#0049FF] focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                            <button type="button"
                                    @click="showPass = !showPass"
                                    tabindex="-1"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <span class="material-symbols-outlined text-lg" x-text="showPass ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        @error('inputPassword')
                            <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="w-full py-3 rounded-xl bg-[#0049FF] hover:bg-blue-700 text-white text-sm font-bold shadow-md shadow-blue-500/20 transition-all disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <span wire:loading.remove class="material-symbols-outlined text-lg">lock_open</span>
                        <span wire:loading class="material-symbols-outlined text-lg animate-spin">progress_activity</span>
                        <span wire:loading.remove>Buka Formulir</span>
                        <span wire:loading>Memverifikasi...</span>
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
