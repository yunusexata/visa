<div class="bg-white dark:bg-gray-850 rounded-xl shadow border border-gray-200 dark:border-gray-800 p-6 flex flex-col justify-between h-full transition duration-200 hover:shadow-md">
    <div>
        <!-- Icon & Type Title -->
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-bold text-gray-800 dark:text-white">{{ $title }}</h4>
            <div class="p-2 rounded-lg {{ $document ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-400' }}">
                @if($type === 'ichijikin')
                    <!-- File Text Icon -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                @elseif($type === 'resi')
                    <!-- Receipt/Credit Card Icon -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                @else
                    <!-- Cash/Kwitansi Icon -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                @endif
            </div>
        </div>

        <!-- Status Indicator -->
        <div class="mb-6">
            <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider block">Status Dokumen</span>
            @if($document)
                <div class="flex items-center text-green-600 dark:text-green-400 font-bold text-sm mt-1">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tersedia
                </div>
                <div class="text-[10px] text-gray-500 mt-1">Selesai diunggah pada {{ $document->uploaded_at->format('d M Y') }}</div>
            @else
                <div class="flex items-center text-gray-500 font-semibold text-sm mt-1">
                    <svg class="w-5 h-5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.364A9 9 0 015.636 5.636m12.728 12.364L5.636 5.636"></path></svg>
                    Belum tersedia
                </div>
                <div class="text-[10px] text-gray-400 mt-1">Menunggu proses upload oleh admin</div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div>
        @if($document)
            <a href="{{ route('documents.download', $type) }}" download
                class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-2.5 px-4 rounded-lg flex items-center justify-center transition duration-200">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download Dokumen
            </a>
        @else
            <button disabled 
                class="w-full bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 font-bold py-2.5 px-4 rounded-lg cursor-not-allowed flex items-center justify-center border border-gray-200 dark:border-gray-700">
                Download Belum Tersedia
            </button>
        @endif
    </div>
</div>
