@extends('layouts.app')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Data Permohonan Visa</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Daftar formulir aplikasi permohonan visa Jepang yang telah diajukan.</p>
    </div>

    <x-common.component-card title="">
        <livewire:form.datatable />
    </x-common.component-card>

@endsection

