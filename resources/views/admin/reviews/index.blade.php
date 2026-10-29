@extends('layouts.app')

@section('content')
    <div class="col-span-12">
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Manajemen Ulasan Riset Kepuasan</h3>
        </div>
        
        <livewire:admin.datatable-upod-riset />
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Manajemen Ulasan</h3>
            <p class="text-sm text-gray-500">Kelola dan selesaikan ulasan kepuasan klien yang terintercepted</p>
        </div>
        
        <livewire:admin.review-table />

        <div class="mt-10 pt-10 border-t border-gray-200 dark:border-gray-800">
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">UPOD Control Panel</h3>
                <p class="text-sm text-gray-500">Atur status target UPOD untuk masing-masing klien</p>
            </div>
            
            <livewire:admin.review.datatable :upodMode="true" />
        </div>
    </div>
@endsection
