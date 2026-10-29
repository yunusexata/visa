@extends('layouts.app')

@section('content')
    
    <x-common.component-card title="">
        
        <div data-slot="card-content" class="group-data-[size=sm]/card:px-3 p-0">
            @can(PermissionHelper::transform(PermissionHelper::ACCESS_MASTER_CLIENT_REGISTERED, PermissionHelper::TYPE_READ))
                <x-ui.button size="sm" variant="primary" @click="$dispatch('export', { type: '{{ App\Helpers\ExportHelper::TYPE_EXCEL }}' })">Export Excel
                </x-ui.button>
            @endCan
            @can(PermissionHelper::transform(PermissionHelper::ACCESS_EXPORT_EXCEL_CLIENT_RE_ENTRY, PermissionHelper::TYPE_READ))
                <x-ui.button size="sm" variant="primary" @click="$dispatch('exportReEntry', { type: '{{ App\Helpers\ExportHelper::TYPE_EXCEL }}' })">Export Excel Re-Entry
                </x-ui.button>
            @endCan
            @if(auth()->user()->hasRole(\App\Models\User::ROLE_HS3) || auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                <x-ui.button size="sm" variant="success" @click="$dispatch('showClosingConfirmation')">Buat Closing
                </x-ui.button>
            @endif
            @if(auth()->user()->hasRole(\App\Models\User::ROLE_HS3) || auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                <x-ui.button size="sm" variant="warning" @click="$dispatch('showLockConfirmation')">Tutup Pengisian
                </x-ui.button>
            @endif
            <x-ui.button size="sm" variant="primary" @click="$dispatch('validate-modal')">
                Preview Data
            </x-ui.button>
        </div>
        <livewire:master-client-registered.datatable />
        
        <livewire:master-client-registered.preview-modal />
    </x-common.component-card>
 
@endsection
