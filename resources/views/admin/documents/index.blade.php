@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="{ activeTab: 'ichijikin', loading: false, showStep2: {{ $referenceClients ? 'true' : 'false' }} }">
    <!-- Breadcrumb / Header -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Admin Tab: Upload Dokumen (2-Step Flow)</h2>
            <p class="text-sm text-gray-500 mt-1">Upload list referensi Excel, jalankan name matching, review hasil, lalu unggah dokumen ke awan Supabase.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 rounded text-green-700 dark:text-green-300 font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded text-red-700 dark:text-red-300 font-semibold">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded text-red-700 dark:text-red-300">
            <ul class="list-disc pl-5 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Step Upload Section -->
        <div class="lg:col-span-5 space-y-6">
            @if(!$referenceClients)
                <!-- STEP 1: Upload Excel Reference -->
                <div class="bg-white dark:bg-gray-850 rounded-xl shadow border border-gray-200 dark:border-gray-800 p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-brand-500 text-white font-bold text-sm">1</span>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Step 1 — Upload Excel Referensi</h3>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">Excel berisi daftar klien Jepang wajib diunggah terlebih dahulu sebagai basis data pencocokan (NO INPUT JPN, NAMA, TGL LAHIR).</p>
                    
                    <form action="{{ route('admin.documents.uploadReference') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pilih File Excel (.xlsx, .xls)</label>
                            <input type="file" name="excel_file" accept=".xlsx,.xls" required
                                class="block w-full text-sm text-gray-500 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 p-2.5 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        </div>
                        <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-2 px-4 rounded-lg flex items-center justify-center transition duration-200">
                            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Upload Excel Referensi
                        </button>
                    </form>
                </div>
            @else
                <!-- STEP 1 PREVIEW: Looked up Excel Data -->
                <div class="bg-white dark:bg-gray-850 rounded-xl shadow border border-gray-200 dark:border-gray-800 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-green-500 text-white font-bold text-sm">✓</span>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Step 1 — Preview Referensi</h3>
                        </div>
                        <form action="{{ route('admin.documents.clearReference') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin me-reset referensi klien saat ini?');">
                            @csrf
                            <button type="submit" class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 font-bold flex items-center">
                                <svg class="w-4 h-4 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Reset
                            </button>
                        </form>
                    </div>

                    <!-- Summary Stats -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="p-3 bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-900 rounded-lg text-center">
                            <div class="text-xs font-semibold text-green-700 dark:text-green-400">Ditemukan di DB</div>
                            <div class="text-2xl font-bold text-green-800 dark:text-green-300 mt-1">{{ collect($referenceClients)->where('found', true)->count() }}</div>
                        </div>
                        <div class="p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900 rounded-lg text-center">
                            <div class="text-xs font-semibold text-red-700 dark:text-red-400">Tidak Ditemukan</div>
                            <div class="text-2xl font-bold text-red-800 dark:text-red-300 mt-1">{{ collect($referenceClients)->where('found', false)->count() }}</div>
                        </div>
                    </div>

                    <!-- Client lists -->
                    <div class="max-h-48 overflow-y-auto mb-4 border border-gray-100 dark:border-gray-800 rounded-lg divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                        @foreach($referenceClients as $ref)
                            <div class="p-2.5 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-gray-800/10">
                                <div>
                                    <div class="font-bold text-gray-800 dark:text-white">{{ $ref['name'] }}</div>
                                    <div class="text-[10px] text-gray-500 font-mono mt-0.5">JPN: {{ $ref['no_jp'] ?? 'N/A' }}</div>
                                </div>
                                <div>
                                    @if($ref['found'])
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">Ditemukan</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400" title="Klien belum di-import ke master data">Belum Ada</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Next button -->
                    <button type="button" x-show="!showStep2" @click="showStep2 = true"
                        class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-2.5 px-4 rounded-lg flex items-center justify-center transition">
                        Lanjut ke Upload Dokumen
                        <svg class="w-5 h-5 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>

                <!-- STEP 2: Upload Document Files -->
                <div x-show="showStep2" x-transition class="bg-white dark:bg-gray-850 rounded-xl shadow border border-gray-200 dark:border-gray-800 p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-brand-500 text-white font-bold text-sm">2</span>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Step 2 — Upload File Dokumen</h3>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">Unggah file dokumen gambar klien dalam batch referensi ini. Sistem akan mencocokkan nama file secara dinamis.</p>

                    <form action="{{ route('admin.documents.upload') }}" method="POST" enctype="multipart/form-data" @submit="loading = true">
                        @csrf

                        <!-- Tab Navigation Buttons -->
                        <div class="flex border-b border-gray-200 dark:border-gray-700 mb-4">
                            <button type="button" 
                                class="flex-1 py-2 px-1 text-center font-semibold text-sm border-b-2 transition-all duration-200"
                                :class="activeTab === 'ichijikin' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                @click="activeTab = 'ichijikin'">
                                Ichijikin
                            </button>
                            <button type="button" 
                                class="flex-1 py-2 px-1 text-center font-semibold text-sm border-b-2 transition-all duration-200"
                                :class="activeTab === 'resi' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                @click="activeTab = 'resi'">
                                Resi Transfer
                            </button>
                            <button type="button" 
                                class="flex-1 py-2 px-1 text-center font-semibold text-sm border-b-2 transition-all duration-200"
                                :class="activeTab === 'kwitansi' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                @click="activeTab = 'kwitansi'">
                                Kwitansi
                            </button>
                        </div>

                        <!-- Tab Contents -->
                        <div class="mb-6 min-h-[140px]">
                            <!-- Ichijikin Tab -->
                            <div x-show="activeTab === 'ichijikin'" class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">File Dokumen Ichijikin</label>
                                <p class="text-xs text-gray-500 mb-2">Format nama file wajib: `NAMA_DEPAN_NAMA_BELAKANG.JPG` (.jpg, .jpeg, .png)</p>
                                <input type="file" name="ichijikin_files[]" multiple accept=".jpg,.jpeg,.png"
                                    class="block w-full text-sm text-gray-500 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 p-2 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            </div>

                            <!-- Resi Tab -->
                            <div x-show="activeTab === 'resi'" class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">File Dokumen Resi Transfer</label>
                                <p class="text-xs text-gray-500 mb-2">Format nama file wajib: `NAMA_DEPAN_NAMA_BELAKANG.JPG` (.jpg, .jpeg, .png)</p>
                                <input type="file" name="resi_files[]" multiple accept=".jpg,.jpeg,.png"
                                    class="block w-full text-sm text-gray-500 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 p-2 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            </div>

                            <!-- Kwitansi Tab -->
                            <div x-show="activeTab === 'kwitansi'" class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">File Dokumen Kwitansi</label>
                                <p class="text-xs text-gray-500 mb-2">Format nama file wajib: `NAMA_DEPAN_NAMA_BELAKANG.JPG` (.jpg, .jpeg, .png)</p>
                                <input type="file" name="kwitansi_files[]" multiple accept=".jpg,.jpeg,.png"
                                    class="block w-full text-sm text-gray-500 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 p-2 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                            class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-2.5 px-4 rounded-lg flex items-center justify-center transition duration-200"
                            :disabled="loading">
                            <span x-show="!loading" class="flex items-center">
                                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                Proses Matching
                            </span>
                            <span x-show="loading" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Sedang Memproses Matching...
                            </span>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Recent Batches Section -->
        <div class="lg:col-span-7 bg-white dark:bg-gray-850 rounded-xl shadow border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Riwayat Batch Upload</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Batch ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">PIC Upload</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Matched</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Gagal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($batches as $batch)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">#{{ $batch->id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $batch->uploader->name ?? 'System' }}</td>
                                <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white font-medium">{{ $batch->total_files }}</td>
                                <td class="px-4 py-3 text-sm text-center text-green-600 dark:text-green-400 font-semibold">{{ $batch->matched_files }}</td>
                                <td class="px-4 py-3 text-sm text-center text-red-600 dark:text-red-400 font-semibold">{{ $batch->failed_files }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($batch->status === 'processing')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 animate-pulse">Processing</span>
                                    @elseif($batch->status === 'completed')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">Completed</span>
                                    @elseif($batch->status === 'partial_failed')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400">Partial Failed</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400">Failed</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $batch->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="{{ route('admin.documents.batchDetail', $batch->id) }}" 
                                            class="inline-flex items-center text-brand-600 dark:text-brand-400 hover:underline font-semibold">
                                            Detail
                                            <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>

                                        <a href="{{ route('admin.documents.download-status.export', $batch->id) }}" 
                                            class="inline-flex items-center text-emerald-600 dark:text-emerald-400 hover:underline font-semibold"
                                            title="Export Status Download Excel">
                                            Excel Status
                                            <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </a>

                                        @php
                                            $client = $batch->documents->first()?->client;
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
                                            <button class="inline-flex items-center text-gray-400 dark:text-gray-500 font-semibold cursor-not-allowed opacity-50" 
                                                title="Dokumen belum lengkap" 
                                                disabled>
                                                Download ZIP
                                                <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada batch upload yang diproses.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $batches->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
