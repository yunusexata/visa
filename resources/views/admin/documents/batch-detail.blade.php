@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <a href="{{ route('admin_upload_document.index') }}" class="text-brand-600 dark:text-brand-400 hover:underline flex items-center text-sm font-semibold mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Upload
            </a>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Detail Batch Upload #{{ $batch->id }}</h2>
            <p class="text-sm text-gray-500 mt-1">Status dan log pemrosesan dokumen batch</p>
        </div>
        
        <div class="flex flex-wrap gap-2">
            @if($batch->failed_files > 0)
                <a href="{{ route('admin.documents.failures.export', $batch->id) }}" 
                    class="bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-950/20 dark:hover:bg-red-900/20 border border-red-200 dark:border-red-900 font-semibold py-2 px-4 rounded-lg flex items-center transition duration-200">
                    <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download Failure Log (CSV)
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 rounded text-green-700 dark:text-green-300">
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
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Batch Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-850 p-6 rounded-xl shadow border border-gray-200 dark:border-gray-800">
            <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">Total File</span>
            <div class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $batch->total_files }}</div>
        </div>
        <div class="bg-white dark:bg-gray-850 p-6 rounded-xl shadow border border-gray-200 dark:border-gray-800">
            <span class="text-sm font-semibold text-green-600 dark:text-green-400">Matched / Berhasil</span>
            <div class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $batch->matched_files }}</div>
        </div>
        <div class="bg-white dark:bg-gray-850 p-6 rounded-xl shadow border border-gray-200 dark:border-gray-800">
            <span class="text-sm font-semibold text-red-600 dark:text-red-400">Gagal Match</span>
            <div class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $batch->failed_files }}</div>
        </div>
        <div class="bg-white dark:bg-gray-850 p-6 rounded-xl shadow border border-gray-200 dark:border-gray-800">
            <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">Status Batch</span>
            <div class="mt-2">
                @if($batch->status === 'processing')
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 animate-pulse">Processing</span>
                @elseif($batch->status === 'completed')
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">Completed</span>
                @elseif($batch->status === 'partial_failed')
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400">Partial Failed</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400">Failed</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Detail Tabs / Sections -->
    <div class="space-y-6" x-data="{ detailTab: 'failures' }">
        <!-- Navigation Tabs -->
        <div class="flex border-b border-gray-200 dark:border-gray-700">
            <button class="py-2 px-4 font-semibold text-sm border-b-2 transition-all duration-200"
                :class="detailTab === 'failures' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700'"
                @click="detailTab = 'failures'">
                Log Kegagalan ({{ $failures->count() }})
            </button>
            <button class="py-2 px-4 font-semibold text-sm border-b-2 transition-all duration-200"
                :class="detailTab === 'matched' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700'"
                @click="detailTab = 'matched'">
                Dokumen Berhasil Match ({{ $documents->count() }})
            </button>
        </div>

        <!-- Failure Log Section -->
        <div x-show="detailTab === 'failures'" class="bg-white dark:bg-gray-850 rounded-xl shadow border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Daftar Dokumen Gagal Match</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama File</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Folder</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alasan Gagal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rekomendasi Best Match</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status Resolve</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Manual Resolve</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($failures as $failure)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white break-all">{{ $failure->original_filename }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">{{ ucfirst($failure->folder_type) }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-red-600 dark:text-red-400">{{ $failure->failure_reason }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($failure->best_match_name)
                                        <div class="text-gray-800 dark:text-gray-200 font-semibold">{{ $failure->best_match_name }}</div>
                                        <div class="text-xs text-gray-500">Kecocokan: {{ number_format($failure->best_match_score * 100, 1) }}%</div>
                                    @else
                                        <span class="text-gray-400 italic">Tidak ada saran</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($failure->resolved_at)
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 flex items-center w-max">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Resolved
                                        </span>
                                        <div class="text-[10px] text-gray-500 mt-1">Ke: {{ $failure->resolvedClient->name ?? 'N/A' }}</div>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right">
                                    @if(!$failure->resolved_at)
                                        <!-- Manual Resolve Searchable Combobox Component -->
                                        <form action="{{ route('admin.documents.failures.resolve', $failure->id) }}" method="POST" class="inline-block w-full max-w-[280px]">
                                            @csrf
                                            <div x-data="{
                                                search: '',
                                                results: [],
                                                selectedClient: null,
                                                loading: false,
                                                fetchClients() {
                                                    if (this.search.length < 2) {
                                                        this.results = [];
                                                        return;
                                                    }
                                                    this.loading = true;
                                                    fetch('{{ route('admin.documents.clients.search') }}?q=' + encodeURIComponent(this.search))
                                                        .then(res => res.json())
                                                        .then(data => {
                                                            this.results = data;
                                                            this.loading = false;
                                                        })
                                                        .catch(() => { this.loading = false; });
                                                }
                                            }" class="relative text-left">
                                                <div class="flex gap-1.5 items-center">
                                                    <div class="relative flex-1">
                                                        <input type="text" x-model="search" @input.debounce.300ms="fetchClients()" 
                                                            placeholder="Cari nama klien..." 
                                                            class="block w-full text-xs border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 p-2 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:text-white">
                                                        <div x-show="loading" class="absolute right-2 top-2.5">
                                                            <svg class="animate-spin h-3.5 w-3.5 text-brand-500" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <button type="submit" :disabled="!selectedClient" 
                                                        class="bg-brand-500 hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold py-2 px-3 rounded-lg flex-shrink-0 transition">
                                                        Assign
                                                    </button>
                                                </div>

                                                <!-- Search Dropdown Results -->
                                                <div x-show="results.length > 0" @click.outside="results = []"
                                                    class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                                    <template x-for="client in results" :key="client.id">
                                                        <button type="button" @click="selectedClient = client; search = client.name; results = []"
                                                            class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-700 transition flex flex-col">
                                                            <span class="font-semibold text-gray-900 dark:text-white" x-text="client.name"></span>
                                                            <span class="text-[10px] text-gray-500" x-text="'JPN: ' + client.no_jp"></span>
                                                        </button>
                                                    </template>
                                                </div>

                                                <!-- Hidden input -->
                                                <input type="hidden" name="client_id" :value="selectedClient ? selectedClient.id : ''">
                                            </div>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400 italic">No action needed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400 font-medium">
                                    Luar biasa! Tidak ada kegagalan matching dalam batch ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Matched Documents Section -->
        <div x-show="detailTab === 'matched'" class="bg-white dark:bg-gray-850 rounded-xl shadow border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Daftar Dokumen Berhasil Match</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama File</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Folder / Tipe</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ter-Match Ke Klien</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No Input JPN</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Confidence Score</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu Upload</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($documents as $doc)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white break-all">{{ $doc->original_filename }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400">{{ ucfirst($doc->type) }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200 font-semibold">{{ $doc->client->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 font-mono">{{ $doc->client->no_jp ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-center">
                                    @if($doc->match_confidence >= 1.000)
                                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300">100% (Exact)</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-brand-800 dark:bg-blue-950 dark:text-brand-300">{{ number_format($doc->match_confidence * 100, 1) }}%</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">{{ $doc->uploaded_at->format('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 text-center text-sm">
                                    @php
                                        $client = $doc->client;
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
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada dokumen yang berhasil ter-match dalam batch ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
