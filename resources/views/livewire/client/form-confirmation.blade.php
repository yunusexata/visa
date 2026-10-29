
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

        <!-- Nama Lengkap -->
        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
            <input type="text" 
                    id="name" 
                    name="name" 
                    value="{{ $name }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 text-center text-[#111827] uppercase placeholder-gray-400"
                    readonly>
        </div>

        <!-- Nomor WA Saat Ini -->
        <div>
            <label for="phone_number" class="block text-sm font-semibold text-gray-700 mb-1">
                Nomor Whatsapp Aktif saat ini
                <br>
                <span class="text-xs font-normal text-gray-500">(*Wajib di isi! karena digunakan untuk update info & mengirim kwitansi pencairan nenkin)</span>
                <span class="text-red-500">*</span>
            </label>
            <div class="flex rounded-lg shadow-sm">
                <!-- Country Prefix Select -->
                <div class="relative shrink-0">
                    <select id="phone_country_code" 
                            name="phone_country_code" 
                            wire:model.live="phone_country_code" 
                            required
                            class="h-[44px] px-3 py-2.5 bg-gray-50 border rounded-l-lg text-sm font-semibold text-[#111827] focus:outline-none focus:ring-2 focus:ring-[#F97316] transition @error('phone_country_code') border-red-500 @else border-gray-300 @enderror">
                        <option value="">Pilih Kode</option>
                        <option value="62">INDO +62</option>
                        <option value="81">JPN +81</option>
                    </select>
                </div>

                <!-- Phone Number Input (Number, Alpine.js leading zero stripper) -->
                <div class="relative flex-1">
                    <input type="number" 
                           id="phone_number" 
                           name="phone_number" 
                           wire:model="phone_number" 
                           x-data 
                           x-on:input="$el.value = $el.value.replace(/^[0123456]+/, '')"
                           min="1"
                           placeholder="{{ $phone_country_code == '81' ? '8012345678' : '81234567890' }}"
                           class="w-full h-[44px] px-4 py-2.5 rounded-r-lg border border-l-0 text-[#111827] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#F97316] transition @error('phone_number') border-red-500 @else border-gray-300 @enderror"
                           required>
                </div>
            </div>

            @error('phone_country_code')
                <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
            @enderror
            @error('phone_number')
                <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        
        <!-- Status Penerimaan Nenkin 80% -->
        <div>
            <label for="nenkin_80_received_type" class="block text-sm font-semibold text-gray-700 mb-1">
                Status Penerimaan Nenkin 80% 
            <span class="text-red-500">*</span>
            </label>
            <select id="nenkin_80_received_type" 
                    name="nenkin_80_received_type"
                    wire:model="nenkin_80_received_type" 
                    required
                    class="w-full px-4 py-2.5 bg-white rounded-lg border text-[#111827] focus:outline-none focus:ring-2 focus:ring-[#F97316] transition @error('nenkin_80_received_type') border-red-500 focus:ring-red-500 @else border-gray-300 @enderror">
                <option value="">-- Pilih Status --</option>
                @foreach (\App\Models\Client\ClientConfirmation::NENKIN_80_RECEIVED_TYPE_CHOICE as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
            @error('nenkin_80_received_type')
                <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <!-- Posisi Saat Ini -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Posisi Saat Ini
                <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-3">

                @foreach (\App\Models\Client\ClientConfirmation::CURRENT_POSITION_CHOICE as $key => $value)
                    <label class="flex items-center justify-center px-4 py-3 border rounded-lg cursor-pointer transition select-none hover:bg-gray-50 border-gray-300 [&:has(input:checked)]:border-[#F97316] [&:has(input:checked)]:bg-orange-50 [&:has(input:checked)]:text-[#F97316]">
                        <input type="radio" wire:model.live="current_position" value="{{$key}}" class="sr-only" required>
                        <span class="text-sm font-medium">{{$value}}</span>
                    </label>
                @endforeach
            </div>
            @error('current_position')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Rencana Kembali ke Jepang (6 Bulan) -->
        @if ($current_position === \App\Models\Client\ClientConfirmation::CURRENT_POSITION_INDONESIA)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Rencana Kembali ke Jepang dalam 6 Bulan?
                    <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    
                    @foreach (\App\Models\Client\ClientConfirmation::BACK_TO_JP_TYPE_CHOICE as $key => $value)
                        <label class="flex items-center justify-center px-4 py-3 border rounded-lg cursor-pointer transition select-none hover:bg-gray-50 border-gray-300 [&:has(input:checked)]:border-[#F97316] [&:has(input:checked)]:bg-orange-50 [&:has(input:checked)]:text-[#F97316]">
                            <input type="radio" id="back_to_jp_type_{{ $key }}" wire:model.live="back_to_jp_type" value="{{ $key }}" class="sr-only" required>
                            <span class="text-sm font-medium">{{$value}}</span>
                        </label>
                    @endForeach
                    
                    
                </div>
                <p class="text-gray-400 text-[10px] mt-0.5">
                    Jawaban Ya, Hanya Untuk Yang Sudah Tanda Tangan Kontrak / Sudah di Telfon Oleh Pihak Kaisha/Kumiai.
                    <span class="text-red-500">*</span>
                </p>
                
                @error('back_to_jp_type')
                    <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        @elseif ($current_position === \App\Models\Client\ClientConfirmation::CURRENT_POSITION_JEPANG)
        
            <div id="container_estimasi" class="transition duration-300">
                <label for="estimated_return" class="block text-sm font-semibold text-gray-700 mb-1">
                    Buat Kamu Yang Posisi Sudah Ada Di Jepang, Estimasi Kepulangan
                    <span class="text-red-500">*</span>
                </label>
                <input type="month" 
                    id="estimated_return" 
                    wire:model="estimated_return" 
                    required
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-[#111827] focus:outline-none focus:ring-2 focus:ring-[#F97316] focus:border-transparent transition">
                    <p class="text-gray-400 text-[10px] mt-0.5">Silakan pilih bulan & tahun perkiraan Kepulangan Anda.</p>
                @error('estimated_return')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <!-- Estimasi Keberangkatan (Conditional) -->
        @if ($back_to_jp_type === \App\Models\Client\ClientConfirmation::BACK_TO_JP_TYPE_YES &&
        $current_position === \App\Models\Client\ClientConfirmation::CURRENT_POSITION_INDONESIA )
            
            <div id="container_estimasi" class="transition duration-300">
                <label for="departure" class="block text-sm font-semibold text-gray-700 mb-1">
                        Estimasi Bulan Kembali Ke Jepang
                        <span class="text-red-500">*</span>
                </label>
                <input type="month" 
                    id="departure" 
                    wire:model.live="departure" 
                    required
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-[#111827] focus:outline-none focus:ring-2 focus:ring-[#F97316] focus:border-transparent transition">
                <p class="text-gray-400 text-[10px] mt-0.5">
                    Silakan pilih bulan & tahun perkiraan keberangkatan Anda.
                </p>
                @error('departure')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
        @endif

        <!-- Alamat Indonesia -->
        <div>
            <label for="indonesia_address" class="block text-sm font-semibold text-gray-700 mb-1">
                Alamat Lengkap Indonesia
                <span class="text-red-500">*</span>
            </label>
            <textarea id="indonesia_address" 
                        name="indonesia_address" 
                        rows="3" 
                        wire:model="indonesia_address"
                        required
                        placeholder="Masukkan alamat detail (Jalan, No. Rumah, RT/RW, Kecamatan, Kabupaten, Provinsi)"
                        class="w-full px-4 py-2.5 rounded-lg border text-[#111827] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#F97316] transition @error('indonesia_address') border-red-500 focus:ring-red-500 @else border-gray-300 @enderror"></textarea>
            @error('indonesia_address')
                <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
            @enderror
        </div>
            
        <!-- NIK -->
        <div>
            <label for="nik" class="block text-sm font-semibold text-gray-700 mb-2">
                NIK sesuai KTP
                <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                    id="nik" 
                    name="nik" 
                    wire:model="nik"
                    maxlength="16"
                    inputmode="numeric"
                    required
                    class="w-full px-4 py-2.5 rounded-lg border text-[#111827] uppercase placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#F97316] transition @error('nik') border-red-500 focus:ring-red-500 @else border-gray-300 @enderror"
                    placeholder="Masukkan NIK sesuai KTP">
            @error('nik')
                <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
            @enderror
        </div>
        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" 
                    class="w-full bg-[#F97316] text-white py-3 px-4 rounded-lg font-bold hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-200 transition-all cursor-pointer shadow-md">
                SIMPAN DATA
            </button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        Livewire.on('redirect-dashboard', () => {
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 3000); // 3 detik
        });
    </script>
@endpush