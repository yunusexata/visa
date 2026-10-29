
        <!-- Main Card -->
        <div class="bg-white text-[#111827] rounded-2xl shadow-xl overflow-hidden border border-gray-200">
            <div class="bg-[#F97316] p-4 text-white text-center font-bold tracking-wide text-sm uppercase">
                Formulir Informasi Klien
            </div>

            <form wire:submit="store" class="p-6 sm:p-8 space-y-5">
                @csrf

                <!-- General Database Error Alert -->
                @error('error_database')
                <div class="bg-red-50 border-l-4 border-red-500 p-4 text-red-700 text-sm rounded-r-lg">
                    {{ $message }}
                </div>
                @enderror
                @if ($is_verified)
                    <button type="button"wire:click="registerWithGoogle"
                        class="w-full flex items-center justify-center gap-3 px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                            
                            <!-- Google Official SVG Logo -->
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                            </svg>
                            <span>Daftar dengan Google</span>
                    </button>
                    <!-- Divider / Garis Pemisah "Atau" -->
                    <div class="relative flex my-5 items-center">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="flex-shrink mx-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Atau</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" 
                            id="email" 
                            name="email" 
                            required 
                            placeholder="CONTOH: exata@gmail.com"
                            wire:model.defer="email"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-[#111827] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#F97316] focus:border-transparent transition">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Password -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Password (Tanggal Lahir DDMMYYYY)<span class="text-error-500">*</span>
                        </label>
                        <div x-data="{ showPassword: true }" class="relative">
                            <input type="text"
                                value="{{ $password_dob }}"
                                readonly
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30  @error('password_dob') is-invalid @enderror" />
                                @error('password_dob')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full bg-[#F97316] text-white py-3 px-4 rounded-lg font-bold hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-200 transition-all cursor-pointer shadow-md">
                            SIMPAN DATA
                        </button>
                    </div>
                @else
                    <!-- Nama Lengkap -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="name" 
                            id="name" 
                            name="name" 
                            required 
                            placeholder="CONTOH: exata indonesia"
                            wire:model.defer="name"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-[#111827] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#F97316] focus:border-transparent transition">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- DOB -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Tanggal Lahir<span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <!-- Tanggal -->
                            <div>
                                <select wire:model="tanggal" name="tanggal" 
                                    class="w-full px-2 py-2.5 rounded-lg border border-gray-300 text-sm text-[#111827] bg-white focus:outline-none focus:ring-2 focus:ring-[#F97316] focus:border-transparent transition @error('tanggal') border-red-500 focus:ring-red-500 @enderror"
                                    required>
                                    <option value="">Tanggal</option>
                                    @for ($i = 1; $i <= 31; $i++)
                                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <!-- Bulan -->
                            <div>
                                <select wire:model="bulan" name="bulan" 
                                    class="w-full px-2 py-2.5 rounded-lg border border-gray-300 text-sm text-[#111827] bg-white focus:outline-none focus:ring-2 focus:ring-[#F97316] focus:border-transparent transition @error('bulan') border-red-500 focus:ring-red-500 @enderror"
                                    required>
                                    <option value="">Bulan</option>
                                    @foreach (['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $val => $name)
                                        <option value="{{ $val }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Tahun -->
                            <div>
                                <select wire:model="tahun" name="tahun" 
                                    class="w-full px-2 py-2.5 rounded-lg border border-gray-300 text-sm text-[#111827] bg-white focus:outline-none focus:ring-2 focus:ring-[#F97316] focus:border-transparent transition @error('tahun') border-red-500 focus:ring-red-500 @enderror"
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
                    
                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="button" wire:click="verify" 
                                class="w-full bg-[#F97316] text-white py-3 px-4 rounded-lg font-bold hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-200 transition-all cursor-pointer shadow-md">
                            Verifikasi
                        </button>
                    </div>
                @endif
            </form>
        </div>