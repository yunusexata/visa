@extends('layouts.app')

@section('content')
    <x-common.component-card title="Monitoring Bukti Transfer">
        <div data-slot="card-content" class="group-data-[size=sm]/card:px-3 p-0">
            <div class="flex flex-wrap gap-2 p-3">
                @can(\App\Helpers\PermissionHelper::transform(\App\Helpers\PermissionHelper::ACCESS_MONITORING_BUKTI_TRANSFER, \App\Helpers\PermissionHelper::TYPE_READ))
                    <x-ui.button size="sm" variant="primary" @click="$dispatch('export', { type: '{{ \App\Helpers\ExportHelper::TYPE_EXCEL }}' })">
                        Export Excel
                    </x-ui.button>
                @endcan
            </div>
            <livewire:monitoring-bukti-transfer.datatable />
        </div>
    </x-common.component-card>
@endsection
