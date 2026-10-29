@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6">
        @if(auth()->user()->hasRole(\App\Models\User::ROLE_CLIENT))
        <div class="col-span-12">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Surat Perjanjian</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-850 rounded-xl shadow border border-gray-200 dark:border-gray-800 p-6 flex flex-col justify-between h-full transition duration-200 hover:shadow-md">
                <div>
                    <!-- Icon & Type Title -->
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white">Surat Perjanjian</h4>
                        <div class="p-2 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                            <!-- File Text Icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div>
                    <a href="{{ route('agreement.download') }}" download
                        class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-2.5 px-4 rounded-lg flex items-center justify-center transition duration-200">
                        <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Dokumen
                    </a>
                </div>
            </div>

            </div>
        </div>
        @else
        @endif
    </div>
 
@endsection
