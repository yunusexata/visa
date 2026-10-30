<div class="rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
    <!-- Header Controls: Replikasi dari livewire-datatable.blade.php -->
    <div class="mb-4 flex flex-col gap-4 px-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            
            <div class="flex items-center gap-2">
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

    <!-- Sticky Datatable Flux UI dengan Alpine.js Infinite Scroll -->
    <div 
        x-data="{
            isLoading: false,
            hasMore: @js($data->hasMorePages()),
            observer: null,
            init() {
                this.$nextTick(() => {
                    this.setupObserver();
                });
            },
            setupObserver() {
                if (this.observer) {
                    this.observer.disconnect();
                }

                const container = this.$el.querySelector('.overflow-auto') || this.$el.querySelector('[data-flux-table]') || this.$el;
                const sentinel = this.$refs.infiniteSentinel;

                if (!sentinel) return;

                this.observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && !this.isLoading && this.hasMore) {
                            this.triggerLoad();
                        }
                    });
                }, {
                    root: container !== this.$el ? container : null,
                    rootMargin: '120px'
                });

                this.observer.observe(sentinel);
            },
            triggerLoad() {
                if (this.isLoading || !this.hasMore) return;
                this.isLoading = true;
                $wire.loadMore().then(() => {
                    this.isLoading = false;
                    this.$nextTick(() => {
                        this.setupObserver();
                    });
                }).catch(() => {
                    this.isLoading = false;
                });
            },
            onScroll(e) {
                if (this.isLoading || !this.hasMore) return;
                const el = e.target;
                if (el.scrollHeight - el.scrollTop - el.clientHeight < 150) {
                    this.triggerLoad();
                }
            },
            destroy() {
                if (this.observer) {
                    this.observer.disconnect();
                }
            }
        }"
        x-effect="hasMore = @js($data->hasMorePages())"
        @scroll.passive.debounce.150ms="onScroll($event)"
        class="relative overflow-hidden"
    >
        <flux:table container:class="max-h-[600px] overflow-auto border-t border-b border-gray-200 dark:border-zinc-800">
            <flux:table.columns sticky class="bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-800 z-10">
                <flux:table.column sticky class="bg-white dark:bg-zinc-900 z-20 cursor-pointer" wire:click="datatableSort('nomor_registrasi')">
                    <div class="flex items-center gap-1.5 font-semibold justify-center mx-auto text-gray-700 dark:text-gray-300 text-xs">
                        <span class="text-center">ID</span>
                        @if ($sortBy === 'nomor_registrasi')
                            <span class="text-blue-500 font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </div>
                </flux:table.column>

                <flux:table.column class="cursor-pointer" wire:click="datatableSort('nama_lengkap')">
                    <div class="flex items-center gap-1.5 font-semibold justify-center mx-auto text-gray-700 dark:text-gray-300 text-xs">
                        <span class="text-center">NAMA</span>
                        @if ($sortBy === 'nama_lengkap')
                            <span class="text-blue-500 font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </div>
                </flux:table.column>

                <flux:table.column>KAISHA</flux:table.column>
                <flux:table.column>PASPOR / COE</flux:table.column>

                <flux:table.column class="cursor-pointer" wire:click="datatableSort('tanggal_kedatangan')">
                    <div class="flex items-center gap-1.5 font-semibold text-gray-700 dark:text-gray-300 text-xs">
                        <span>TGL BERANGKAT</span>
                        @if ($sortBy === 'tanggal_kedatangan')
                            <span class="text-blue-500 font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </div>
                </flux:table.column>

                <flux:table.column>LAMPIRAN</flux:table.column>

                <flux:table.column class="cursor-pointer" wire:click="datatableSort('status')">
                    <div class="flex items-center gap-1.5 font-semibold text-gray-700 dark:text-gray-300 text-xs">
                        <span>STATUS</span>
                        @if ($sortBy === 'status')
                            <span class="text-blue-500 font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </div>
                </flux:table.column>

                <flux:table.column class="text-center">AKSI</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($data as $item)
                    @php
                        $formHashid = \Vinkla\Hashids\Facades\Hashids::encode($item->id);
                        $statusEnum = $item->status instanceof \App\Enums\Form\VisaFormStatus
                            ? $item->status
                            : \App\Enums\Form\VisaFormStatus::fromLabel((string) $item->status);
                        $badgeColor = match($statusEnum) {
                            \App\Enums\Form\VisaFormStatus::DRAFT => 'zinc',
                            \App\Enums\Form\VisaFormStatus::DIAJUKAN => 'blue',
                            \App\Enums\Form\VisaFormStatus::SIAP_VERIFIKASI => 'amber',
                            \App\Enums\Form\VisaFormStatus::VERIFIKASI, \App\Enums\Form\VisaFormStatus::SELESAI => 'emerald',
                            \App\Enums\Form\VisaFormStatus::DITOLAK => 'red',
                            default => 'zinc',
                        };
                    @endphp
                    <flux:table.row :key="$item->id">
                        <!-- Sticky ID / Nomor Registrasi Column -->
                        <flux:table.cell sticky class="bg-white dark:bg-zinc-900 z-10 font-medium text-gray-900 dark:text-white">
                            <div class="font-mono text-xs font-semibold text-brand-600 dark:text-brand-400 text-center">
                                {{ $item->nomor_registrasi }}
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="font-semibold text-gray-900 dark:text-white">
                            <div class="text-sm font-medium">{{ $item->nama_lengkap }}</div>
                            @if ($item->pic_code)
                                <div class="text-[11px] text-gray-400 dark:text-gray-500 font-normal">PIC: {{ $item->pic_code }}</div>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-gray-600 dark:text-zinc-400">
                            <div class="max-w-[170px] truncate block text-sm" title="{{ $item->nama_kaisha }}">
                                {{ $item->nama_kaisha ?: '-' }}
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="text-gray-600 dark:text-zinc-400 font-mono text-xs">
                            <div>{{ $item->nomor_paspor ?: '-' }}</div>
                            @if ($item->nomor_coe)
                                <div class="text-[11px] text-gray-400">{{ $item->nomor_coe }}</div>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-gray-500 dark:text-zinc-400 text-sm text-center">
                            {{ $item->tanggal_kedatangan ? $item->tanggal_kedatangan->format('d/m/Y') : '-' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <a href="{{ route('visa_form.attachments', ['id' => $item->id]) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               title="Lihat & Kelola Berkas di Tab Baru"
                               class="inline-block transition hover:opacity-80">
                                <flux:badge size="sm" color="blue" class="font-mono cursor-pointer">
                                    {{ $item->attachments_count ?? 0 }} berkas
                                </flux:badge>
                            </a>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge variant="pill" size="sm" color="{{ $badgeColor }}">
                                {{ $statusEnum?->label() ?? (string) $item->status }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @if ($isCanUpdate)
                                    <button type="button"
                                            wire:click="$dispatch('open-visa-form-detail', { id: {{ $item->id }} })"
                                            title="Edit Formulir"
                                            class="inline-flex items-center justify-center p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/50 transition cursor-pointer">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                @endif

                                <a href="{{ route('visa_form.attachments', ['id' => $item->id]) }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   title="Kelola Berkas Lampiran (Tab Baru)"
                                   class="inline-flex items-center justify-center p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/50 transition cursor-pointer">
                                    <span class="material-symbols-outlined text-lg">folder_open</span>
                                </a>

                                <button type="button"
                                        wire:click="generateVisaJapanPdf({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        title="Download Form Visa Jepang (PDF)"
                                        class="inline-flex items-center justify-center p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/50 transition cursor-pointer">
                                    <span class="material-symbols-outlined text-lg">picture_as_pdf</span>
                                </button>

                                @if ($item->status === \App\Enums\Form\VisaFormStatus::DRAFT)
                                    <button type="button" onclick="copyToClipboard('{{ route('form.upload-document', ['hashid' => $formHashid]) }}')"
                                       target="_blank"
                                       title="Unggah Dokumen"
                                       class="inline-flex items-center justify-center p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/50 transition">
                                        <span class="material-symbols-outlined text-lg">cloud_upload</span>
                                    </button>
                                @endif

                                @if ($isCanDelete)
                                    <button type="button"
                                            wire:click="showDeleteDialog({{ $item->id }})"
                                            title="Hapus"
                                            class="inline-flex items-center justify-center p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/50 transition cursor-pointer">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center py-12 text-gray-400 dark:text-gray-500">
                            Tidak ada data permohonan visa yang ditemukan.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse

                <!-- Sentinel Element Row untuk Infinite Scroll Alpine.js -->
                @if ($data->hasMorePages())
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="p-0 border-0">
                            <div x-ref="infiniteSentinel" class="h-4 w-full"></div>
                        </flux:table.cell>
                    </flux:table.row>
                @endif
            </flux:table.rows>
        </flux:table>
    </div>

    <!-- Footer Status & Infinite Scroll Loading Indicator -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400">
        <div>
            Menampilkan <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $data->count() }}</span> dari <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $data->total() }}</span> total data
        </div>

        <div wire:loading wire:target="loadMore" class="flex items-center gap-2 text-primary dark:text-brand-400 font-medium">
            <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>Memuat 50 data berikutnya...</span>
        </div>

        @if (!$data->hasMorePages() && $data->total() > 0)
            <div class="text-gray-400 dark:text-gray-500 italic">
                Semua data telah ditampilkan
            </div>
        @endif
    </div>

    <!-- Livewire Modal Detail & Edit VisaForm -->
    <livewire:form.visa-form-detail-modal :key="'visa-form-detail-modal'" />
</div>

