<div x-data="{ open: @entangle('showSurvey') }" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

    <!-- Modal Content Wrapper -->
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-slate-900 border border-indigo-100 dark:border-slate-800">
            
            <!-- Header -->
            <div class="mb-5 flex items-center justify-between border-b border-gray-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-500 font-bold text-2xl">rate_review</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Survei Kepuasan Pelanggan</h3>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Progres Pengisian</span>
                    <span class="text-xs font-bold text-brand-600 dark:text-brand-400">Langkah {{ $step }} dari 10</span>
                </div>
                <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-500 transition-all duration-300 ease-out" style="width: {{ ($step / 10) * 100 }}%"></div>
                </div>
            </div>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 p-3 text-xs text-red-600 dark:bg-red-500/10 dark:text-red-400 font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Wizard Steps -->
            <div class="space-y-4">

                <!-- Step 1: Kecepatan Layanan -->
                @if ($step === 1)
                    <div wire:key="step-container-1">
                        <h4 class="text-base font-bold text-slate-900 dark:text-white mb-4 leading-snug">
                            1. Seberapa puas Anda dengan kecepatan layanan kami?
                        </h4>
                        <div class="space-y-2">
                            @foreach(['Sangat Puas', 'Puas', 'Cukup Puas', 'Tidak Puas'] as $index => $option)
                                <label wire:key="step-1-option-{{ $index }}" class="flex items-center p-3 rounded-xl border border-gray-200 dark:border-slate-800 cursor-pointer transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <input type="radio" name="q1" wire:model.live="responses.q1" value="{{ $option }}" class="text-brand-500 focus:ring-brand-500 mr-3 border-gray-300 dark:border-slate-700 dark:bg-slate-900">
                                    <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Step 2: Keramahan Staff -->
                @if ($step === 2)
                    <div wire:key="step-container-2">
                        <h4 class="text-base font-bold text-slate-900 dark:text-white mb-4 leading-snug">
                            2. Seberapa puas Anda dengan keramahan dan responsifitas staff kami?
                        </h4>
                        <div class="space-y-2">
                            @foreach(['Sangat Puas', 'Puas', 'Cukup Puas', 'Tidak Puas'] as $index => $option)
                                <label wire:key="step-2-option-{{ $index }}" class="flex items-center p-3 rounded-xl border border-gray-200 dark:border-slate-800 cursor-pointer transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <input type="radio" name="q2" wire:model.live="responses.q2" value="{{ $option }}" class="text-brand-500 focus:ring-brand-500 mr-3 border-gray-300 dark:border-slate-700 dark:bg-slate-900">
                                    <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Step 3: Kejelasan Informasi -->
                @if ($step === 3)
                    <div wire:key="step-container-3">
                        <h4 class="text-base font-bold text-slate-900 dark:text-white mb-4 leading-snug">
                            3. Apakah informasi yang kami berikan cukup jelas dan mudah dipahami?
                        </h4>
                        <div class="space-y-2">
                            @foreach(['Sangat Jelas', 'Jelas', 'Cukup Jelas', 'Tidak Jelas'] as $index => $option)
                                <label wire:key="step-3-option-{{ $index }}" class="flex items-center p-3 rounded-xl border border-gray-200 dark:border-slate-800 cursor-pointer transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <input type="radio" name="q3" wire:model.live="responses.q3" value="{{ $option }}" class="text-brand-500 focus:ring-brand-500 mr-3 border-gray-300 dark:border-slate-700 dark:bg-slate-900">
                                    <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Step 4: Rekomendasi (Q4) -->
                @if ($step === 4)
                    <div wire:key="step-container-4">
                        <h4 class="text-base font-bold text-slate-900 dark:text-white mb-4 leading-snug">
                            4. Apakah Anda bersedia merekomendasikan layanan kami kepada rekan atau keluarga Anda?
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <label wire:key="step-4-option-ya" class="flex flex-col items-center justify-center p-4 rounded-xl border-2 border-gray-200 dark:border-slate-800 cursor-pointer transition hover:border-brand-500">
                                <input type="radio" name="q4" wire:model.live="responses.q4" value="Ya" class="text-brand-500 focus:ring-brand-500 mb-2 border-gray-300 dark:border-slate-700 dark:bg-slate-900">
                                <span class="text-sm text-slate-700 dark:text-slate-300 font-bold">Ya</span>
                            </label>
                            <label wire:key="step-4-option-tidak" class="flex flex-col items-center justify-center p-4 rounded-xl border-2 border-gray-200 dark:border-slate-800 cursor-pointer transition hover:border-brand-500">
                                <input type="radio" name="q4" wire:model.live="responses.q4" value="Tidak" class="text-brand-500 focus:ring-brand-500 mb-2 border-gray-300 dark:border-slate-700 dark:bg-slate-900">
                                <span class="text-sm text-slate-700 dark:text-slate-300 font-bold">Tidak</span>
                            </label>
                        </div>
                    </div>
                @endif

                <!-- Step 5: Alasan Rekomendasi (Q5 - Kondisional jika Q4 = Ya) -->
                @if ($step === 5)
                    <div wire:key="step-container-5">
                        <h4 class="text-base font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                            5. Apa alasan utama Anda bersedia merekomendasikan kami?
                        </h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Jawaban Anda sangat berharga bagi peningkatan layanan kami.</p>
                        <textarea wire:model="responses.q5" rows="4" placeholder="Tulis alasan Anda di sini..." class="w-full rounded-xl border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-500 focus:ring-brand-500 text-sm"></textarea>
                    </div>
                @endif

                <!-- Step 6: Saran Perbaikan (Q6 - Kondisional jika Q4 = Ya) -->
                @if ($step === 6)
                    <div wire:key="step-container-6">
                        <h4 class="text-base font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                            6. Apakah ada saran agar layanan kami bisa lebih baik lagi?
                        </h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Masukkan konstruktif Anda membantu kami terus bertumbuh.</p>
                        <textarea wire:model="responses.q6" rows="4" placeholder="Tulis saran Anda di sini..." class="w-full rounded-xl border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-brand-500 focus:ring-brand-500 text-sm"></textarea>
                    </div>
                @endif

                <!-- Step 7: Kualitas Portal Nenkin -->
                @if ($step === 7)
                    <div wire:key="step-container-7">
                        <h4 class="text-base font-bold text-slate-900 dark:text-white mb-4 leading-snug">
                            7. Bagaimana pengalaman Anda menggunakan Portal Online Nenkin ini?
                        </h4>
                        <div class="space-y-2">
                            @foreach(['Sangat Baik', 'Baik', 'Cukup', 'Kurang Baik'] as $index => $option)
                                <label wire:key="step-7-option-{{ $index }}" class="flex items-center p-3 rounded-xl border border-gray-200 dark:border-slate-800 cursor-pointer transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <input type="radio" name="q7" wire:model.live="responses.q7" value="{{ $option }}" class="text-brand-500 focus:ring-brand-500 mr-3 border-gray-300 dark:border-slate-700 dark:bg-slate-900">
                                    <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Step 8: Transparansi Biaya/Proses -->
                @if ($step === 8)
                    <div wire:key="step-container-8">
                        <h4 class="text-base font-bold text-slate-900 dark:text-white mb-4 leading-snug">
                            8. Apakah Anda merasa proses pencairan dana Nenkin kami transparan?
                        </h4>
                        <div class="space-y-2">
                            @foreach(['Sangat Transparan', 'Transparan', 'Cukup Transparan', 'Tidak Transparan'] as $index => $option)
                                <label wire:key="step-8-option-{{ $index }}" class="flex items-center p-3 rounded-xl border border-gray-200 dark:border-slate-800 cursor-pointer transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <input type="radio" name="q8" wire:model.live="responses.q8" value="{{ $option }}" class="text-brand-500 focus:ring-brand-500 mr-3 border-gray-300 dark:border-slate-700 dark:bg-slate-900">
                                    <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Step 9: Kepercayaan -->
                @if ($step === 9)
                    <div wire:key="step-container-9">
                        <h4 class="text-base font-bold text-slate-900 dark:text-white mb-4 leading-snug">
                            9. Seberapa besar tingkat kepercayaan Anda terhadap PT Sumber Rezeki Exata Indonesia?
                        </h4>
                        <div class="space-y-2">
                            @foreach(['Sangat Tinggi', 'Tinggi', 'Cukup', 'Rendah'] as $index => $option)
                                <label wire:key="step-9-option-{{ $index }}" class="flex items-center p-3 rounded-xl border border-gray-200 dark:border-slate-800 cursor-pointer transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <input type="radio" name="q9" wire:model.live="responses.q9" value="{{ $option }}" class="text-brand-500 focus:ring-brand-500 mr-3 border-gray-300 dark:border-slate-700 dark:bg-slate-900">
                                    <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Step 10: Kepuasan Keseluruhan -->
                @if ($step === 10)
                    <div wire:key="step-container-10">
                        <h4 class="text-base font-bold text-slate-900 dark:text-white mb-4 leading-snug">
                            10. Secara keseluruhan, seberapa puas Anda dengan seluruh rangkaian layanan kami?
                        </h4>
                        <div class="space-y-2">
                            @foreach(['Sangat Puas', 'Puas', 'Cukup Puas', 'Tidak Puas'] as $index => $option)
                                <label wire:key="step-10-option-{{ $index }}" class="flex items-center p-3 rounded-xl border border-gray-200 dark:border-slate-800 cursor-pointer transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <input type="radio" name="q10" wire:model.live="responses.q10" value="{{ $option }}" class="text-brand-500 focus:ring-brand-500 mr-3 border-gray-300 dark:border-slate-700 dark:bg-slate-900">
                                    <span class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            <!-- Footer Actions -->
            <div class="mt-6 flex gap-3 border-t border-gray-100 dark:border-slate-800 pt-4">
                @if ($step > 1)
                    <button type="button" wire:click="prevStep" class="flex-1 rounded-xl border border-gray-300 dark:border-slate-700 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition active:scale-95">
                        Sebelumnya
                    </button>
                @endif

                @if ($step < 10)
                    <button type="button" wire:click="nextStep" class="flex-1 rounded-xl bg-brand-500 py-3 text-sm font-semibold text-white hover:bg-brand-600 shadow-lg shadow-brand-500/25 transition active:scale-95">
                        Selanjutnya
                    </button>
                @else
                    <button type="button" wire:click="submit" class="flex-1 rounded-xl bg-emerald-600 py-3 text-sm font-semibold text-white hover:bg-emerald-700 shadow-lg shadow-emerald-600/25 transition active:scale-95">
                        Kirim Jawaban
                    </button>
                @endif
            </div>

        </div>
    </div>
</div>
