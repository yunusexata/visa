@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Preview Hasil Name Matching</h2>
            <p class="text-sm text-gray-500 mt-1">Review hasil pencocokan nama otomatis sebelum mengunggah ke penyimpanan awan Supabase.</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-2">
            <form action="{{ route('admin.documents.clearReference') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan batch ini? Semua file sementara akan dihapus.');">
                @csrf
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 font-bold py-2.5 px-4 rounded-lg text-sm transition">
                    Batalkan Batch
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 rounded text-green-700 dark:text-green-300 font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded text-red-700 dark:text-red-300">
            <ul class="list-disc pl-5 font-semibold">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Section A: Files successfully matched -->
    <details class="group bg-white dark:bg-gray-850 rounded-xl shadow border border-gray-200 dark:border-gray-800 mb-6">
        <!-- Header / Toggle -->
        <summary class="p-6 cursor-pointer list-none">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-850 pb-3 group-open:mb-4">
                <div class="flex items-center space-x-2">
                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-green-500 text-white text-xs font-bold">A</span>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Section A — File Berhasil Match / Siap Upload ({{ count($matchedFiles) }})</h3>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-xs text-gray-400">Akan diunggah ke folder klien terkait</span>
                    <!-- Chevron Icon for visual feedback -->
                    <svg class="w-5 h-5 text-gray-500 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
        </summary>

        <!-- Collapsible Content -->
        <div class="px-6 pb-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama File</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Klien Terkait</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Confidence</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($matchedFiles as $file)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white break-all">{{ $file['original_filename'] }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">{{ ucfirst($file['folder_type']) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-brand-600 dark:text-brand-400">{{ $file['client_name'] }}</td>
                            <td class="px-4 py-3 text-sm text-center font-mono">
                                @if($file['manually_assigned'])
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">Manual Resolve</span>
                                @elseif($file['confidence'] >= 1.0)
                                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300">100% (Exact)</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-brand-850 dark:bg-blue-950 dark:text-brand-300">{{ number_format($file['confidence'] * 100, 1) }}%</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <span class="text-green-600 dark:text-green-400 font-bold flex items-center justify-end text-xs">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    ✓ Siap Upload
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Belum ada file yang berhasil di-match dengan status matched (skor >= 95%). Gunakan Section B di bawah untuk melakukan konfirmasi/resolusi manual.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </details>

    <!-- Section B: Perlu Perhatian -->
    <div class="mb-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center space-x-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-red-500 text-white text-xs font-bold">B</span>
            <span>Section B — Perlu Perhatian</span>
        </h3>

        <!-- Sub-kondisi 1: File Ada, Tapi Name-Match Kurang Cocok / Ambigu -->
        <div class="bg-white dark:bg-gray-850 rounded-xl shadow border border-gray-200 dark:border-gray-800 p-6 mb-6">
            <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-850 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="flex items-center justify-center w-5 h-5 rounded bg-amber-500 text-white text-xs font-bold">1</span>
                    <h4 class="text-base font-bold text-gray-800 dark:text-white">Sub-kondisi 1 — File Ada, Tapi Butuh Konfirmasi / Koreksi ({{ count($failedFiles) }})</h4>
                </div>
                <span class="text-xs text-gray-400">Pilih kandidat murni, approve rekomendasi, atau assign manual</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/4">Nama File</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/12">Tipe</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/6">Klasifikasi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/6">Saran / Rekomendasi</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/3">Assign ke Klien</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($failedFiles as $file)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white break-all">{{ $file['original_filename'] }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">{{ ucfirst($file['folder_type']) }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($file['status'] === 'ambiguous')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-400">Nama Ambigu</span>
                                    @elseif($file['status'] === 'low_confidence')
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-400">Low Confidence</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-400">Gagal Match</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($file['status'] === 'ambiguous')
                                        <span class="text-xs text-orange-600 dark:text-orange-400 font-semibold">Ditemukan {{ count($file['candidates']) }} klien nama sama</span>
                                    @elseif($file['status'] === 'low_confidence')
                                        @if($file['best_candidate'])
                                            <div class="font-bold text-gray-800 dark:text-gray-200">{{ $file['best_candidate']['name'] }}</div>
                                            <div class="text-[10px] text-gray-500 font-semibold mt-0.5">Skor: {{ number_format($file['confidence'] * 100, 1) }}%</div>
                                        @else
                                            <span class="text-gray-400 italic">Tidak ada saran</span>
                                        @endif
                                    @else
                                        @if($file['best_candidate'])
                                            <div class="text-gray-700 dark:text-gray-400 font-semibold">{{ $file['best_candidate']['name'] }}</div>
                                            <div class="text-[10px] text-gray-500 mt-0.5">Skor: {{ number_format($file['confidence'] * 100, 1) }}%</div>
                                        @else
                                            <span class="text-gray-400 italic">Tidak ada saran</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <!-- Dynamic Action based on classification status -->
                                    @if($file['status'] === 'ambiguous')
                                        <!-- Case 1: Horizontal button groups for <= 3 candidates -->
                                        @if(count($file['candidates']) <= 3)
                                            <div class="flex flex-wrap gap-2 text-left justify-end">
                                                @foreach($file['candidates'] as $candidate)
                                                    <form action="{{ route('admin.documents.previewAssign') }}" method="POST" class="inline-block">
                                                        @csrf
                                                        <input type="hidden" name="temp_batch_id" value="{{ $tempBatchId }}">
                                                        <input type="hidden" name="original_filename" value="{{ $file['original_filename'] }}">
                                                        <input type="hidden" name="client_id" value="{{ $candidate['id'] }}">
                                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 text-xs bg-orange-50 hover:bg-orange-100 text-orange-700 rounded-lg border border-orange-200 transition font-semibold" title="Assign ke klien ini">
                                                            <span class="flex flex-col text-left">
                                                                <span class="font-bold">{{ $candidate['name'] }}</span>
                                                                <span class="text-[9px] opacity-80 mt-0.5">JPN: {{ $candidate['no_jp'] }} | Lahir: {{ $candidate['dob'] }}</span>
                                                            </span>
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        @else
                                            <!-- Fallback vertical layout if candidates > 3 -->
                                            <div class="space-y-1.5 text-left flex flex-col items-end">
                                                @foreach($file['candidates'] as $candidate)
                                                    <form action="{{ route('admin.documents.previewAssign') }}" method="POST" class="block w-full max-w-[260px]">
                                                        @csrf
                                                        <input type="hidden" name="temp_batch_id" value="{{ $tempBatchId }}">
                                                        <input type="hidden" name="original_filename" value="{{ $file['original_filename'] }}">
                                                        <input type="hidden" name="client_id" value="{{ $candidate['id'] }}">
                                                        <button type="submit" class="w-full text-left px-2 py-1 bg-orange-50 hover:bg-orange-100 text-orange-700 rounded border border-orange-200 transition font-medium text-xs flex justify-between items-center">
                                                            <span>
                                                                <span class="font-bold">{{ $candidate['name'] }}</span> 
                                                                <span class="text-[9px] opacity-80">(JPN: {{ $candidate['no_jp'] }}, {{ $candidate['dob'] }})</span>
                                                            </span>
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        @endif
                                    @elseif($file['status'] === 'low_confidence')
                                        <!-- Case 2: Low confidence support Approve Recommended or choose other client -->
                                        <div class="flex flex-col items-end gap-1.5">
                                            @if($file['best_candidate'])
                                                <form action="{{ route('admin.documents.previewAssign') }}" method="POST" class="w-full max-w-[280px]">
                                                    @csrf
                                                    <input type="hidden" name="temp_batch_id" value="{{ $tempBatchId }}">
                                                    <input type="hidden" name="original_filename" value="{{ $file['original_filename'] }}">
                                                    <input type="hidden" name="client_id" value="{{ $file['best_candidate']['id'] }}">
                                                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white text-xs font-bold py-1.5 px-3 rounded-lg flex items-center justify-center transition">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        Setujui "{{ $file['best_candidate']['name'] }}"
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <!-- Alternative searchable dropdown lookup -->
                                            <div class="w-full max-w-[280px]">
                                                @include('admin.documents.partials.search-assign-form', ['file' => $file, 'tempBatchId' => $tempBatchId])
                                            </div>
                                        </div>
                                    @else
                                        <!-- Case 3: Failed match global searchable combobox -->
                                        <div class="w-full max-w-[280px] inline-block">
                                            @include('admin.documents.partials.search-assign-form', ['file' => $file, 'tempBatchId' => $tempBatchId])
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-green-600 dark:text-green-400 font-bold">
                                    ✓ Sempurna! Seluruh file berhasil ter-match tanpa kendala.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sub-kondisi 2: Klien Ada di Excel, Tapi File Tidak Ditemukan (Hanya tampil jika ada data missing) -->
        @if(!empty($missingDocuments) && count($missingDocuments) > 0)
            <div class="bg-white dark:bg-gray-850 rounded-xl shadow border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-850 pb-3">
                    <div class="flex items-center space-x-2">
                        <span class="flex items-center justify-center w-5 h-5 rounded bg-orange-500 text-white text-xs font-bold">2</span>
                        <h4 class="text-base font-bold text-gray-800 dark:text-white">Sub-kondisi 2 — Klien Ada di Excel Referensi, Tapi File Belum Lengkap ({{ count($missingDocuments) }})</h4>
                    </div>
                    <span class="text-xs text-gray-400">Informasi status ketersediaan dokumen klien SOT</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Klien</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No Input JPN</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ichijikin</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Resi Transfer</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kwitansi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($missingDocuments as $missing)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $missing['name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $missing['no_jp'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        @if($missing['ichijikin'])
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-50 text-green-600 dark:bg-green-950/20 dark:text-green-400">✓ Ada</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400">✗ Tidak Ada</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        @if($missing['resi'])
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-50 text-green-600 dark:bg-green-950/20 dark:text-green-400">✓ Ada</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400">✗ Tidak Ada</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        @if($missing['kwitansi'])
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-50 text-green-600 dark:bg-green-950/20 dark:text-green-400">✓ Ada</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-600 dark:bg-red-950/20 dark:text-red-400">✗ Tidak Ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Final Confirmation Form -->
    <div class="bg-gray-50 dark:bg-gray-800/35 border border-gray-200 dark:border-gray-850 p-6 rounded-xl flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h4 class="text-base font-bold text-gray-800 dark:text-white">Siap untuk Mengunggah?</h4>
            <p class="text-xs text-gray-500 mt-1">Daftar klien dan file yang siap diunggah akan segera ditarik dari antrean latar belakang (Queue).</p>
        </div>
        <form action="{{ route('admin.documents.confirmUpload') }}" method="POST" x-data="{ confirming: false }" @submit="confirming = true">
            @csrf
            <input type="hidden" name="temp_batch_id" value="{{ $tempBatchId }}">
            <button type="submit" 
                class="bg-brand-500 hover:bg-brand-600 text-white font-bold py-3 px-6 rounded-lg shadow flex items-center justify-center transition"
                :disabled="confirming">
                <span x-show="!confirming" class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Konfirmasi & Upload Semua
                </span>
                <span x-show="confirming" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Men-dispatch Queue...
                </span>
            </button>
        </form>
    </div>
</div>
@endsection
