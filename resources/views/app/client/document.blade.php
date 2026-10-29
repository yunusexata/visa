@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6">
        @if(auth()->user()->hasRole(\App\Models\User::ROLE_CLIENT))
        <div class="col-span-12">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Dokumen Saya</h3>
            <p class="text-sm text-gray-500 mb-6">Status ketersediaan dokumen Anda yang diunggah oleh admin</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @include('client.components.document-card', ['type' => 'ichijikin', 'title' => 'Ichijikin', 'document' => $documents['ichijikin'] ?? null])
            @include('client.components.document-card', ['type' => 'resi', 'title' => 'Resi Transfer', 'document' => $documents['resi'] ?? null])
            @include('client.components.document-card', ['type' => 'kwitansi', 'title' => 'Kwitansi', 'document' => $documents['kwitansi'] ?? null])
            </div>
        </div>
        @else
        <div class="col-span-12 bg-white dark:bg-gray-850 p-8 rounded-xl shadow border border-gray-200 dark:border-gray-800">
            <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Dashboard Admin</h3>
            <p class="text-sm text-gray-500">Selamat datang di Portal Nenkin Terpusat. Silakan gunakan menu sidebar untuk mengelola klien dan dokumen.</p>
        </div>
        @endif
    </div>
 
@endsection
