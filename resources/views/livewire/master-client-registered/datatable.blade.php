
    <div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <!-- Header -->
        <div class="mb-4 flex flex-col gap-4 px-5 sm:flex-row sm:items-center sm:justify-between sm:px-6 w-full">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center justify-between w-full">
                
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

                <form onsubmit="event.preventDefault();" class="m-0">
                    <div class="relative">
                        <button type="button" class="absolute -translate-y-1/2 left-4 top-1/2">
                            <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""/>
                            </svg>
                        </button>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-[42px] pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[300px]"/>
                    </div>
                </form>
            </div>
        </div>

        <div class="row flex justify-start flex-wrap">
            <div class="w-1/4">
                <!-- Filter by Batch Dropdown Select -->
                <div class="px-5 mb-5 sm:px-6">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Batch:</div>
                    
                    <div x-data="{ 
                        open: false, 
                        search: '',
                        batches: @entangle('batches'),
                        get filtered() { 
                            if (!this.search) return this.batches;
                            return this.batches.filter(b => 
                                b.name.toLowerCase().includes(this.search.toLowerCase())
                            )
                        }
                    }" @click.away="open = false" class="relative inline-block text-left w-full">
                        
                        {{-- Dropdown Trigger Button --}}
                        <div class="relative">
                            <button 
                                type="button" 
                                @click="open = !open"
                                class="flex h-[42px] w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <span>
                                    @if($selectedBatch && $selectedBatch !== 'all')
                                        {{ $selectedBatch }}
                                    @else
                                        Pilih Batch...
                                    @endif
                                </span>
                                
                                <div class="flex items-center gap-1.5">
                                    @if($selectedBatch && $selectedBatch !== 'all')
                                        {{-- Tombol X untuk reset --}}
                                        <span 
                                            @click.stop="$wire.selectBatch('all')" 
                                            class="material-symbols-outlined text-gray-400 hover:text-gray-600 dark:hover:text-white text-lg cursor-pointer"
                                        >
                                            close
                                        </span>
                                    @endif
                                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7 10l3 3 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </button>
                        </div>

                        {{-- Dropdown Menu --}}
                        <div 
                            x-show="open" 
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute left-0 mt-2 z-50 w-full rounded-lg bg-white shadow-lg border border-gray-200 dark:bg-gray-900 dark:border-gray-800 py-1"
                            style="display: none;"
                        >
                            {{-- Search Input inside Dropdown --}}
                            <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-800">
                                <input 
                                    x-model="search" 
                                    type="text" 
                                    placeholder="Cari nama batch..." 
                                    class="h-[36px] w-full rounded-md border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white"
                                    @click.stop
                                >
                            </div>

                            {{-- Batches List --}}
                            <div class="max-h-[240px] overflow-y-auto custom-scrollbar py-1">
                                <template x-for="batch in filtered" :key="batch.name">
                                    <button
                                        type="button"
                                        @click="open = false; $wire.selectBatch(batch.name)"
                                        class="flex w-full items-center justify-between px-4 py-2.5 text-xs text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors"
                                    >
                                        <span class="font-medium truncate mr-2" x-text="batch.name"></span>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 whitespace-nowrap" x-text="`(${Number(batch.total).toLocaleString()} klien)`"></span>
                                    </button>
                                </template>

                                <div x-show="filtered.length === 0" class="px-4 py-3 text-xs text-center text-gray-400">
                                    Tidak ada batch ditemukan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-1/4">
                <!-- Filter by Export Status -->
                <div class="px-5 mb-5 sm:px-6">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Status Ekspor</div>
                    
                    <div class="w-full">
                        <select
                            wire:model.live="filter_export_status"
                            class="h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <option value="">Semua Status Ekspor...</option>
                            <option value="exported">EXPORTED</option>
                            <option value="unexported">UN-EXPORTED</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="w-1/4">
                <!-- Filter by Jenis Pencairan -->
                <div class="px-5 mb-5 sm:px-6">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Jenis Pencairan</div>
                    
                    <div class="w-full">
                        <select
                            wire:model.live="filter_claim_type"
                            class="h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <option value="all">Pilih Jenis Pencairan...</option>

                            @foreach(App\Models\Client\Client::CLAIM_TYPE_CHOICE as $value => $name)
                                <option value="{{ $value }}">
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            @can(PermissionHelper::transform(PermissionHelper::ACCESS_FILTER_ALL_PIC, PermissionHelper::TYPE_READ))
                <div class="w-1/4">
                    <div class="px-5 mb-5 sm:px-6">
                        <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Pilih PIC (Sales Agent)</div>
                        
                        <div class="w-full md:w-auto">
                                <select wire:model.live="bulkPic" class="h-[42px] w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="">Select PIC...</option>
                                @foreach(App\Models\User::PIC_CHOICE as $value => $name)
                                    <option value="{{ $value }}">{{ ucfirst($name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endCan
        </div>

        <!-- Table -->
        <div class="overflow-hidden">
            <div class="w-full overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                
                {{-- State 2 — Loading Skeleton (batch baru dipilih, data sedang di-fetch) --}}
                <div wire:loading wire:target="selectBatch" class="w-full">
                    <table class="w-full min-w-max border-collapse text-left whitespace-nowrap">
                        <thead>
                            <tr class="border-gray-200 border-y dark:border-gray-700">
                                @foreach ($columns as $col)
                                    <th scope="col" class="px-4 py-3 font-normal text-gray-500 text-start text-theme-sm dark:text-gray-400">
                                        {{ $col['name'] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @for ($i = 0; $i < 10; $i++)
                                <tr class="animate-pulse bg-white dark:bg-gray-900">
                                    <td class="px-4 py-4.5"><div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-24"></div></td>
                                    <td class="px-4 py-4.5"><div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-44"></div></td>
                                    <td class="px-4 py-4.5"><div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-28"></div></td>
                                    <td class="px-4 py-4.5"><div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-36"></div></td>
                                    <td class="px-4 py-4.5"><div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-32"></div></td>
                                    <td class="px-4 py-4.5"><div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-24"></div></td>
                                    <td class="px-4 py-4.5"><div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-20"></div></td>
                                    <td class="px-4 py-4.5"><div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-24"></div></td>
                                    <td class="px-4 py-4.5"><div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-28"></div></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                {{-- Actual Table Content --}}
                <div wire:loading.remove wire:target="selectBatch" class="w-full">
                    <table class="w-full min-w-max border-collapse text-left whitespace-nowrap">
                        <thead>
                            <tr class="border-gray-200 border-y dark:border-gray-700">
                                @foreach ($columns as $index => $col)
                                    <th scope="col" class="px-4 py-3 font-normal text-gray-500 text-start text-theme-sm dark:text-gray-400" wire:key='datatable_header_{{ $index }}'>
                                        @if (!isset($col['sortable']) || $col['sortable'])
                                            @php $isSortAscending = $col['key'] == $sortBy && $sortDirection == 'asc'@endphp
                                            <button
                                                type="button"
                                                wire:click="datatableSort('{{ $col['key'] }}')"
                                                class="inline-flex items-center gap-2 font-medium text-gray-700 transition hover:text-brand-500 dark:text-gray-300 dark:hover:text-brand-400"
                                            >
                                                <span>
                                                    {{ $col['name'] }}
                                                </span>

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
                                                        class="h-3 w-3 {{ $isSortAscending ? 'text-gray-400' : 'text-gray-900 dark:text-white' }}"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path d="M10 15l5-5H5l5 5z"/>
                                                    </svg>
                                                </span>
                                            </button>
                                        @else
                                            <div class="fs-6 p-2">
                                                {{ $col['name'] }}
                                            </div>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @if ($data->isEmpty())
                                {{-- State 4 — Batch dipilih tapi data kosong --}}
                                <tr>
                                    <td colspan="{{ count($columns) }}" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <span class="material-symbols-outlined text-4xl text-gray-400 dark:text-gray-500">search_off</span>
                                            <span>Tidak ada data klien dalam batch ini</span>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                {{-- State 3 — Data loaded --}}
                                @foreach ($data as $index => $item)
                                    <tr wire:key='datatable_row_{{ $index }}' class="hover:bg-surface-container-low transition-colors group">
                                        @foreach ($columns as $col)
                                            @php
                                                $cell_style = '';
                                                if (isset($col['style'])) {
                                                    $cell_style = is_callable($col['style'])
                                                        ? call_user_func($col['style'], $item, $index)
                                                        : $col['style'];
                                                    $cell_style = "style='{$cell_class}'";
                                                }

                                                $cell_class = '';
                                                if (isset($col['class'])) {
                                                    $cell_class = is_callable($col['class'])
                                                        ? call_user_func($col['class'], $item, $index)
                                                        : $col['class'];
                                                    $cell_class = "class='{$cell_class}'";
                                                }
                                            @endphp
                                            
                                            @if (isset($col['render']) && is_callable($col['render']))
                                                <td class="px-4 py-2 {!! $cell_class !!}" style="{!! $cell_style !!}">
                                                    {!! call_user_func($col['render'], $item, $index) !!}
                                                </td>
                                            @elseif (isset($col['key']))
                                                <td class="px-4 py-2 {!! $cell_class !!}" style="{!! $cell_style !!}">
                                                    {{ $item->{$col['key']} }}
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{ $data->links(data: ['scrollTo' => false]) }}
        <div
            wire:loading
            class="fixed inset-0 z-[9999] items-center justify-center bg-black/50 backdrop-blur-sm"
        >
            <div class="flex flex-col items-center gap-4 rounded-xl bg-white px-8 py-6 shadow-xl">
                <svg
                    class="h-10 w-10 animate-spin text-primary"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>

                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                    ></path>
                </svg>

                <p class="text-sm font-medium text-gray-700">
                    Sedang Memproses ...
                </p>
            </div>
        </div>
        
    </div>

    @push('scripts')
        <script>
            
        Livewire.on('showEsign', (data) => {
            Swal.fire({
                title: "E-Sign",
                icon: "info",
                html: `
                    <img id="preview" src="`+data+`">
                `,
                showCloseButton: true,
                showCancelButton: false,
                focusConfirm: false,    
            });
        });
        </script>
    @endpush
