<div class="">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        <div class="w-auto">
            <!-- Filter by Batch Dropdown Select -->
            <div class="mb-5">
                <div class="text-md font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Batch:</div>
                
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
                            class="flex h-[42px] w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-lg text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <span>
                                @if($selectedBatch && $selectedBatch !== 'all')
                                    {{ $selectedBatch }}
                                @else
                                    Seluruh Batch...
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
                                class="h-[36px] w-full rounded-md border border-gray-200 bg-gray-50 px-3 text-md text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-white"
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
        <div class="w-auto flex items-end mb-5">
            <livewire:dashboard.create-magic-link />
        </div>
    </div>
    <div class="my-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Detail Data Klien</h1>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        <div class="rounded-2xl border border-blue-100 bg-blue-500 p-5 dark:border-blue-900/40 dark:bg-blue-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                
                <!-- Left Side: Main Total -->
                <div>
                    <span class="text-sm text-white dark:text-blue-400 font-semibold uppercase tracking-wider">Total Klien</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['total_clients']) }}
                    </h4>
                </div>

                <!-- Right Side: PIC Breakdown -->
                <div class="flex flex-col text-xs md:text-sm text-blue-100 dark:text-blue-300 space-y-1 border-l border-blue-400/50 dark:border-blue-800 pl-4">
                    <div class="flex items-center justify-between gap-4">
                        <span class="font-medium text-white">AI</span>
                        <span class="font-bold text-white">{{ number_format($total_achievements['total_clients_ainul']) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="font-medium text-white">MT</span>
                        <span class="font-bold text-white">{{ number_format($total_achievements['total_clients_kim']) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="font-medium text-white">SN</span>
                        <span class="font-bold text-white">{{ number_format($total_achievements['total_clients_slamet']) }}</span>
                    </div>
                </div>
                
            </div>
        </div>
        <!-- KOKUMIN -->
        <div class="rounded-2xl border border-cyan-100 bg-[#FF00FE] p-5 dark:border-cyan-900/40 dark:bg-cyan-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-cyan-400 font-semibold uppercase tracking-wider">KOKUMIN</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['kokumin_total']) }}
                    </h4>
                </div>
                <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($total_achievements['kokumin_ainul']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($total_achievements['kokumin_kim']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($total_achievements['kokumin_slamet']) }}</span></div>
                </div>
            </div>
        </div>

        <!-- UNREGISTERED -->
        <div class="rounded-2xl border border-rose-100 bg-red-500 p-5 dark:border-rose-900/40 dark:bg-rose-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-white font-semibold uppercase tracking-wider">UNREGISTERED</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['total_clients'] - $total_achievements['registered_total'] - $total_achievements['kokumin_total']) }}
                    </h4>
                </div>
                <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($total_achievements['total_clients_ainul'] - $total_achievements['registered_ainul'] - $total_achievements['kokumin_ainul']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($total_achievements['total_clients_kim'] - $total_achievements['registered_kim'] - $total_achievements['kokumin_kim']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($total_achievements['total_clients_slamet'] - $total_achievements['registered_slamet'] - $total_achievements['kokumin_slamet']) }}</span></div>
                </div>
            </div>
        </div>

        <!-- REGISTERED -->
        <div class="rounded-2xl border border-rose-100 bg-green-500 p-5 dark:border-rose-900/40 dark:bg-rose-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-rose-400 font-semibold uppercase tracking-wider">REGISTERED</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['registered_total']) }}
                    </h4>
                </div>
                <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($total_achievements['registered_ainul']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($total_achievements['registered_kim']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($total_achievements['registered_slamet']) }}</span></div>
                </div>
            </div>
        </div>

        <!-- UNCONFIRMED -->
        <div class="rounded-2xl border border-rose-100 bg-red-500 p-5 dark:border-rose-900/40 dark:bg-rose-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-rose-400 font-semibold uppercase tracking-wider">UNCONFIRMED</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['not_confirmed_total']) }}
                    </h4>
                </div>
                <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($total_achievements['not_confirmed_ainul']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($total_achievements['not_confirmed_kim']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($total_achievements['not_confirmed_slamet']) }}</span></div>
                </div>
            </div>
        </div>

        <!-- CONFIRMED -->
        <div class="rounded-2xl border border-emerald-100 bg-green-500 p-5 dark:border-emerald-900/40 dark:bg-emerald-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-emerald-400 font-semibold uppercase tracking-wider">CONFIRMED</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['registered_total'] - $total_achievements['not_confirmed_total']) }}
                    </h4>
                </div>
                <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($total_achievements['registered_ainul'] - $total_achievements['not_confirmed_ainul']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($total_achievements['registered_kim'] - $total_achievements['not_confirmed_kim']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($total_achievements['registered_slamet'] - $total_achievements['not_confirmed_slamet']) }}</span></div>
                </div>
            </div>
        </div>

        <!-- PENDING -->
        <div class="rounded-2xl border border-rose-100 bg-red-500 p-5 dark:border-rose-900/40 dark:bg-rose-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-rose-400 font-semibold uppercase tracking-wider">PENDING</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['pending_total']) }}
                    </h4>
                </div>
                <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($total_achievements['pending_ainul']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($total_achievements['pending_kim']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($total_achievements['pending_slamet']) }}</span></div>
                </div>
            </div>
        </div>

        <!-- E-SIGNED -->
        <div class="rounded-2xl border border-rose-100 bg-green-500 p-5 dark:border-rose-900/40 dark:bg-rose-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-rose-400 font-semibold uppercase tracking-wider">E-Signed</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['speed_total'] + $total_achievements['adjustment_total']) }}
                    </h4>
                </div>
                <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($total_achievements['speed_ainul'] + $total_achievements['adjustment_ainul']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($total_achievements['speed_kim'] + $total_achievements['adjustment_kim']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($total_achievements['speed_slamet'] + $total_achievements['adjustment_slamet']) }}</span></div>
                </div>
            </div>
        </div>
    </div>
    <hr class="mt-3 border border-2">

    <!-- SECTION DIVIDER -->
    <div class="my-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Detail Sudah Konfirmasi</h1>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        <!-- JPN -->
        <div class="rounded-2xl border border-indigo-100 bg-[#FE9900] p-5 dark:border-indigo-900/40 dark:bg-indigo-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-indigo-400 font-semibold uppercase tracking-wider">JPN</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['jpn_total']) }}
                    </h4>
                </div>
                <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($total_achievements['jpn_ainul']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($total_achievements['jpn_kim']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($total_achievements['jpn_slamet']) }}</span></div>
                </div>
            </div>
        </div>

        <!-- RE - ENTRY -->
        <div class="rounded-2xl border border-amber-100 bg-[#FE0000] p-5 dark:border-amber-900/40 dark:bg-amber-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-amber-400 font-semibold uppercase tracking-wider">RE - ENTRY</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['re_total']) }}
                    </h4>
                </div>
                <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($total_achievements['re_ainul']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($total_achievements['re_kim']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($total_achievements['re_slamet']) }}</span></div>
                </div>
            </div>
        </div>

        <!-- SPEED -->
        <div class="rounded-2xl border border-violet-100 bg-[#00FF01] p-5 dark:border-violet-900/40 dark:bg-violet-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-violet-400 font-semibold uppercase tracking-wider">SPEED</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['speed_total']) }}
                    </h4>
                </div>
                <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($total_achievements['speed_ainul']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($total_achievements['speed_kim']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($total_achievements['speed_slamet']) }}</span></div>
                </div>
            </div>
        </div>

        <!-- ADJUSTMENT -->
        <div class="rounded-2xl border border-fuchsia-100 bg-[#FFDF20] p-5 dark:border-fuchsia-900/40 dark:bg-fuchsia-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-fuchsia-400 font-semibold uppercase tracking-wider">ADJUSTMENT</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['adjustment_total']) }}
                    </h4>
                </div>
                <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($total_achievements['adjustment_ainul']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($total_achievements['adjustment_kim']) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($total_achievements['adjustment_slamet']) }}</span></div>
                </div>
            </div>
        </div>
    </div>
    <hr class="mt-3 border border-2">

    @foreach ($batch_achievements as $index => $item)
        <!-- SECTION DIVIDER -->
        <div class="my-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                @if(empty($item['client_closing_batch_number']))
                    Data Belum Closing
                @else
                    Data Closing {{ $item['client_closing_batch_number'] }}
                @endif
            </h1>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
            <!-- JPN -->
            <div class="rounded-2xl border border-indigo-100 bg-[#FE9900] p-5 dark:border-indigo-900/40 dark:bg-indigo-950/10 md:p-6 shadow-xs hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-white dark:text-indigo-400 font-semibold uppercase tracking-wider">JPN</span>
                        <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                            {{ number_format($item['jpn_total']) }}
                        </h4>
                    </div>
                    <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                        <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($item['jpn_ainul']) }}</span></div>
                        <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($item['jpn_kim']) }}</span></div>
                        <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($item['jpn_slamet']) }}</span></div>
                    </div>
                </div>
            </div>

            <!-- RE - ENTRY -->
            <div class="rounded-2xl border border-amber-100 bg-[#FE0000] p-5 dark:border-amber-900/40 dark:bg-amber-950/10 md:p-6 shadow-xs hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-white dark:text-amber-400 font-semibold uppercase tracking-wider">RE - ENTRY</span>
                        <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                            {{ number_format($item['re_total']) }}
                        </h4>
                    </div>
                    <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                        <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($item['re_ainul']) }}</span></div>
                        <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($item['re_kim']) }}</span></div>
                        <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($item['re_slamet']) }}</span></div>
                    </div>
                </div>
            </div>

            <!-- SPEED -->
            <div class="rounded-2xl border border-violet-100 bg-[#00FF01] p-5 dark:border-violet-900/40 dark:bg-violet-950/10 md:p-6 shadow-xs hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-white dark:text-violet-400 font-semibold uppercase tracking-wider">SPEED</span>
                        <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                            {{ number_format($item['speed_total']) }}
                        </h4>
                    </div>
                    <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                        <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($item['speed_ainul']) }}</span></div>
                        <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($item['speed_kim']) }}</span></div>
                        <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($item['speed_slamet']) }}</span></div>
                    </div>
                </div>
            </div>

            <!-- ADJUSTMENT -->
            <div class="rounded-2xl border border-fuchsia-100 bg-[#FFDF20] p-5 dark:border-fuchsia-900/40 dark:bg-fuchsia-950/10 md:p-6 shadow-xs hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm text-white dark:text-fuchsia-400 font-semibold uppercase tracking-wider">ADJUSTMENT</span>
                        <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                            {{ number_format($item['adjustment_total']) }}
                        </h4>
                    </div>
                    <div class="flex flex-col text-xs md:text-sm text-white/90 space-y-1 border-l border-white/30 pl-4">
                        <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">AI</span><span class="font-bold text-white">{{ number_format($item['adjustment_ainul']) }}</span></div>
                        <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">MT</span><span class="font-bold text-white">{{ number_format($item['adjustment_kim']) }}</span></div>
                        <div class="flex items-center justify-between gap-4"><span class="font-medium text-white">SN</span><span class="font-bold text-white">{{ number_format($item['adjustment_slamet']) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <hr class="mt-3 border border-2">
    @endforeach

{{-- 
        <div class="rounded-2xl border border-cyan-100 bg-[#FF00FE] p-5 dark:border-cyan-900/40 dark:bg-cyan-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-cyan-400 font-semibold uppercase tracking-wider">KOKUMIN</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['kokumin_total']) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-rose-100 bg-red-500 p-5 dark:border-rose-900/40 dark:bg-rose-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-white font-semibold uppercase tracking-wider"> UNREGISTERED</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['total_clients'] - $total_achievements['registered_total'] - $total_achievements['kokumin_total']) }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-green-500 p-5 dark:border-rose-900/40 dark:bg-rose-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-rose-400 font-semibold uppercase tracking-wider">REGISTERED</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['registered_total']) }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-red-500 p-5 dark:border-rose-900/40 dark:bg-rose-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-rose-400 font-semibold uppercase tracking-wider">UNCONFIRMED</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['not_confirmed_total']) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-green-500 p-5 dark:border-emerald-900/40 dark:bg-emerald-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-emerald-400 font-semibold uppercase tracking-wider">CONFIRMED</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['registered_total'] - $total_achievements['not_confirmed_total']) }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-red-500 p-5 dark:border-rose-900/40 dark:bg-rose-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-rose-400 font-semibold uppercase tracking-wider">PENDING</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['pending_total']) }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-green-500 p-5 dark:border-rose-900/40 dark:bg-rose-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-rose-400 font-semibold uppercase tracking-wider">E-Signed</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['speed_total'] + $total_achievements['adjustment_total']) }}
                    </h4>
                </div>
            </div>
        </div>

    </div>

    <div class="my-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Detail Sudah Konfirmasi</h1>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        
        <div class="rounded-2xl border border-indigo-100 bg-[#FE9900] p-5 dark:border-indigo-900/40 dark:bg-indigo-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-indigo-400 font-semibold uppercase tracking-wider">JPN</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['jpn_total']) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-[#FE0000] p-5 dark:border-amber-900/40 dark:bg-amber-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-amber-400 font-semibold uppercase tracking-wider">RE - ENTRY</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['re_total']) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-violet-100 bg-[#00FF01] p-5 dark:border-violet-900/40 dark:bg-violet-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-violet-400 font-semibold uppercase tracking-wider">SPEED</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['speed_total']) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-fuchsia-100 bg-[#FFDF20] p-5 dark:border-fuchsia-900/40 dark:bg-fuchsia-950/10 md:p-6 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm text-white dark:text-fuchsia-400 font-semibold uppercase tracking-wider">ADJUSTMENT</span>
                    <h4 class="mt-2 font-bold text-white text-title-sm dark:text-white">
                        {{ number_format($total_achievements['adjustment_total']) }}
                    </h4>
                </div>
            </div>
        </div> --}}
    <div class="my-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Detail Data PIC Registrasi</h1>
    </div>
    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="border border-stroke border-gray-400 dark:border-strokedark text-center min-w-[150px] px-6 py-4.5 font-bold uppercase text-black dark:text-white text-base tracking-wider">
                            PIC
                        </th>
                        <th class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-4.5 font-bold uppercase text-black dark:text-white text-base tracking-wider hidden sm:table-cell">
                            Belum Buat Akun
                        </th>
                        <th class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-4.5 font-bold uppercase text-black dark:text-white text-base tracking-wider hidden sm:table-cell">
                            Sudah Buat Akun
                        </th>
                        <th class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-4.5 font-bold uppercase text-black dark:text-white text-base tracking-wider hidden sm:table-cell">
                            KOKUMIN
                        </th>
                        <th class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-4.5 font-extrabold uppercase text-primary dark:text-white text-lg tracking-wider">
                            Total
                        </th>
                    </tr>
                </thead>

                @php
                    $total_pic_not_registered = 0;
                    $total_pic_kokumin = 0;
                    $total_pic_total = 0;
                @endphp
                
                <tbody class="bg-white dark:bg-boxdark">
                    @foreach ($pic_achievements as $item)
                    @php
                        $total_pic_not_registered += $item['not_registered'];
                        $total_pic_kokumin += $item['kokumin'];
                        $total_pic_total += $item['total_client'];
                    @endphp
                        <tr class="bg-gradient-to-r from-orange-50/30 to-transparent dark:from-meta-4/20 transition hover:bg-gray-50 dark:hover:bg-meta-4/40">
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5">
                                <div class="flex flex-col gap-1">
                                    <h3 class="text-xl font-bold text-black dark:text-white">
                                        {{ strtoupper($item['pic']) }}
                                    </h3>
                                </div>
                            </td>

                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl font-semibold text-red-500 dark:text-white hidden sm:table-cell">
                                <a
                                    href="{{ route('master_client.index', [
                                        'bulkPic' => $item['pic'],
                                        'filter_data_type' => App\Models\Client\Client::FILTER_DATA_TYPE_NOT_REGISTERED,
                                        'selectedBatch' => $selectedBatch
                                    ]) }}"
                                    target="_blank"
                                    class="hover:underline hover:text-red-600"
                                >
                                    {{ number_format($item['not_registered']) }}
                                </a>
                            </td>

                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl font-semibold text-green-500 dark:text-white hidden sm:table-cell">
                                {{ number_format($item['total_client'] - $item['kokumin'] - $item['not_registered']) }}
                            </td>

                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl text-[#FF00FE] font-semibold text-meta-1 hidden sm:table-cell">
                                {{ number_format($item['kokumin']) }}
                            </td>

                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-3xl font-black text-meta-3 tracking-tight">
                                {{ number_format($item['total_client']) }}
                            </td>
                        </tr>
                    @endforeach
                        <tr class="bg-gradient-to-r from-slate-100 to-slate-50 dark:from-slate-800 dark:to-slate-900">
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5">
                                <div class="flex flex-col gap-1">
                                    <h3 class="text-xl font-bold text-black dark:text-white">
                                        TOTAL
                                    </h3>
                                </div>
                            </td>

                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl font-semibold text-red-500 dark:text-white hidden sm:table-cell">
                                {{ number_format($total_pic_not_registered) }}
                            </td>

                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl font-semibold text-green-500 dark:text-white hidden sm:table-cell">
                                {{ number_format($total_pic_total - $total_pic_kokumin - $total_pic_not_registered) }}
                            </td>

                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl text-[#FF00FE] font-semibold text-meta-1 hidden sm:table-cell">
                                {{ number_format($total_pic_kokumin) }}
                            </td>

                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-3xl font-black text-meta-3 tracking-tight">
                                {{ number_format($total_pic_total) }}
                            </td>
                        </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="my-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Detail Data PIC Konfirmasi</h1>
    </div>
    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="border border-stroke border-gray-400 dark:border-strokedark text-center min-w-[150px] px-6 py-4.5 font-bold uppercase text-black dark:text-white text-base tracking-wider">
                            PIC
                        </th>
                        <th class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-4.5 font-bold uppercase text-black dark:text-white text-base tracking-wider hidden sm:table-cell">
                            Belum Konfirmasi
                        </th>
                        <th class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-4.5 font-bold uppercase text-black dark:text-white text-base tracking-wider hidden sm:table-cell">
                            Sudah Konfirmasi
                        </th>
                        <th class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-4.5 font-bold uppercase text-black dark:text-white text-base tracking-wider hidden sm:table-cell">
                            Total Registered
                        </th>
                    </tr>
                </thead>

                @php
                    $total_pic_not_confirmed = 0;
                    $total_pic_total = 0;
                @endphp
                
                <tbody class="bg-white dark:bg-boxdark">
                    @foreach ($pic_achievements as $item)
                    @php
                        $total_pic_not_confirmed += $item['not_confirmed'];
                        $total_pic_total += $item['total_registered'];
                    @endphp
                        <tr class="bg-gradient-to-r from-orange-50/30 to-transparent dark:from-meta-4/20 transition hover:bg-gray-50 dark:hover:bg-meta-4/40">
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5">
                                <div class="flex flex-col gap-1">
                                    <h3 class="text-xl font-bold text-black dark:text-white">
                                        {{ strtoupper($item['pic']) }}
                                    </h3>
                                </div>
                            </td>
                            
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl font-semibold text-red-500 dark:text-white hidden sm:table-cell">
                                 <a
                                    href="{{ route('master_client.index', [
                                        'bulkPic' => $item['pic'],
                                        'filter_data_type' =>  App\Models\Client\Client::FILTER_DATA_TYPE_NOT_CONFIRMATION,
                                        'selectedBatch' => $selectedBatch
                                    ]) }}"
                                    target="_BLANK"
                                    class="hover:underline hover:text-red-600"
                                >
                                    {{ number_format($item['not_confirmed']) }}
                                 </a>
                            </td>
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl font-semibold text-green-500 dark:text-white hidden sm:table-cell">
                                {{ number_format($item['total_registered'] - $item['not_confirmed']) }}
                            </td>
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-3xl font-black text-meta-3 tracking-tight">
                                {{ number_format($item['total_registered']) }}
                            </td>
                            
                        </tr>
                    @endforeach
                        <tr class="bg-gradient-to-r from-slate-100 to-slate-50 dark:from-slate-800 dark:to-slate-900">
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5">
                                <div class="flex flex-col gap-1">
                                    <h3 class="text-xl font-bold text-black dark:text-white">
                                        TOTAL
                                    </h3>
                                </div>
                            </td>
                            
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl text-red-500 font-semibold text-meta-1 hidden sm:table-cell">
                                {{ number_format($total_pic_not_confirmed) }}
                            </td>
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl text-green-500 font-semibold text-meta-1 hidden sm:table-cell">
                                {{ number_format($total_pic_total - $total_pic_not_confirmed) }}
                            </td>
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-3xl font-black text-meta-3 tracking-tight">
                                {{ number_format($total_pic_total) }}
                            </td>
                            
                        </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="my-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Detail Data PIC Pending E-Sign</h1>
    </div>
    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="border border-stroke border-gray-400 dark:border-strokedark text-center min-w-[150px] px-6 py-4.5 font-bold uppercase text-black dark:text-white text-base tracking-wider">
                            PIC
                        </th>
                        <th class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-4.5 font-bold uppercase text-black dark:text-white text-base tracking-wider hidden sm:table-cell">
                            Belum E-Sign
                        </th>
                        <th class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-4.5 font-bold uppercase text-black dark:text-white text-base tracking-wider hidden sm:table-cell">
                            Sudah E-Sign
                        </th>
                        <th class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-4.5 font-bold uppercase text-black dark:text-white text-base tracking-wider hidden sm:table-cell">
                            Total Speed / Adjusment
                        </th>
                    </tr>
                </thead>

                @php
                    $total_pic_pending = 0;
                    $total_pic_total_speed = 0;
                @endphp
                
                <tbody class="bg-white dark:bg-boxdark">
                    @foreach ($pic_achievements as $item)
                    @php
                        $total_pic_pending += $item['pending'];
                        $total_pic_total_speed += $item['total_speed'];
                    @endphp
                        <tr class="bg-gradient-to-r from-orange-50/30 to-transparent dark:from-meta-4/20 transition hover:bg-gray-50 dark:hover:bg-meta-4/40">
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5">
                                <div class="flex flex-col gap-1">
                                    <h3 class="text-xl font-bold text-black dark:text-white">
                                        {{ strtoupper($item['pic']) }}
                                    </h3>
                                </div>
                            </td>
                            
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl font-semibold text-red-500 dark:text-white hidden sm:table-cell">
                                 <a
                                    href="{{ route('master_client_registered.index', [
                                        'bulkPic' => $item['pic'],
                                        'filter_claim_type' =>  App\Models\Client\Client::CLAIM_TYPE_PENDING,
                                        'selectedBatch' => $selectedBatch
                                    ]) }}"
                                    target="_BLANK"
                                    class="hover:underline hover:text-red-600"
                                >
                                    {{ number_format($item['pending']) }}
                                 </a>
                            </td>
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl font-semibold text-green-500 dark:text-white hidden sm:table-cell">
                                {{ number_format($item['total_speed'] - $item['pending']) }}
                            </td>
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-3xl font-black text-meta-3 tracking-tight">
                                {{ number_format($item['total_speed']) }}
                            </td>
                            
                        </tr>
                    @endforeach
                        <tr class="bg-gradient-to-r from-slate-100 to-slate-50 dark:from-slate-800 dark:to-slate-900">
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5">
                                <div class="flex flex-col gap-1">
                                    <h3 class="text-xl font-bold text-black dark:text-white">
                                        TOTAL
                                    </h3>
                                </div>
                            </td>
                            
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl text-red-500 font-semibold text-meta-1 hidden sm:table-cell">
                                {{ number_format($total_pic_pending) }}
                            </td>
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-2xl text-green-500 font-semibold text-meta-1 hidden sm:table-cell">
                                {{ number_format($total_pic_total_speed - $total_pic_pending) }}
                            </td>
                            <td class="border border-stroke border-gray-400 dark:border-strokedark text-center px-6 py-5 text-3xl font-black text-meta-3 tracking-tight">
                                {{ number_format($total_pic_total_speed) }}
                            </td>
                        </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>