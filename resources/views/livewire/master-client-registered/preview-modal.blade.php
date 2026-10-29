
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
                <div x-data="{ tab: 'registered' }">
                    {{-- Tabs --}}
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-8">

                            <button
                                type="button"
                                class="border-b-2 px-1 py-4 text-sm font-medium"
                                wire:click="getData('registered', 'fix')"
                            >
                                Data Client Konfirmasi
                            </button>
                            @foreach ($closingHistories as $closing)
                                <button
                                    type="button"
                                    class="border-b-2 px-1 py-4 text-sm font-medium"
                                    wire:click="getData('{{ $closing->id }}', 'closing')"
                                >
                                    Closing {{ $closing->batch_number }} {{ $closing->created_at->format('Ymd H_i_s') }}
                                </button>
                            @endforeach
                            <button
                                type="button"
                                class="border-b-2 px-1 py-4 text-sm font-medium"
                                wire:click="getData('unclosed', 'fix')"
                            >
                                Data Belum Closing
                            </button>
                            <button
                                type="button"
                                class="border-b-2 px-1 py-4 text-sm font-medium"
                                wire:click="getData('unconfirmed', 'fix')"
                            >
                                Data Belum Konfirmasi
                            </button>
                            <button
                                type="button"
                                class="border-b-2 px-1 py-4 text-sm font-medium"
                                wire:click="getData('kokumin', 'fix')"
                            >
                                Data Client Kokumin
                            </button>

                        </nav>
                    </div>

                    {{-- REGISTERED TAB --}}
                    <div class="mt-4">
                        <div class="overflow-x-auto max-h-[450px] overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                
                            {{-- <livewire:master-client-registered.datatable-preview
                                :selectedBatch="$selectedBatch"
                                        wire:key="datatable-preview"
                            /> --}}
                            
                            <div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
                                <div class="relative w-full bg-white dark:bg-gray-900 overflow-y-scroll max-h-[95vw]">
                                    {{-- Blocking overlay --}}
                                    <div class="absolute inset-0 bg-white/70 dark:bg-gray-900/70 z-50 flex items-center justify-center backdrop-blur-[2px]">
                                        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 flex flex-col items-center gap-3">
                                            <svg class="animate-spin h-10 w-8 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Sedang mencari data anda ...</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Footer Totals Section -->
                                <div class="mt-8 pt-6 border-t border-gray-200 p-3">
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4">

                                        <!-- Speed Card -->
                                        <div class="bg-white w-100 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-[#00ff3c] p-4 flex justify-between px-5">
                                            <span class="text-3xl font-medium text-gray-500 uppercase">Speed</span>
                                            <span class="text-4xl font-bold text-gray-800">{{ $totals['total_speed'] ?? 0 }}</span>
                                        </div>
                                        
                                        <!-- Adjustment Card -->
                                        <div class="bg-white w-100 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-[#fffb00] p-4 flex justify-between px-5">
                                            <span class="text-3xl font-medium text-gray-500 uppercase">Adjustment</span>
                                            <span class="text-4xl font-bold text-gray-800">{{ $totals['total_adjustment'] ?? 0 }}</span>
                                        </div>
                                        
                                        <!-- Pending Card -->
                                        <div class="bg-white w-100 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-[#465FFF] p-4 flex justify-between px-5">
                                            <span class="text-3xl font-medium text-gray-500 uppercase">Pending</span>
                                            <span class="text-4xl font-bold text-gray-800">{{ $totals['total_pending'] ?? 0 }}</span>
                                        </div>

                                        <!-- RE Card -->
                                        <div class="bg-white w-100 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-[#ff9900] p-4 flex justify-between px-5">
                                            <span class="text-3xl font-medium text-gray-500 uppercase">RE</span>
                                            <span class="text-4xl font-bold text-gray-800">{{ $totals['total_re'] ?? 0 }}</span>
                                        </div>
                                        
                                        <!-- JPN Card -->
                                        <div class="bg-white w-100 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-[#ff0015] p-4 flex justify-between px-5">
                                            <span class="text-3xl font-medium text-gray-500 uppercase">JPN</span>
                                            <span class="text-4xl font-bold text-gray-800">{{ $totals['total_jpn'] ?? 0 }}</span>
                                        </div>

                                        <!-- Kokumin Card (Using Purple as a distinct color) -->
                                        <div class="bg-white w-100 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-purple-500 p-4 flex justify-between px-5">
                                            <span class="text-3xl font-medium text-gray-500 uppercase">Kokumin</span>
                                            <span class="text-4xl font-bold text-gray-800">{{ $totals['total_kokumin'] ?? 0 }}</span>
                                        </div>
                                        
                                        <!-- AI Card -->
                                        <div class="bg-white w-100 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-[#465FFF] p-4 flex justify-between px-5">
                                            <span class="text-3xl font-medium text-gray-500 uppercase">AI</span>
                                            <span class="text-4xl font-bold text-gray-800">{{ $totals['total_ai'] ?? 0 }}</span>
                                        </div>

                                        <!-- MT Card -->
                                        <div class="bg-white w-100 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-[#00ff3c] p-4 flex justify-between px-5">
                                            <span class="text-3xl font-medium text-gray-500 uppercase">MT</span>
                                            <span class="text-4xl font-bold text-gray-800">{{ $totals['total_mt'] ?? 0 }}</span>
                                        </div>

                                        <!-- SN Card -->
                                        <div class="bg-white w-100 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-[#ff0015] p-4 flex justify-between px-5">
                                            <span class="text-3xl font-medium text-gray-500 uppercase">SN</span>
                                            <span class="text-4xl font-bold text-gray-800">{{ $totals['total_sn'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Header -->
                                <div class="mb-4 flex flex-col gap-4 px-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                        
                                        <div class="flex items-center gap-2 {{ !isset($show_filter) || $show_filter == true ? '' : 'hidden' }}">
                                            <label class="text-sm font-medium text-black dark:text-white shrink-0">
                                                Show
                                            </label>
                                            <div class="relative z-20 bg-transparent min-w-[80px] dark:bg-form-input">
                                                <select 
                                                    wire:model.live.change="length" 
                                                    class="relative z-20 w-full appearance-none rounded-lg border border-gray-300 bg-transparent pl-4 pr-10 h-[42px] text-sm text-black outline-none transition focus:border-primary active:border-primary dark:border-gray-700 dark:text-white"
                                                >
                                                    @foreach ($lengthOptions as $item)
                                                        <option value="{{ $item }}" class="text-body dark:bg-boxdark">{{ $item }}</option>
                                                    @endforeach
                                                </select>
                                                
                                                <span class="absolute right-3 top-1/2 z-30 -translate-y-1/2 text-body pointer-events-none">
                                                    <svg class="fill-current" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.29289 8.29289C5.68342 7.90237 6.31658 7.90237 6.70711 8.29289L12 13.5858L17.2929 8.29289C17.6834 7.90237 18.3166 7.90237 18.7071 8.29289C19.0976 8.68342 19.0976 9.31658 18.7071 9.70711L12.7071 15.7071C12.3166 16.0976 11.6834 16.0976 11.2929 15.7071L5.29289 9.70711C4.90237 9.31658 4.90237 8.68342 5.29289 8.29289Z"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Table -->
                                <div class="overflow-hidden">
                                    <div class="w-full overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800 relative">
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
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">JENIS PENCAIRAN</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">TANDA TANGAN</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">POSISI SAAT INI</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">BULAN RE ENTRY</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">ALASAN RE ENTRY</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">STATUS EKSPOR</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">WAKTU CLOSING</th>
                                                </tr>
                                            </thead>

                                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                                                @if ($data)
                                                    @foreach ($data as $index => $item)
                                                        @php
                                                            /*
                                                            * Colour matrix:
                                                            * PENDING                                         → blue   #465FFF
                                                            * SPEED                                           → green  #00ff3c
                                                            * NORMAL + ALASAN RE ENTRY = akan kembali jepang  → red    #ff0015
                                                            * NORMAL + ALASAN RE ENTRY = sudah di jepang      → orange #ff9900
                                                            * ADJUSTMENT                                      → yellow #fffb00
                                                            */
                                                            $rowColor = '';

                                                            $claimType       = $item?->confirmation?->claim_type ?? '';
                                                            $currentPosition = $item->confirmation?->current_position;
                                                            $backToJpType    = $item->confirmation?->back_to_jp_type;
                                                            if(in_array($claimType, [
                                                                App\Models\Client\Client::CLAIM_TYPE_SPEED,
                                                                App\Models\Client\Client::CLAIM_TYPE_ADJUSTMENT
                                                            ]) && !$item?->esign
                                                            ){
                                                                $rowColor = '#465FFF';
                                                            }elseif ($claimType === App\Models\Client\Client::CLAIM_TYPE_SPEED) {
                                                                $rowColor = '#00ff3c'; // hijau
                                                            } elseif ($claimType === App\Models\Client\Client::CLAIM_TYPE_ADJUSTMENT) {
                                                                $rowColor = '#fffb00'; // kuning
                                                            } elseif ($claimType === App\Models\Client\Client::CLAIM_TYPE_NORMAL) {
                                                                if ($currentPosition === App\Models\Client\ClientConfirmation::CURRENT_POSITION_JEPANG) {
                                                                    $rowColor = '#ff9900'; // orange — sudah di jepang
                                                                } elseif (
                                                                    $currentPosition === App\Models\Client\ClientConfirmation::CURRENT_POSITION_INDONESIA
                                                                    && $backToJpType === App\Models\Client\ClientConfirmation::BACK_TO_JP_TYPE_YES
                                                                ) {
                                                                    $rowColor = '#ff0015'; // merah — akan kembali ke jepang
                                                                }
                                                            }

                                                            // WHITE = MATRIKS SPEED/ADJUSMENT NOT ESIGNED

                                                            $rowStyle = $rowColor ? "background-color: {$rowColor};" : '';
                                                            $rowStyle .='
                                                                border:1px solid #000;
                                                                padding:4px;
                                                                white-space: nowrap;
                                                            ';
                                                            // Alasan re entry text
                                                            
                                                            $alasanReEntry = '';
                                                            if ($currentPosition === App\Models\Client\ClientConfirmation::CURRENT_POSITION_JEPANG) {
                                                                $alasanReEntry = 'SUDAH DIJEPANG';
                                                            } elseif (
                                                                $currentPosition === App\Models\Client\ClientConfirmation::CURRENT_POSITION_INDONESIA
                                                                && $backToJpType === App\Models\Client\ClientConfirmation::BACK_TO_JP_TYPE_NO
                                                            ) {
                                                                $alasanReEntry = 'TIDAK KEMBALI KE JEPANG';
                                                            } elseif (
                                                                $currentPosition === App\Models\Client\ClientConfirmation::CURRENT_POSITION_INDONESIA
                                                                && $backToJpType === App\Models\Client\ClientConfirmation::BACK_TO_JP_TYPE_YES
                                                            ) {
                                                                $alasanReEntry = 'AKAN KEMBALI KE JEPANG';
                                                            }
                                                        @endphp
                                                        <tr style="{{ $rowStyle }}">
                                                            <td style="{{ $rowStyle }}">{{ $loop->iteration }}</td>
                                                            <td style="{{ $rowStyle }}">{{ mb_strtoupper($item['no_jp']) }}</td>
                                                            <td style="{{ $rowStyle }}">{{ mb_strtoupper($item['name']) }}</td>
                                                            <td style="{{ $rowStyle }}">{{ $item['dob'] ? Carbon\Carbon::parse($item['dob'])->format('Ymd') : '' }}</td>
                                                            <td style="{{ $rowStyle }}">{{ mb_strtoupper($item['facebook']) }}</td>
                                                            <td style="{{ $rowStyle }}">{{ mb_strtoupper($item['instagram']) }}</td>
                                                            <td style="{{ $rowStyle }}">{{ $item['phone'] }}</td>
                                                            <td style="{{ $rowStyle }}">{{ mb_strtoupper($item['pic']) }}</td>
                                                            <td style="{{ $rowStyle }}">{{ mb_strtoupper($item['lpk']) }}</td>
                                                            <td style="{{ $rowStyle }}">{{ mb_strtoupper($item['note']) }}</td>
                                                            @php
                                                                $claimType = $item?->confirmation?->claim_type;
                                                                $text = '';

                                                                if (!$item?->confirmation || $claimType === App\Models\Client\Client::CLAIM_TYPE_NORMAL) {
                                                                    $text = App\Models\Client\Client::CLAIM_TYPE_NORMAL;
                                                                } elseif (in_array($claimType, [App\Models\Client\Client::CLAIM_TYPE_ADJUSTMENT, App\Models\Client\Client::CLAIM_TYPE_SPEED])) {
                                                                    // Jika esign true -> pakai claim_type asli, jika false -> PENDING
                                                                    $text = $item?->esign ? $claimType : App\Models\Client\Client::CLAIM_TYPE_PENDING;
                                                                }
                                                            @endphp
                                                            <td style="{{ $rowStyle }}">{{ mb_strtoupper($text) }}</td>
                                                            <td style="{{ $rowStyle }}">{{ mb_strtoupper($item['esign_exists'] ? 'Sudah' : 'Belum') }}</td>
                                                            <td style="{{ $rowStyle }}">{{ mb_strtoupper($item->confirmation?->current_position) }}</td>
                                                            <td style="{{ $rowStyle }}">{{ mb_strtoupper($item->confirmation?->departure_month ? Carbon\Carbon::createFromFormat('n-Y', $item->confirmation?->departure_month.'-'.$item->confirmation?->departure_year)->locale('id')->translatedFormat('F Y') : '') }}</td>
                                                            <td style="{{ $rowStyle }}">{{ mb_strtoupper($alasanReEntry) }}</td>
                                                            <td style="{{ $rowStyle }}">{{ $item['is_exported'] ? 'EXPORTED' : 'UN-EXPORTED' }}</td>
                                                            <td style="{{ $rowStyle }}">{{ $item->confirmation?->closing?->created_at ? Carbon\Carbon::parse($item->confirmation?->closing?->created_at)->format('Ymd H:i:s') : '' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                
                                {{ $data->links(data: ['scrollTo' => false]) }}
                                
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-ui.modal>


@push('scripts')
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