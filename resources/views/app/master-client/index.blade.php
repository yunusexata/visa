@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.component-card title="">
            <div data-slot="card-content" class="group-data-[size=sm]/card:px-3 p-0 mb-4 flex gap-2">
                
                @can(PermissionHelper::transform(PermissionHelper::ACCESS_MASTER_CLIENT, PermissionHelper::TYPE_CREATE))
                    <x-ui.button size="sm" variant="primary" @click="$dispatch('open-regular-modal')">Import Excel Data Client
                    </x-ui.button>
                @endCan
                <x-ui.button size="sm" variant="success" @click="$dispatch('export', { type: '{{ App\Helpers\ExportHelper::TYPE_EXCEL }}' })">Export Excel
                </x-ui.button>
            </div>
            
            <livewire:master-client.import-modal />
            <livewire:master-client.batch-detail-modal />
            
            <livewire:master-client.datatable />
        </x-common.component-card>
    </div>
@endsection
