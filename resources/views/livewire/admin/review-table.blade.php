<div>
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Total Reviews -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-400 dark:text-gray-400">Total Ulasan</p>
                <h4 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $stats['total'] }}</h4>
            </div>
            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 text-blue-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                </svg>
            </div>
        </div>

        <!-- Intercepted -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-400 dark:text-gray-400">Intercepted</p>
                <h4 class="text-2xl font-bold text-red-500 mt-1">{{ $stats['intercepted'] }}</h4>
            </div>
            <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>

        <!-- Resolved -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-400 dark:text-gray-400">Resolved</p>
                <h4 class="text-2xl font-bold text-green-500 mt-1">{{ $stats['resolved'] }}</h4>
            </div>
            <div class="w-12 h-12 bg-green-50 dark:bg-green-900/20 text-green-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Forwarded -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-400 dark:text-gray-400">Forwarded (Google)</p>
                <h4 class="text-2xl font-bold text-purple-500 mt-1">{{ $stats['forwarded'] }}</h4>
            </div>
            <div class="w-12 h-12 bg-purple-50 dark:bg-purple-900/20 text-purple-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">Filter Status:</span>
            <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-900 rounded-xl">
                <button type="button" 
                        wire:click="$set('statusFilter', 'all')" 
                        class="px-4 py-2 text-xs font-semibold rounded-lg transition {{ $statusFilter === 'all' ? 'bg-white dark:bg-gray-800 text-gray-800 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-800 dark:hover:text-white' }}">
                    Semua
                </button>
                <button type="button" 
                        wire:click="$set('statusFilter', 'intercepted')" 
                        class="px-4 py-2 text-xs font-semibold rounded-lg transition {{ $statusFilter === 'intercepted' ? 'bg-white dark:bg-gray-800 text-gray-800 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-800 dark:hover:text-white' }}">
                    Intercepted
                </button>
                <button type="button" 
                        wire:click="$set('statusFilter', 'resolved')" 
                        class="px-4 py-2 text-xs font-semibold rounded-lg transition {{ $statusFilter === 'resolved' ? 'bg-white dark:bg-gray-800 text-gray-800 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-800 dark:hover:text-white' }}">
                    Resolved
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 text-xs font-bold uppercase">
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Nama Klien</th>
                        <th class="px-6 py-4">Rating</th>
                        <th class="px-6 py-4">Pesan</th>
                        <th class="px-6 py-4">Tanggal Submit</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-700 dark:text-gray-300">
                    @forelse ($reviews as $index => $review)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition duration-150">
                            <td class="px-6 py-4 font-semibold">{{ $reviews->firstItem() + $index }}</td>
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $review->client->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center text-amber-400">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'fill-current' : 'text-gray-300 dark:text-gray-650' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.24.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.18 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118l-3.97-2.883c-.77-.57-.37-1.81.588-1.81h4.906a1 1 0 00.951-.69l1.519-4.674z"></path>
                                        </svg>
                                    @endfor
                                    <span class="ml-1 text-xs font-semibold text-gray-500">({{ $review->rating }}/5)</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate" title="{{ $review->message }}">{{ $review->message ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $review->submitted_at ? $review->submitted_at->timezone('Asia/Jakarta')->format('d M Y H:i') : '-' }} WIB</td>
                            <td class="px-6 py-4">
                                @if ($review->status === 'intercepted')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">
                                        Intercepted
                                    </span>
                                @elseif ($review->status === 'resolved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400" title="Oleh: {{ $review->resolver->name ?? '-' }}&#10;Catatan: {{ $review->notes ?? '-' }}">
                                        Resolved
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400">
                                        Forwarded
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if ($review->status === 'intercepted')
                                    <button type="button" 
                                            wire:click="openResolveModal({{ $review->id }})" 
                                            class="inline-flex items-center px-3.5 py-1.5 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl shadow-sm hover:shadow transition text-xs">
                                        Tandai Resolved
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-550">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                Tidak ada data ulasan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reviews->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/30">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>

    <!-- Resolve Modal -->
    @if ($isModalOpen)
        <div class="fixed inset-0 z-9999 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" wire:click="closeResolveModal"></div>
            
            <!-- Content -->
            <div class="relative w-full max-w-md mx-auto my-6 z-10 p-4">
                <div class="relative flex flex-col w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-2xl outline-none focus:outline-none overflow-hidden transition-all transform scale-100">
                    <!-- Header -->
                    <div class="flex items-start justify-between p-5 border-b border-gray-200 dark:border-gray-700 rounded-t">
                        <h3 class="text-lg font-extrabold text-gray-800 dark:text-white">
                            Tandai Ulasan Selesai
                        </h3>
                        <button type="button" class="p-1 ml-auto bg-transparent border-0 text-gray-400 hover:text-gray-650 dark:hover:text-white outline-none focus:outline-none transition" wire:click="closeResolveModal">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="relative p-6 flex-auto">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label for="resolution-notes" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Catatan Resolusi <span class="text-xs text-gray-400 font-normal">(Opsional)</span>
                                </label>
                                <textarea id="resolution-notes" 
                                          wire:model.defer="notes" 
                                          rows="4" 
                                          placeholder="Masukkan catatan resolusi penanganan keluhan klien..."
                                          class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none transition duration-150"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end p-5 border-t border-gray-200 dark:border-gray-700 rounded-b space-x-3">
                        <button type="button" 
                                wire:click="closeResolveModal" 
                                class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold rounded-xl text-sm transition">
                            Batal
                        </button>
                        <button type="button" 
                                wire:click="resolveReview" 
                                class="px-5 py-2.5 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl text-sm shadow-md hover:shadow-lg transition">
                            Simpan Resolusi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
