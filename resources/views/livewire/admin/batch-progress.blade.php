<div wire:poll.2s="checkProgress" class="max-w-md mx-auto my-12">
    <div class="bg-white dark:bg-gray-850 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 p-6 text-center">
        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Mengunggah Dokumen ke Supabase</h3>
        
        <div class="flex items-center justify-center mb-6">
            <!-- Spinner -->
            <svg class="animate-spin h-10 w-10 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <div class="mb-4">
            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                Status: 
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold 
                    {{ $status === 'processing' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' }}">
                    {{ ucfirst($status) }}
                </span>
            </span>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 mb-3 overflow-hidden">
            <div class="bg-brand-500 h-4 rounded-full transition-all duration-500 ease-out" style="width: {{ $progress }}%"></div>
        </div>

        <div class="flex justify-between text-xs text-gray-500 mb-6">
            <span>Progress: {{ number_format($progress, 0) }}%</span>
            @if($totalJobs > 0)
                <span>Chunk: {{ $totalJobs - $pendingJobs }} / {{ $totalJobs }}</span>
            @endif
        </div>

        <div class="text-xs text-gray-400 dark:text-gray-500 border-t border-gray-100 dark:border-gray-800 pt-4">
            Jangan tutup atau memuat ulang halaman ini. Berkas Anda sedang dipindahkan dan diunggah secara aman.
        </div>
    </div>
</div>
