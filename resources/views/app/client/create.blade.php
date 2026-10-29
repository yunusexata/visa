@extends('layouts.fullscreen-layout')

@section('content')
    
    <x-common.component-card title="" class="flex justify-center flex-nowrap">
        
    <div class="w-full max-w-lg">
        <!-- Logo / Branding Header -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-extrabold text-[#111827] tracking-tight">EXATA <span class="text-[#F97316]">INDONESIA</span></h1>
            <p class="text-gray-600 text-sm mt-1 font-medium">Konfirmasi Status Layanan Pemrosesan Nenkin</p>
        </div>

        @livewire('client.create-user', ['token' => $token])

        <div class="text-center text-xs text-gray-500 mt-6">
            &copy; 2026 PT Sumber Rezeki Exata Indonesia. Seluruh Hak Cipta Dilindungi.
        </div>
    </div>
    </x-common.component-card>
 
@endsection
