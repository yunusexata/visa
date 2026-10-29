@extends('layouts.app')


@section('header')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        
        <div>
            <h2 class="text-title-md2 font-bold text-black dark:text-white">
                Jabatan
            </h2>
            
            <nav class="mt-1">
                <ul class="flex items-center gap-2">
                    <li>
                        <a class="font-medium text-primary" href="#">Dashboard</a>
                    </li>
                    <li class="text-body dark:text-bodydark">/</li>
                    <li class="font-medium text-body dark:text-bodydark">Jabatan</li>
                </ul>
            </nav>
        </div>

        @can(PermissionHelper::transform(PermissionHelper::ACCESS_ROLE, PermissionHelper::TYPE_CREATE))
            <div class="flex items-center">
                <a 
                    href="{{ route('role.create') }}" 
                    class="inline-flex items-center bg-green-300 rounded justify-center gap-2.5 rounded bg-meta-3 px-6 py-3 text-center font-medium hover:bg-opacity-90 shadow-sm"
                >
                    Tambah Baru
                </a>
            </div>
        @endcan

    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <livewire:account.role.datatable lazy />
        </div>
    </div>
@stop
