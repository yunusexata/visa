<div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
    <!-- Header -->
    <div class="mb-4 flex flex-col gap-4 px-5 sm:flex-row sm:items-center sm:justify-between sm:px-6 w-full">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center justify-between w-full">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-black dark:text-white shrink-0">
                    Show
                </label>
                <div class="relative z-20 bg-transparent min-w-[80px]">
                    <select 
                        wire:model.live.change="length" 
                        class="relative z-20 w-full appearance-none rounded-lg border border-gray-300 bg-transparent pl-4 pr-10 h-[42px] text-sm text-black outline-none transition focus:border-primary active:border-primary dark:border-gray-700 dark:text-white"
                    >
                        @foreach ($lengthOptions as $item)
                            <option value="{{ $item }}" class="text-body dark:bg-boxdark">{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <form onsubmit="event.preventDefault();" class="m-0">
                <div class="relative">
                    <button type="button" class="absolute -translate-y-1/2 left-4 top-1/2">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z"/>
                        </svg>
                    </button>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-[42px] pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[300px]"/>
                </div>
            </form>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="grid grid-cols-1 gap-3 px-5 sm:px-6 mb-5 sm:grid-cols-2 md:grid-cols-3 {{ $isCanGetAll ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }}">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Batch Client</label>
            <select wire:model.live="selectedBatch" class="h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="all">Semua Batch...</option>
                @foreach($batches as $b)
                    <option value="{{ $b['name'] }}">{{ $b['name'] }} ({{ $b['total'] }} klien)</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Kloter Closing</label>
            <select wire:model.live="selectedClosing" class="h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="all">Semua Kloter...</option>
                @foreach($closings as $c)
                    <option value="{{ $c['name'] }}">Kloter {{ $c['name'] }} ({{ $c['total'] }} klien)</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Tipe Dokumen</label>
            <select wire:model.live="documentType" class="h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="all">General (Ichijikin, Resi, Kwitansi)</option>
                <option value="resi">Resi</option>
                <option value="kwitansi">Kwitansi</option>
                <option value="ichijikin">Ichijikin</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Status Download</label>
            <select wire:model.live="filterStatus" class="h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="all">Semua Status...</option>
                <option value="downloaded">Mendownload</option>
                <option value="seeded">Downloaded*</option>
                <option value="not_downloaded">Belum Mendownload</option>
            </select>
        </div>

        @can(\App\Helpers\PermissionHelper::transform(\App\Helpers\PermissionHelper::ACCESS_FILTER_ALL_PIC, \App\Helpers\PermissionHelper::TYPE_READ))
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Pilih PIC (Sales Agent)</label>
                <select wire:model.live="bulkPic" class="h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Semua PIC...</option>
                    @foreach(\App\Models\User::PIC_CHOICE as $value => $name)
                        <option value="{{ $value }}">{{ ucfirst($name) }}</option>
                    @endforeach
                </select>
            </div>
        @endcan
    </div>

    <!-- Summary Cards Row -->
    <div class="px-5 sm:px-6 mb-6">
        <style>
            .card-sudah-download {
                background-color: rgba(240, 253, 244, 0.7);
                border: 1px solid rgba(187, 247, 208, 0.8) !important;
                border-left: 4px solid #22c55e !important;
            }
            .card-sudah-download .card-label {
                color: #15803d !important;
            }
            .card-sudah-download .card-value {
                color: #166534 !important;
            }

            .card-belum-download {
                background-color: rgba(254, 242, 242, 0.7);
                border: 1px solid rgba(254, 202, 202, 0.8) !important;
                border-left: 4px solid #ef4444 !important;
            }
            .card-belum-download .card-label {
                color: #b91c1c !important;
            }
            .card-belum-download .card-value {
                color: #991b1b !important;
            }

            /* Dark mode overrides */
            .dark .card-sudah-download {
                background-color: rgba(20, 83, 45, 0.25) !important;
                border-color: rgba(20, 83, 45, 0.4) !important;
            }
            .dark .card-sudah-download .card-label {
                color: #4ade80 !important;
            }
            .dark .card-sudah-download .card-value {
                color: #bbf7d0 !important;
            }

            .dark .card-belum-download {
                background-color: rgba(127, 29, 29, 0.25) !important;
                border-color: rgba(127, 29, 29, 0.4) !important;
            }
            .dark .card-belum-download .card-label {
                color: #f87171 !important;
            }
            .dark .card-belum-download .card-value {
                color: #fca5a5 !important;
            }
        </style>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 16px;">
            
            <!-- Sudah Download Card -->
            <div class="card-sudah-download rounded-lg shadow-sm p-4 flex justify-between px-5 items-center">
                <span class="text-xs font-bold uppercase card-label">Sudah Download</span>
                <span class="text-2xl font-black card-value">{{ $totals['total_downloaded'] }}</span>
            </div>

            <!-- Belum Download Card -->
            <div class="card-belum-download rounded-lg shadow-sm p-4 flex justify-between px-5 items-center">
                <span class="text-xs font-bold uppercase card-label">Belum Download</span>
                <span class="text-2xl font-black card-value">{{ $totals['total_not_downloaded'] }}</span>
            </div>

            <!-- AI Card -->
            <div class="rounded-2xl border border-blue-100 bg-blue-500 p-3 dark:border-blue-900/40 dark:bg-blue-950/10 md:p-3 shadow-xs hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    
                    <!-- Left Side: AI Total -->
                    <div>
                        <span class="text-sm text-white dark:text-blue-400 font-semibold uppercase tracking-wider">AI</span>
                        <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                            {{ number_format($totals['total_ai']) }}
                        </h4>
                    </div>

                    <!-- Right Side: Status Breakdown -->
                    <div class="flex flex-col text-xs md:text-sm text-blue-100 dark:text-blue-300 space-y-1 border-l border-blue-400/50 dark:border-blue-800 pl-2">
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium text-white">Sudah</span>
                            <span class="font-bold text-white">{{ number_format($totals['total_ai_downloaded']) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium text-white">Belum</span>
                            <span class="font-bold text-white">{{ number_format($totals['total_ai_not_downloaded']) }}</span>
                        </div>
                    </div>
                    
                </div>
            </div>
            <!-- MT Card -->
            <div class="rounded-2xl border border-blue-100 bg-green-500 p-2 dark:border-blue-900/40 dark:bg-blue-950/10 md:p-2 shadow-xs hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    
                    <!-- Left Side: AI Total -->
                    <div>
                        <span class="text-sm text-white dark:text-blue-400 font-semibold uppercase tracking-wider">MT</span>
                        <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                            {{ number_format($totals['total_mt']) }}
                        </h4>
                    </div>

                    <!-- Right Side: Status Breakdown -->
                    <div class="flex flex-col text-xs md:text-sm text-blue-100 dark:text-blue-300 space-y-1 border-l border-blue-400/50 dark:border-blue-800 pl-2">
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium text-white">Sudah</span>
                            <span class="font-bold text-white">{{ number_format($totals['total_mt_downloaded']) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium text-white">Belum</span>
                            <span class="font-bold text-white">{{ number_format($totals['total_mt_not_downloaded']) }}</span>
                        </div>
                    </div>
                    
                </div>
            </div>
            <!-- SN Card -->
            <div class="rounded-2xl border border-blue-100 bg-[#FE9900] p-3 dark:border-indigo-900/40 dark:bg-indigo-950/10 md:p-3 shadow-xs hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    
                    <!-- Left Side: AI Total -->
                    <div>
                        <span class="text-sm text-white dark:text-indigo-400 font-semibold uppercase tracking-wider">SN</span>
                        <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                            {{ number_format($totals['total_sn']) }}
                        </h4>
                    </div>

                    <!-- Right Side: Status Breakdown -->
                    <div class="flex flex-col text-xs md:text-sm text-indigo-100 dark:text-indigo-300 space-y-1 border-l border-blue-400/50 dark:border-blue-800 pl-2">
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium text-white">Sudah</span>
                            <span class="font-bold text-white">{{ number_format($totals['total_sn_downloaded']) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium text-white">Belum</span>
                            <span class="font-bold text-white">{{ number_format($totals['total_sn_not_downloaded']) }}</span>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden">
        <div class="w-full overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
            <table class="w-full min-w-max border-collapse text-left whitespace-nowrap">
                <thead>
                    <tr class="border-gray-200 border-y dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        @foreach ($columns as $index => $col)
                            <th scope="col" class="px-4 py-3 font-normal text-gray-500 text-start text-theme-sm dark:text-gray-400" wire:key="header_{{ $index }}">
                                @if (!isset($col['sortable']) || $col['sortable'])
                                    @php $isSortAscending = $col['key'] == $sortBy && $sortDirection == 'asc' @endphp
                                    <button
                                        type="button"
                                        wire:click="datatableSort('{{ $col['key'] }}')"
                                        class="inline-flex items-center gap-2 font-medium text-gray-700 transition hover:text-brand-500 dark:text-gray-300 dark:hover:text-brand-400"
                                    >
                                        <span>{{ $col['name'] }}</span>
                                        <span class="flex flex-col leading-none">
                                            <svg
                                                class="h-3 w-3 {{ $isSortAscending ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path d="M10 5l-5 5h10l-5-5z"/>
                                            </svg>
                                            <svg
                                                class="h-3 w-3 {{ ($col['key'] == $sortBy && $sortDirection == 'desc') ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path d="M10 15l5-5H5l5 5z"/>
                                            </svg>
                                        </span>
                                    </button>
                                @else
                                    <span>{{ $col['name'] }}</span>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @if ($data->isEmpty())
                        <tr>
                            <td colspan="{{ count($columns) }}" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-4xl text-gray-400 dark:text-gray-500">search_off</span>
                                    <span>Tidak ada data client ditemukan</span>
                                </div>
                            </td>
                        </tr>
                    @else
                        @foreach ($data as $index => $item)
                            <tr wire:key="row_{{ $index }}" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                @php
                                    $credential = $item->email . " " . $item->password;
                                    $credentialHtml = "<div class='col-auto'>
                                                <button type='button' class='inline-flex items-center justify-center font-medium gap-2 rounded-lg transition px-2 py-1 text-sm bg-success-500 text-white shadow-theme-xs hover:bg-success-600 disabled:bg-brand-300' onclick=\"copyToClipboard('$credential')\">
                                                    <span class='material-symbols-outlined text-lg' data-icon='edit'>vpn_key</span>
                                                </button>
                                            </div>
                                    ";


                                    $html = "<div class='flex flex-nowrap justify-start p-0 m-0 gap-1'>
                                        $credentialHtml
                                    </div>";
                                @endphp     
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{!! $html !!}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $item->no_jp ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">{{ $item->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">
                                    @php
                                    $text = "";
                                        if($item->status != 'downloaded' || $item->status != 'seeded'){
                                             $text = "Halo+Kak+" . strtoupper($item->name) . "+%2C+proses+pencairan+Nenkin+Kakak+sudah+selesai%21+%E2%9C%85%0A%0AKwitansi+dan+bukti+pencairannya+sudah+tersedia+di+dalam+sistem.+Jangan+lupa+untuk+login+ke+%5Bhttps://nenkin.exata-indonesia.id%5D+dan+segera+Download+kwitansinya+untuk+disimpan+sebagai+arsip+pribadi+Kakak+ya.%0A%0ATerima+kasih+sudah+mempercayakan+kepengurusan+Nenkin+Kakak+kepada+Exata+Indonesia%21";   
                                        }
                                    @endphp
                                    <a href="https://api.whatsapp.com/send/?phone={{ App\Helpers\NumberFormatter::formatForWhatsApp($item->current_phone ?? $item->phone) }}&text={{ $text }}&type=phone_number&app_absent=0" target="_BLANK" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition-colors border border-emerald-200/50">💬 {{ $item->current_phone ?? $item->phone  }}</a>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ strtoupper($item->pic) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $item->source_file_name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ $item->client_closing_batch_number ? 'Kloter ' . $item->client_closing_batch_number : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $documentType === 'all' ? 'General (Ichijikin, Resi, Kwitansi)' : ucfirst($item->document_type) }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @php
                                        $statusMeta = match($item->status) {
                                            'downloaded'     => ['label' => 'Downloaded', 'tone' => 'success', 'tip' => 'Klik download nyata secara live-tracked'],
                                            'seeded'         => ['label' => 'Downloaded*', 'tone' => 'amber',  'tip' => 'Data dari seeder/historis (bukan klik live-tracked)'],
                                            default          => ['label' => 'Belum Mendownload', 'tone' => 'red', 'tip' => 'Belum ada aktivitas download tercatat'],
                                        };
                                        $toneClasses = match($statusMeta['tone']) {
                                            'success' => 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400 border border-green-200 dark:border-green-800/30',
                                            'amber' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400 border border-amber-200 dark:border-amber-800/30',
                                            'red' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400 border border-red-200 dark:border-red-800/30',
                                        };
                                    @endphp
                                    <span title="{{ $statusMeta['tip'] }}"
                                          class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold cursor-help {{ $toneClasses }}">
                                        {{ $statusMeta['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ $item->last_downloaded_at ? \Carbon\Carbon::parse($item->last_downloaded_at)->format('Y-m-d H:i:s') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    @php
                                        $client = $item;
                                        $hasAllDocs = false;
                                        if ($client) {
                                            $clientDocs = $client->documents;
                                            $hasIchijikin = $clientDocs->where('type', 'ichijikin')->isNotEmpty();
                                            $hasResi = $clientDocs->where('type', 'resi')->isNotEmpty();
                                            $hasKwitansi = $clientDocs->where('type', 'kwitansi')->isNotEmpty();
                                            $hasAllDocs = $hasIchijikin && $hasResi && $hasKwitansi;
                                        }
                                    @endphp

                                    @if($client)
                                        @if($hasAllDocs)
                                            <a href="{{ route('admin.documents.download-zip', $client->id) }}" 
                                                class="inline-flex items-center text-brand-600 dark:text-brand-400 hover:underline font-semibold"
                                                title="Download ZIP">
                                                Download ZIP
                                                <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </a>
                                        @else
                                            <button class="inline-flex items-center text-gray-400 dark:text-gray-500 font-semibold cursor-not-allowed opacity-50" 
                                                title="Dokumen belum lengkap" 
                                                disabled>
                                                Download ZIP
                                                <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </button>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-800">
        {{ $data->links(data: ['scrollTo' => false]) }}
    </div>

    <!-- Process overlay -->
    <div wire:loading class="fixed inset-0 z-[9999] items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="flex flex-col items-center gap-4 rounded-xl bg-white px-8 py-6 shadow-xl">
            <svg class="h-10 w-10 animate-spin text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <p class="text-sm font-medium text-gray-700">Sedang Memproses ...</p>
        </div>
    </div>
</div>
