<div class="relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900">
    
    <div class="relative flex h-screen w-full flex-col justify-center sm:p-0 lg:flex-row dark:bg-gray-900">
        <!-- Form -->
            <div class="flex w-full flex-1 flex-col lg:w-1/2">
                <div class="w-full mt-4 px-5 h-auto top-0">
                
                @if (session()->has('error'))
                    <x-ui.alert
                        variant="error"
                        :message="session('error')"
                    />
                @endif
            </div>
            <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
                <div>
                    <div class="mb-5 sm:mb-8">
                        <h1 class="text-title-sm sm:text-title-md mb-2 font-bold text-gray-800 dark:text-white/90">
                            Masuk
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Masukkan email dan kata sandi Anda untuk masuk ke Nenkin Portal.
                        </p>
                    </div>
                    <div>
                        <form wire:submit="store">
                            @csrf
                            <div class="space-y-5">
                                <!-- Email -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Email<span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" id="email" wire:model="nameOrEmail" name="email" placeholder="info@gmail.com"
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30
                                        @error('nameOrEmail') border-red-500 focus:ring-red-500 @enderror" required/>
                                    @error('nameOrEmail')
                                        <p class="text-red-500 text-xs mt-1 font-medium">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                                <!-- Tanggal Lahir -->
                                <div>
                                    <div class="mb-1.5">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Tanggal Lahir<span class="text-red-500">*</span>
                                        </label>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <!-- Tanggal -->
                                        <div>
                                            <select wire:model="tanggal" name="tanggal" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('tanggal') border-red-500 focus:ring-red-500 @enderror"
                                            required>
                                                <option value="">Tanggal</option>
                                                @for ($i = 1; $i <= 31; $i++)
                                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <!-- Bulan -->
                                        <div>
                                            <select wire:model="bulan" name="bulan" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('bulan') border-red-500 focus:ring-red-500 @enderror"
                                            required>
                                                <option value="">Bulan</option>
                                                @foreach (['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $val => $name)
                                                    <option value="{{ $val }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!-- Tahun -->
                                        <div>
                                            <select wire:model="tahun" name="tahun" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('tahun') border-red-500 focus:ring-red-500 @enderror"
                                            required>
                                                <option value="">Tahun</option>
                                                @for ($i = date('Y'); $i >= 1950; $i--)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    @if ($errors->has('tanggal') || $errors->has('bulan') || $errors->has('tahun'))
                                        <p class="text-red-500 text-xs mt-1 font-medium">
                                            Tanggal lahir harus diisi dengan lengkap.
                                        </p>
                                    @endif
                                </div>
                                <!-- Button -->
                                <div>
                                    <button type="submit"
                                        class="bg-brand-orange shadow-theme-xs hover:bg-brand-orange/90 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-bold text-white transition">
                                        Masuk
                                    </button>
                                </div>
                                
                                <!-- Divider / Garis Pemisah "Atau" -->
                                <div class="relative flex my-5 items-center">
                                    <div class="flex-grow border-t border-gray-300"></div>
                                    <span class="flex-shrink mx-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Atau</span>
                                    <div class="flex-grow border-t border-gray-300"></div>
                                </div>
                                <button type="button"wire:click="registerWithGoogle"
                                    class="w-full flex items-center justify-center gap-3 px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                                        
                                        <!-- Google Official SVG Logo -->
                                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                        </svg>
                                        
                                        <span>Login dengan Google</span>
                                </button>
                                <div class="mb-5 sm:mb-8">
                                    <p class="text-sm text-center font-bold text-gray-800 dark:text-white/90">
                                        PT SUMBER REZEKI EXATA INDONESIA
                                    </p>
                                    <p class="text-sm text-center text-gray-300">
                                        KONSULTAN NENKIN NO 1 DI INDONESIA
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @php
            $waNumber = config('exata.admin_wa_number');
            $waLink = preg_replace('/[^0-9]/', '', $waNumber);
            if (strpos($waLink, '0') === 0) {
                $waLink = '62' . substr($waLink, 1);
            }
        @endphp
        <div class="bg-brand-navy relative hidden h-full w-full items-center lg:flex lg:w-1/2 justify-center p-12">
            <div class="z-1 flex flex-col items-center justify-center text-center max-w-md">
                <div class="mb-6">
                    <h1 class="text-white font-black text-5xl tracking-tight uppercase">Visa Japan</h1>
                    <p class="text-[#0049FF] font-bold text-lg mt-1">by Japanindo Travel</p>
                    {{-- Brand shortform: legal name is PT Sumber Rezeki Exata Indonesia --}}
                </div>
                
        <input type="hidden" class="bg-primary-50 dark:bg-primary-500/15 text-primary-700 dark:text-primary-500
        bg-warning-50 dark:bg-warning-500/15 text-warning-700 dark:text-warning-500
        bg-success-50 dark:bg-success-500/15 text-success-700 dark:text-success-500
        bg-danger-50 dark:bg-danger-500/15 text-danger-700 dark:text-danger-500
        ">
                
                <p class="text-white text-base leading-relaxed mb-12">
                </p>
                
                <div class="mt-auto pt-6 border-t border-white/10 w-full flex flex-col items-center">
                    <span class="text-white/60 text-xs uppercase tracking-wider font-semibold">Butuh Bantuan? Hubungi WhatsApp CS</span>
                    <a href="https://wa.me/{{ $waLink }}" target="_blank" class="text-brand-orange hover:underline font-bold text-lg mt-1 flex items-center gap-1.5">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.665.989 3.3 1.489 4.965 1.493 5.485.002 9.948-4.46 9.95-9.95.002-2.657-1.03-5.158-2.905-7.034C16.824 1.786 14.325.755 11.666.755c-5.487 0-9.95 4.461-9.952 9.953-.001 1.792.483 3.548 1.401 5.12L2.094 22.07l6.553-1.717z"/>
                        </svg>
                        {{ $waNumber }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>