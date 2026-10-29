@extends('layouts.app')

@section('header')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        
        <div>
            <h2 class="text-title-md2 font-bold text-black dark:text-white">
                Akses
            </h2>
            
            <nav class="mt-1">
                <ul class="flex items-center gap-2">
                    <li>
                        <a class="font-medium text-primary" href="#">Dashboard</a>
                    </li>
                    <li class="text-body dark:text-bodydark">/</li>
                    <li class="font-medium text-body dark:text-bodydark">Akses</li>
                </ul>
            </nav>
        </div>

        <div class="flex items-center">
            <a 
                href="{{ route('permission.index') }}" 
                class="inline-flex items-center justify-center gap-2.5 rounded bg-primary px-6 py-3 text-center font-medium bg-blue-300 rounded hover:bg-opacity-90 shadow-sm"
            >
                Kembali
            </a>
        </div>

    </div>
@stop
@section('content')

    <x-common.component-card title="">
        <livewire:account.permission.detail :objId="$objId" />
    </x-common.component-card>
    
@stop
