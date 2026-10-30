@props([
    'placeholder' => 'Pilih kota atau kabupaten...',
    'id' => 'select_regency_' . uniqid(),
])

<div x-data="{
    open: false,
    search: '',
    selected: @entangle($attributes->wire('model')),
    options: [],
    isLoading: false,
    isLoaded: false,

    init() {
        // Deferred loading after initial render for superior first load performance
        if (typeof window.requestIdleCallback === 'function') {
            window.requestIdleCallback(() => this.fetchOptions());
        } else {
            setTimeout(() => this.fetchOptions(), 200);
        }
    },

    fetchOptions() {
        if (this.isLoaded) return;
        this.isLoading = true;
        $wire.loadRegencies().then(data => {
            this.options = Array.isArray(data) ? data : [];
            this.isLoading = false;
            this.isLoaded = true;
        }).catch(err => {
            console.error('Error loading regencies:', err);
            this.isLoading = false;
        });
    },

    get filteredOptions() {
        if (!this.search.trim()) {
            return this.options.slice(0, 100);
        }
        const query = this.search.toLowerCase();
        return this.options.filter(item => item.toLowerCase().includes(query)).slice(0, 100);
    },

    selectOption(val) {
        this.selected = val;
        this.open = false;
        this.search = '';
        this.$nextTick(() => {
            this.$dispatch('change');
            this.$dispatch('blur');
        });
    },

    clearSelection() {
        this.selected = '';
        this.open = false;
        this.search = '';
        this.$nextTick(() => {
            this.$dispatch('change');
            this.$dispatch('blur');
        });
    },

    toggleDropdown() {
        if (!this.isLoaded && !this.isLoading) {
            this.fetchOptions();
        }
        this.open = !this.open;
        if (this.open) {
            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        }
    }
}" 
class="relative w-full"
@click.outside="open = false"
@keydown.escape.window="open = false">

    <!-- Selected Value Display Trigger Box -->
    <div @click="toggleDropdown()"
         role="combobox"
         aria-haspopup="listbox"
         :aria-expanded="open"
         class="w-full flex items-center justify-between pl-10 pr-3.5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium cursor-pointer shadow-2xs hover:border-slate-300 dark:hover:border-slate-600 focus-within:border-[#0049FF] focus-within:ring-4 focus-within:ring-blue-500/10 transition-all">
        
        <!-- Icon -->
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">
            location_on
        </span>

        <!-- Display Text -->
        <span class="truncate block flex-1"
              :class="selected ? 'text-slate-900 dark:text-white font-semibold uppercase' : 'text-slate-400 dark:text-slate-500 font-normal'">
            <span x-text="selected || '{{ $placeholder }}'"></span>
        </span>

        <!-- Action Icons (Clear + Caret) -->
        <div class="flex items-center gap-1 shrink-0 ml-2">
            <!-- Clear Button -->
            <button type="button" 
                    x-show="selected" 
                    @click.stop="clearSelection()"
                    title="Hapus Pilihan"
                    class="p-1 text-slate-400 hover:text-rose-500 rounded-md transition-colors">
                <span class="material-symbols-outlined text-base">close</span>
            </button>

            <!-- Caret Icon -->
            <span class="material-symbols-outlined text-slate-400 transition-transform duration-200"
                  :class="open ? 'rotate-180 text-[#0049FF]' : ''">
                expand_more
            </span>
        </div>
    </div>

    <!-- Hidden Input for standard form fallback & pure JS LocalStorage integration -->
    <input type="hidden" 
           id="{{ $id }}"
           name="{{ $id }}"
           x-ref="hiddenInput"
           :value="selected"
           @input="selected = $event.target.value"
           @change="selected = $event.target.value"
           {{ $attributes->whereDoesntStartWith('wire:model')->except(['id', 'name']) }}>

    <!-- Dropdown Panel (Select2 Searchable Panel) -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1 scale-98"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-98"
         style="display: none;"
         class="absolute left-0 right-0 z-50 mt-1.5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden">
        
        <!-- Search Input Bar -->
        <div class="p-2.5 border-b border-slate-100 dark:border-slate-700/60 bg-slate-50/70 dark:bg-slate-900/50">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">
                    search
                </span>
                <input type="text"
                       x-ref="searchInput"
                       x-model="search"
                       @keydown.enter.prevent="if (filteredOptions.length > 0) selectOption(filteredOptions[0])"
                       placeholder="Cari nama kabupaten/kota..."
                       class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium uppercase outline-none focus:border-[#0049FF] focus:ring-2 focus:ring-blue-500/15 transition-all placeholder:normal-case placeholder:text-slate-400">
            </div>
        </div>

        <!-- Options List -->
        <div class="max-h-56 overflow-y-auto divide-y divide-slate-100/60 dark:divide-slate-700/40 text-xs">
            
            <!-- Loading Indicator -->
            <div x-show="isLoading" class="p-4 text-center text-slate-500 dark:text-slate-400 flex items-center justify-center gap-2">
                <span class="inline-block w-4 h-4 border-2 border-slate-300 border-t-[#0049FF] rounded-full animate-spin"></span>
                <span>Memuat data kabupaten/kota...</span>
            </div>

            <!-- Options Items -->
            <template x-for="item in filteredOptions" :key="item">
                <div @click="selectOption(item)"
                     class="px-4 py-2.5 flex items-center justify-between cursor-pointer transition-colors"
                     :class="selected === item 
                        ? 'bg-blue-50/90 dark:bg-blue-950/60 text-[#0049FF] dark:text-blue-400 font-bold' 
                        : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50'">
                    <span x-text="item" class="uppercase"></span>
                    <span x-show="selected === item" class="material-symbols-outlined text-sm text-[#0049FF]">check</span>
                </div>
            </template>

            <!-- Empty State -->
            <div x-show="!isLoading && isLoaded && filteredOptions.length === 0" 
                 class="p-4 text-center text-slate-400 dark:text-slate-500">
                Kabupaten / Kota tidak ditemukan
            </div>
        </div>

        <!-- Footer Info -->
        <div class="px-3 py-1.5 border-t border-slate-100 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-900/30 text-[10px] text-slate-400 flex items-center justify-between">
            <span>Daftar Kabupaten/Kota Indonesia</span>
            <span x-text="`${options.length} Wilayah`"></span>
        </div>
    </div>
</div>
