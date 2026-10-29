
    <div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <!-- Header -->
        <div class="flex flex-col gap-2 px-5 mb-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div data-slot="card-content" class="group-data-[size=sm]/card:px-3 p-0">
                <x-ui.button size="sm" variant="success" @click="$dispatch('export', { type: '{{ App\Helpers\ExportHelper::TYPE_EXCEL }}' })">Export Excel
                </x-ui.button>
            </div>
        </div>
        
        <div class="row flex justify-start flex-wrap">
            <div class="w-1/3">
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
            <div class="w-1/3">
                <!-- Filter by Batch Dropdown Select -->
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
            
            @if (auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                <div class="w-1/3">
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
            @endif
        </div>

        <!-- Table -->
        <div class="overflow-hidden">
            <div class="w-full overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
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
                                            {!! call_user_func($col['render'], $item) !!}
                                        </td>
                                    @elseif (isset($col['key']))
                                        <td class="px-4 py-2 {!! $cell_class !!}" style="{!! $cell_style !!}">
                                            {{ $item->{$col['key']} }}
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        
        {{ $data->links(data: ['scrollTo' => false]) }}
        <!-- Pagination -->
        {{-- <div class="px-6 py-4 border-t border-gray-200 dark:border-white/[0.05]">
            <div class="flex items-center justify-between">
                <button @click="prevPage" :disabled="currentPage === 1" :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''" class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:px-3.5">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2.58301 9.99868C2.58272 10.1909 2.65588 10.3833 2.80249 10.53L7.79915 15.5301C8.09194 15.8231 8.56682 15.8233 8.85981 15.5305C9.15281 15.2377 9.15297 14.7629 8.86018 14.4699L5.14009 10.7472L16.6675 10.7472C17.0817 10.7472 17.4175 10.4114 17.4175 9.99715C17.4175 9.58294 17.0817 9.24715 16.6675 9.24715L5.14554 9.24715L8.86017 5.53016C9.15297 5.23717 9.15282 4.7623 8.85983 4.4695C8.56684 4.1767 8.09197 4.17685 7.79917 4.46984L2.84167 9.43049C2.68321 9.568 2.58301 9.77087 2.58301 9.99715C2.58301 9.99766 2.58301 9.99817 2.58301 9.99868Z" fill="currentColor"/>
                    </svg>
                    <span class="hidden sm:inline">Previous</span>
                </button>

                <span class="block text-sm font-medium text-gray-700 dark:text-gray-400 sm:hidden">
                    Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                </span>

                <ul class="hidden items-center gap-0.5 sm:flex">
                    <template x-for="page in displayedPages" :key="page">
                        <li>
                            <button x-show="page !== '...'" @click="goToPage(page)" :class="currentPage === page ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-blue-500/[0.08] hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500'" class="flex h-10 w-10 items-center justify-center rounded-lg text-theme-sm font-medium" x-text="page"></button>
                            <span x-show="page === '...'" class="flex h-10 w-10 items-center justify-center text-gray-500">...</span>
                        </li>
                    </template>
                </ul>

                <button @click="nextPage" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''" class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:px-3.5">
                    <span class="hidden sm:inline">Next</span>
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M17.4175 9.9986C17.4178 10.1909 17.3446 10.3832 17.198 10.53L12.2013 15.5301C11.9085 15.8231 11.4337 15.8233 11.1407 15.5305C10.8477 15.2377 10.8475 14.7629 11.1403 14.4699L14.8604 10.7472L3.33301 10.7472C2.91879 10.7472 2.58301 10.4114 2.58301 9.99715C2.58301 9.58294 2.91879 9.24715 3.33301 9.24715L14.8549 9.24715L11.1403 5.53016C10.8475 5.23717 10.8477 4.7623 11.1407 4.4695C11.4336 4.1767 11.9085 4.17685 12.2013 4.46984L17.1588 9.43049C17.3173 9.568 17.4175 9.77087 17.4175 9.99715C17.4175 9.99763 17.4175 9.99812 17.4175 9.9986Z" fill="currentColor"/>
                    </svg>
                </button>
            </div>
        </div> --}}
    </div>
