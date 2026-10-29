
<x-ui.modal x-data="{ open: false }" @open-regular-modal.window="open = true"     @close-regular-modal.window="open = false"
 modalId="importModal" :isOpen="false" class="max-w-[96vw] p-10" wire:ignore.self>
    <div class="relative w-full bg-white dark:bg-gray-900 overflow-y-scroll max-h-[95vw]">
        {{-- Blocking overlay --}}
        <div wire:loading wire:target="input_file, storeImport" class="absolute inset-0 bg-white/70 dark:bg-gray-900/70 z-50 flex items-center justify-center backdrop-blur-[2px]">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 flex flex-col items-center gap-3">
                <svg class="animate-spin h-10 w-8 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-800 dark:text-white" wire:loading wire:target="input_file">Sedang Mengunggah & Memproses Excel...</span>
                <span class="text-sm font-semibold text-gray-800 dark:text-white" wire:loading wire:target="storeImport">Sedang Menyimpan Data ke Supabase...</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Mohon jangan menutup halaman ini</span>
            </div>
        </div>

        <h4 class="font-semibold text-gray-800 mb-7 text-title-sm dark:text-white/90">
            Import Excel Master Data Client
        </h4>

    <div class="border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="p-6">
            {{-- Upload File --}}
            <div class="mb-6">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    File Import Excel
                </label>

                <input
                    type="file"
                    wire:model="input_file"
                    accept=".xls,.xlsx"
                    wire:loading.attr="disabled"
                    wire:target="input_file, storeImport"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white disabled:opacity-50"
                >

                @error('input_file')
                    <p class="mt-2 text-sm text-red-500">
                        {{ $message }}
                    </p>
                @enderror

                {{-- Loading Indicators --}}
                <div wire:loading wire:target="input_file" class="mt-3 text-sm text-brand-600 dark:text-brand-400 flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Mengunggah dan membaca data Excel...</span>
                </div>
            </div>

            @if($previewBulkStatusRows)

                {{-- Input Nama Batch --}}
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800/30 rounded-xl border border-gray-200 dark:border-gray-800">
                    <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Nama Batch <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        wire:model="batch_name"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        placeholder="Contoh: Batch Juni 2026 - Osaka"
                        maxlength="100"
                        required
                    >
                    @error('batch_name')
                        <p class="mt-2 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div x-data="{ tab: 'valid' }">

                    {{-- Tabs --}}
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-8">

                            <button
                                type="button"
                                @click="tab = 'valid'"
                                :class="tab === 'valid'
                                    ? 'border-brand-500 text-brand-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="border-b-2 px-1 py-4 text-sm font-medium"
                            >
                                Imported
                            </button>

                            <button
                                type="button"
                                @click="tab = 'invalid'"
                                :class="tab === 'invalid'
                                    ? 'border-red-500 text-red-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="border-b-2 px-1 py-4 text-sm font-medium"
                            >
                                Invalid ({{ count($errorBulkStatusRows) }})
                            </button>

                        </nav>
                    </div>

                    {{-- VALID TAB --}}
                    <div
                        x-show="tab === 'valid'"
                        x-transition
                        class="mt-4"
                    >
                        <div class="overflow-x-auto max-h-[450px] overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">NO INPUT JPN</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">NAMA</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">TGL LAHIR</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">NAMA FB</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">NAMA IG</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">NO TELP</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">PIC</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">LPK</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">NOTE</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                                    @php $no = 1; @endphp

                                    @foreach($previewBulkStatusRows as $row)
                                        @if(!$row['error'])
                                            <tr>
                                                <td class="px-4 py-3">{{ $no++ }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['no_jp'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['name'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['dob'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['facebook'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['instagram'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['phone'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['pic'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['lpk'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['note'] }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- INVALID TAB --}}
                    <div
                        x-show="tab === 'invalid'"
                        x-transition
                        class="mt-4"
                    >
                        <div class="overflow-x-auto max-h-[450px] overflow-y-auto rounded-lg border border-red-200 dark:border-red-800">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-red-50 dark:bg-red-900/20">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">NO INPUT JPN</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">NAMA</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">TGL LAHIR</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">NAMA FB</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">NAMA IG</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">NO TELP</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">PIC</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">LPK</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">NOTE</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">PESAN ERROR</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                                    @php $no_error = 1; @endphp

                                    @foreach($previewBulkStatusRows as $row)
                                        @if($row['error'])
                                            <tr>
                                                <td class="px-4 py-3">{{ $no_error++ }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['no_jp'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['name'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['dob'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['facebook'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['instagram'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['phone'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['pic'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['lpk'] }}</td>
                                                <td class="px-4 py-3">{{ $row['data']['note'] }}</td>
                                                <td class="px-4 py-3">
                                                    @foreach($row['error'] as $field => $msg)
                                                        <div class="mb-1 rounded bg-red-100 px-2 py-1 text-xs text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                                            {{ is_array($msg) ? implode(', ', $msg) : $msg }}
                                                        </div>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            @endif
        </div>
        <div class="flex flex-col items-center justify-center p-4">
            {{-- Saving Loading Indicator --}}
            <div wire:loading wire:target="storeImport" class="mb-3 text-sm text-brand-600 dark:text-brand-400 flex items-center gap-2">
                <svg class="animate-spin h-5 w-5 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Menyimpan ke database Supabase...</span>
            </div>

            <button 
                class="inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-6 py-3 text-sm bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600 disabled:bg-brand-300" 
                type="button" 
                wire:click="storeImport"
                wire:loading.attr="disabled"
                wire:target="input_file, storeImport"
            >
                <span wire:loading.remove wire:target="storeImport">Simpan</span>
                <span wire:loading wire:target="storeImport">Menyimpan...</span>
            </button>
        </div>
    </div>
    </div>
</x-ui.modal>


@push('scripts')
    {{-- <script src="{{ asset('assets/js/jquery.min.js') }}"></script> --}}
    <script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('onSuccessImportModal', () => {

            window.dispatchEvent(
                new CustomEvent('close-regular-modal')
            );
        });
    });
    </script>
@endpush