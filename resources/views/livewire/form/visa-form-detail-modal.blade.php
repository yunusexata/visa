<div 
    x-data="{
        isOpen: @entangle('isOpen'),
        step: 1,
        totalSteps: 5,
        stepsMeta: [
            { id: 1, title: 'Data Diri', subtitle: 'Identitas & Status', icon: 'badge' },
            { id: 2, title: 'Data Paspor', subtitle: 'Masa Berlaku & Kantor', icon: 'travel_explore' },
            { id: 3, title: 'Dokumen COE', subtitle: 'Eligibility & Izin Stay', icon: 'verified' },
            { id: 4, title: 'Tujuan Jepang', subtitle: 'Kaisha & Sponsor', icon: 'flight_takeoff' },
            { id: 5, title: 'Review & Berkas', subtitle: 'Lampiran & Simpan', icon: 'task_alt' }
        ],
        goToStep(target) {
            this.step = target;
            this.$nextTick(() => {
                const container = this.$refs.modalBody;
                if (container) container.scrollTo({ top: 0, behavior: 'smooth' });
            });
        },
        confirmSave() {
            Swal.fire({
                icon: 'question',
                title: 'Konfirmasi Pembaruan Data',
                text: 'Apakah Anda yakin ingin menyimpan seluruh perubahan data formulir visa ini?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan Perubahan',
                cancelButtonText: 'Periksa Lagi',
                confirmButtonColor: '#0049FF',
                cancelButtonColor: '#6B7280',
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    container: '!z-[110000]'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.save();
                }
            });
        }
    }"
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 z-[100000] flex items-center justify-center overflow-hidden bg-slate-900/75 backdrop-blur-xs p-2 sm:p-4"
>
    <!-- Modal Card (Full Size Width & Height) -->
    <div 
        class="w-full h-full max-w-[98vw] max-h-[96vh] bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl flex flex-col overflow-hidden"
    >
        <!-- Modal Top Bar -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#0049FF] to-[#001B5E] flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                    <span class="material-symbols-outlined text-2xl">description</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Detail Formulir Visa</h3>
                        @if($nomor_registrasi)
                            <span class="px-2.5 py-0.5 text-xs font-mono font-bold uppercase rounded-full bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-900">
                                {{ $nomor_registrasi }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Kelola dan perbarui rincian permohonan visa pemohon</p>
                </div>
            </div>

            <button type="button" 
                    wire:click="close"
                    class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition cursor-pointer">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <!-- Stepper Progress Header: Replikasi Nav Tab dari Client Form Submission -->
        <div class="border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50 px-6 py-3 shrink-0">
            <!-- Mobile Stepper Info -->
            <div class="sm:hidden mb-2">
                <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                    <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1">
                        <span class="material-symbols-outlined text-base text-[#0049FF]" x-text="stepsMeta[step - 1].icon"></span>
                        <span x-text="stepsMeta[step - 1].title"></span>
                    </span>
                    <span class="text-[#0049FF] font-bold" x-text="`Langkah ${step} dari ${totalSteps}`"></span>
                </div>
                <div class="h-1.5 w-full bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-[#0049FF] to-[#465fff] transition-all duration-300 ease-out rounded-full"
                         :style="`width: ${(step / totalSteps) * 100}%`"></div>
                </div>
            </div>

            <!-- Desktop Stepper Pill Tabs -->
            <div class="hidden sm:grid grid-cols-5 gap-2">
                <template x-for="s in stepsMeta" :key="s.id">
                    <button type="button" 
                            @click="goToStep(s.id)"
                            class="flex items-center gap-3 p-2.5 rounded-2xl text-left transition-all duration-200 cursor-pointer"
                            :class="step === s.id 
                                ? 'bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700' 
                                : 'hover:bg-slate-100/60 dark:hover:bg-slate-800/40 opacity-75'">
                        
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold shrink-0 transition-colors"
                             :class="step === s.id 
                                ? 'bg-[#0049FF] text-white shadow-sm shadow-blue-500/30 ring-2 ring-blue-500/20' 
                                : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400'">
                            <span x-text="s.id"></span>
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

        <!-- Form Body Area (Scrollable) -->
        <div x-ref="modalBody" class="flex-1 overflow-y-auto p-6 sm:p-8 space-y-6">
            
            <!-- STEP 1: Data Diri & Input Select Status -->
            <div x-show="step === 1" class="space-y-6">
                <!-- Status Permohonan Banner di Langkah 1 (Top Level) -->
                <div class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 text-xl">sync_saved_locally</span>
                            <label class="text-xs font-bold uppercase tracking-wider text-amber-900 dark:text-amber-200">
                                Status Permohonan Visa <span class="text-rose-500">*</span>
                            </label>
                        </div>
                        <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">Ubah status verifikasi dan progres berkas formulir visa</p>
                    </div>

                    <div class="w-full sm:w-64">
                        <select wire:model="status" class="w-full rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-800 dark:text-white shadow-xs focus:ring-2 focus:ring-amber-500">
                            @foreach(\App\Enums\Form\VisaFormStatus::options() as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('status') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Kode PIC -->
                    {{-- <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Kode PIC
                        </label>
                        <input type="text" wire:model="pic_code" placeholder="Contoh: PIC-001" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('pic_code') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div> --}}

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="nama_lengkap" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('nama_lengkap') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tempat Lahir -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Tempat Lahir <span class="text-rose-500">*</span>
                        </label>
                        <x-form.select-regency wire:model="tempat_lahir" id="modal_tempat_lahir" placeholder="Cari kabupaten/kota kelahiran..." />
                        @error('tempat_lahir') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tanggal Lahir -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Tanggal Lahir <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" wire:model="tanggal_lahir" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('tanggal_lahir') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- NIK KTP -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            NIK KTP (16 Digit) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="nik_ktp" maxlength="16" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('nik_ktp') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Telepon / WA -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            No. Telepon / WhatsApp <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="modal_telepon_indonesia" 
                               wire:model="telepon_indonesia" 
                               inputmode="tel"
                               x-on:input="$el.value = $el.value.replace(/[^0-9\-]/g, '')"
                               placeholder="Contoh: 0812-3456-7890"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                        @error('telepon_indonesia') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email Aktif -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Email Aktif <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" wire:model="email_aktif" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('email_aktif') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Alamat Indonesia -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Alamat Lengkap di Indonesia <span class="text-rose-500">*</span>
                        </label>
                        <textarea wire:model="alamat_indonesia" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
                        @error('alamat_indonesia') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Kode Pos Indonesia -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Kode Pos Indonesia
                        </label>
                        <input type="text" wire:model="kode_pos_indonesia" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('kode_pos_indonesia') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- STEP 2: Data di Paspor -->
            <div x-show="step === 2" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Nomor Paspor -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Nomor Paspor <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="nomor_paspor" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('nomor_paspor') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Kantor Imigrasi Penerbit Paspor -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Kantor Paspor Penerbit <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="kantor_paspor" list="modal_daftar_kantor_imigrasi" placeholder="Contoh: KANIM JAKARTA SELATAN" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm uppercase dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        <datalist id="modal_daftar_kantor_imigrasi">
                            <option value="KANIM KELAS I KHUSUS TPI SOEKARNO HATTA">
                            <option value="KANIM KELAS I KHUSUS TPI SURABAYA">
                            <option value="KANIM KELAS I KHUSUS TPI JAKARTA SELATAN">
                            <option value="KANIM KELAS I KHUSUS TPI MEDAN">
                            <option value="KANIM KELAS I KHUSUS TPI NGURAH RAI">
                            <option value="KANIM KELAS I TPI BANDUNG">
                            <option value="KANIM KELAS I TPI SEMARANG">
                            <option value="KANIM KELAS I TPI YOGYAKARTA">
                        </datalist>
                        @error('kantor_paspor') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tanggal Pengeluaran Paspor -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Tanggal Pengeluaran Paspor <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" wire:model="tanggal_pengeluaran_paspor" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('tanggal_pengeluaran_paspor') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tanggal Habis Berlaku Paspor -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Tanggal Habis Berlaku Paspor <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" wire:model="tanggal_habis_berlaku_paspor" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('tanggal_habis_berlaku_paspor') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- STEP 3: Dokumen COE -->
            <div x-show="step === 3" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Nomor COE -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Nomor Certificate of Eligibility (COE) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="nomor_coe" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('nomor_coe') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status Kependudukan di Jepang -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Status Kependudukan di Jepang (Visa Type) <span class="text-rose-500">*</span>
                        </label>
                        @php
                            $statusKependudukanOptions = [
                                'Specified Skilled Worker (SSW / Tokutei Ginou)',
                                'Technical Intern Training (TITP / Gino Jisshusei)',
                                'Engineer / Specialist in Humanities / Int Services (Gijinkoku)',
                                'Student (Ryugaku)',
                                'Dependent (Kazoku Taizai)',
                                'Business Manager',
                                'Lainnya',
                            ];
                        @endphp
                        <select wire:model="status_kependudukan_jepang" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                            @if ($status_kependudukan_jepang && !in_array($status_kependudukan_jepang, $statusKependudukanOptions))
                                <option value="{{ $status_kependudukan_jepang }}">{{ $status_kependudukan_jepang }}</option>
                            @endif
                            @foreach ($statusKependudukanOptions as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('status_kependudukan_jepang') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Lama Stay di Jepang -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Masa Tinggal di Jepang (Period of Stay) <span class="text-rose-500">*</span>
                        </label>
                        @php
                            $stayOptions = ['1 Tahun', '3 Tahun', '5 Tahun', '6 Bulan', 'Lainnya'];
                        @endphp
                        <select wire:model="lama_stay_jepang" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                            @if ($lama_stay_jepang && !in_array($lama_stay_jepang, $stayOptions))
                                <option value="{{ $lama_stay_jepang }}">{{ $lama_stay_jepang }}</option>
                            @endif
                            @foreach ($stayOptions as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('lama_stay_jepang') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- STEP 4: Tujuan Jepang & Kaisha -->
            <div x-show="step === 4" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Rencana Kedatangan -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Tanggal Rencana Tiba di Jepang <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" wire:model="tanggal_kedatangan" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('tanggal_kedatangan') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Bandara Tujuan -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Bandara Kedatangan di Jepang <span class="text-rose-500">*</span>
                        </label>
                        @php
                            $airportOptions = ['Haneda (HND)', 'Narita (NRT)', 'Fukuoka (FUK)', 'Osaka / Kansai (KIX)', 'Lainnya'];
                        @endphp
                        <select wire:model.live="bandara_tujuan" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                            @if ($bandara_tujuan && !in_array($bandara_tujuan, $airportOptions))
                                <option value="{{ $bandara_tujuan }}">{{ $bandara_tujuan }}</option>
                            @endif
                            @foreach ($airportOptions as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('bandara_tujuan') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror

                        <div x-show="$wire.bandara_tujuan === 'Lainnya'" class="mt-2.5">
                            <input type="text" 
                                   wire:model="bandara_lainnya" 
                                   placeholder="Sebutkan nama bandara tujuan lainnya di Jepang..." 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" />
                            @error('bandara_lainnya') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Maskapai -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Maskapai Penerbangan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="maskapai" placeholder="Contoh: Garuda Indonesia / ANA / JAL" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('maskapai') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nama Kaisha -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Nama Kaisha / Perusahaan di Jepang <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="nama_kaisha" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('nama_kaisha') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Alamat Kaisha di Jepang -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Alamat Kaisha / Perusahaan di Jepang <span class="text-rose-500">*</span>
                        </label>
                        <textarea wire:model="alamat_jepang" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
                        @error('alamat_jepang') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Kode Pos Kaisha -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Kode Pos Kaisha
                        </label>
                        <input type="text" wire:model="kode_pos_jepang" placeholder="Contoh: 100-0001" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-mono dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                    </div>

                    <!-- Telepon Kaisha -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            No. Telepon Kaisha di Jepang <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="modal_telepon_jepang" 
                               wire:model="telepon_jepang" 
                               inputmode="tel"
                               x-on:input="$el.value = $el.value.replace(/[^0-9\-]/g, '')"
                               placeholder="Contoh: 03-3423-1111"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                        @error('telepon_jepang') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Pernah Kerja di Jepang -->
                    <div class="md:col-span-2 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700/80 space-y-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Apakah Sebelumnya Pernah Bekerja / Tinggal di Jepang? <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-300">
                                <input type="radio" 
                                       name="modal_pernah_kerja_jepang"
                                       wire:model.live="pernah_kerja_jepang" 
                                       value="tidak" 
                                       class="text-[#0049FF] focus:ring-blue-500">
                                Tidak Pernah (Pertama Kali)
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-300">
                                <input type="radio" 
                                       name="modal_pernah_kerja_jepang"
                                       wire:model.live="pernah_kerja_jepang" 
                                       value="pernah" 
                                       class="text-[#0049FF] focus:ring-blue-500">
                                Ya, Pernah Bekerja Sebelumnya
                            </label>
                        </div>

                        <!-- Periode Kerja jika Pernah -->
                        <div x-show="$wire.pernah_kerja_jepang === 'pernah'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="pt-3 border-t border-slate-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Dari Bulan / Tahun:</span>
                                <input type="month" 
                                       id="modal_periode_kerja_mulai"
                                       wire:model="periode_kerja_mulai" 
                                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs dark:text-white focus:border-[#0049FF] outline-none">
                                @error('periode_kerja_mulai') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Sampai Bulan / Tahun:</span>
                                <input type="month" 
                                       id="modal_periode_kerja_selesai"
                                       wire:model="periode_kerja_selesai" 
                                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs dark:text-white focus:border-[#0049FF] outline-none">
                                @error('periode_kerja_selesai') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Penanggung Jawab di Jepang -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Nama Penanggung Jawab / Sponsor <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="nama_penanggung_jawab" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('nama_penanggung_jawab') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Telepon Penanggung Jawab <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="modal_telepon_penanggung_jawab" 
                               wire:model="telepon_penanggung_jawab" 
                               inputmode="tel"
                               x-on:input="$el.value = $el.value.replace(/[^0-9\-]/g, '')"
                               placeholder="Contoh: 03-3423-1111"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                        @error('telepon_penanggung_jawab') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Alamat Penanggung Jawab di Jepang <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="alamat_penanggung_jawab" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        @error('alamat_penanggung_jawab') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- STEP 5: Review & Berkas Lampiran -->
            <div x-show="step === 5" class="space-y-6">
                <!-- Tabel Berkas Lampiran Dokumen -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600 text-lg">attach_file</span>
                            Berkas Lampiran Dokumen Pemohon
                        </h4>
                        <span class="text-xs text-slate-500 font-mono">{{ count($attachments) }} dokumen terunggah</span>
                    </div>

                    @if (count($attachments) > 0)
                        <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold">
                                        <th class="py-2.5 px-4">JENIS DOKUMEN</th>
                                        <th class="py-2.5 px-4">NAMA FILE ASLI</th>
                                        <th class="py-2.5 px-4">UKURAN</th>
                                        <th class="py-2.5 px-4">STATUS</th>
                                        <th class="py-2.5 px-4 text-right">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach ($attachments as $att)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                            <td class="py-2.5 px-4 font-semibold text-slate-800 dark:text-white">
                                                {{ strtoupper(str_replace('_', ' ', $att['type'] ?? '-')) }}
                                            </td>
                                            <td class="py-2.5 px-4 text-slate-600 dark:text-slate-400 font-mono truncate max-w-[200px]" title="{{ $att['file_name'] ?? '-' }}">
                                                {{ $att['file_name'] ?? '-' }}
                                            </td>
                                            <td class="py-2.5 px-4 text-slate-500 dark:text-slate-400 font-mono">
                                                {{ isset($att['file_size']) ? number_format($att['file_size'] / 1024, 1) . ' KB' : '-' }}
                                            </td>
                                            <td class="py-2.5 px-4">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold font-mono bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                                    {{ $att['status'] ?? 'UPLOADED' }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-4 text-right">
                                                @if (!empty($att['file_path']))
                                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk($att['disk'] ?? config('filesystems.default'))->url($att['file_path']) }}"
                                                       target="_blank"
                                                       class="inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                                                        Buka File
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-6 text-center rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 text-xs text-slate-400">
                            Belum ada berkas dokumen lampiran yang diunggah untuk permohonan visa ini.
                        </div>
                    @endif
                </div>

                <!-- Input Catatan Admin / Verifikator -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Catatan Internal Verifikator / Petugas
                    </label>
                    <textarea wire:model="catatan" rows="3" placeholder="Catatan opsional mengenai status verifikasi atau kekurangan dokumen berkas..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
                </div>
            </div>
        </div>

        <!-- Modal Bottom Footer Controls -->
        <div class="flex items-center justify-between px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/80 shrink-0">
            <div>
                <button type="button" 
                        x-show="step > 1" 
                        @click="goToStep(step - 1)" 
                        class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer">
                    Kembali
                </button>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" 
                        wire:click="close" 
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 cursor-pointer">
                    Batal
                </button>

                <button type="button" 
                        x-show="step < totalSteps" 
                        @click="goToStep(step + 1)" 
                        class="px-5 py-2.5 rounded-xl bg-slate-800 dark:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold hover:bg-slate-700 dark:hover:bg-slate-100 cursor-pointer">
                    Lanjut
                </button>

                <button type="button" 
                        @click="confirmSave()" 
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#0049FF] to-[#001B5E] text-white text-xs font-bold shadow-md shadow-blue-500/25 hover:opacity-95 transition cursor-pointer">
                    <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
</div>

