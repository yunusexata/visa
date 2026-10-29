<x-ui.modal x-data="{ open: @entangle('isOpen') }" modalId="batchDetailModal" class="max-w-[95vw] p-8" wire:ignore.self>
    <div class="relative w-full bg-white dark:bg-gray-900 overflow-y-scroll max-h-[90vh]">
        {{-- Blocking loading overlay --}}
        <div wire:loading wire:target="search, claimType, status, previousPage, nextPage, gotoPage" class="absolute inset-0 bg-white/70 dark:bg-gray-900/70 z-50 flex items-center justify-center backdrop-blur-[2px]">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 flex flex-col items-center gap-3">
                <svg class="animate-spin h-10 w-8 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-800 dark:text-white">Memuat Data...</span>
            </div>
        </div>

        {{-- Header modal --}}
        <div class="flex items-center justify-between border-b pb-4 mb-6">
            <h4 class="text-xl font-bold text-gray-800 dark:text-white">
                {{ $batchName }} — <span class="text-brand-500">{{ number_format($totalCount) }} Klien</span>
            </h4>
            <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill="currentColor"/>
                </svg>
            </button>
        </div>

        {{-- Filter & Search inside modal --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            {{-- Search input --}}
            <div>
                <label class="mb-2 block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cari Nama</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    placeholder="Cari berdasarkan nama klien..."
                >
            </div>

            {{-- Filter Claim Type --}}
            <div>
                <label class="mb-2 block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Claim Type</label>
                <select
                    wire:model.live="claimType"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                    <option value="">Semua</option>
                    <option value="speed">Speed</option>
                    <option value="adjusment">Adjustment</option>
                    <option value="normal">Normal</option>
                </select>
            </div>

            {{-- Filter Status --}}
            <div>
                <label class="mb-2 block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</label>
                <select
                    wire:model.live="status"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
                    <option value="">Semua</option>
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="complete">Completed</option>
                </select>
            </div>
        </div>

        {{-- Tabel data client inside modal --}}
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 whitespace-nowrap text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">No</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">No Input JPN</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Nama</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Tgl Lahir</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Claim Type</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @if ($clients->isEmpty())
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada data klien yang cocok dengan filter
                            </td>
                        </tr>
                    @else
                        @php $no = ($clients->currentPage() - 1) * $clients->perPage() + 1; @endphp
                        @foreach ($clients as $client)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $no++ }}</td>
                                <td class="px-4 py-3">{{ $client->no_jp }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $client->name }}</td>
                                <td class="px-4 py-3">{{ $client->dob }}</td>
                                <td class="px-4 py-3">
                                    @if ($client->claim_type === 'speed')
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300">Speed</span>
                                    @elseif ($client->claim_type === 'adjusment')
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-300">Adjustment</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">Normal</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($client->status === 'pending')
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300">Pending</span>
                                    @elseif ($client->status === 'active')
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">Active</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300">Completed</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('master_client.edit', $client->id) }}" class="inline-flex items-center justify-center font-semibold gap-1.5 rounded-lg transition px-3 py-1.5 text-xs bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Paginasi di dalam modal --}}
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/20 border border-gray-200 dark:border-gray-700 rounded-xl">
            {{ $clients->links(data: ['scrollTo' => false]) }}
        </div>
    </div>
</x-ui.modal>
