@extends('layouts.app')

@section('content')
    
    <x-common.component-card title="">
        <div data-slot="card-content" class="group-data-[size=sm]/card:px-3 p-0">
            <x-ui.button size="sm" variant="primary" @click="$dispatch('open-regular-modal')">Import Excel
            </x-ui.button>
            <x-ui.button size="sm" variant="success" @click="$dispatch('export', { type: '{{ App\Helpers\ExportHelper::TYPE_EXCEL }}' })">Export Excel
            </x-ui.button>
        </div>
    <livewire:master-client.import-modal />
    
        @livewire('master-client.datatable')
    </x-common.component-card>
 
@endsection
