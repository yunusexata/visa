@extends('layouts.fullscreen-layout')

@section('content')
@php
    $currentYear = date('Y');
@endphp
  <div class="relative flex flex-col items-center justify-center min-h-screen p-6 overflow-hidden z-1 bg-gray-50 dark:bg-gray-900">
      {{-- common grid shape --}}
      <x-common.common-grid-shape />
      
      <!-- Centered Content -->
      <div class="mx-auto w-full max-w-[242px] text-center sm:max-w-[472px]">
          <h1 class="mb-4 font-black text-brand-navy dark:text-white text-5xl sm:text-7xl tracking-tight">
              403
          </h1>

          <img src="/images/error/503.svg" alt="403" class="dark:hidden mx-auto max-h-64 mb-8" />
          <img src="/images/error/503-dark.svg" alt="403" class="hidden dark:block mx-auto max-h-64 mb-8" />

          <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white/90">Akses Ditolak</h2>
          <p class="mt-4 mb-8 text-sm sm:text-base text-gray-600 dark:text-gray-400">
              Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
          </p>

          <a href="/"
              class="inline-flex items-center justify-center rounded-xl bg-brand-orange px-6 py-3.5 text-sm font-bold text-white shadow-theme-xs hover:bg-brand-orange/90 transition duration-300">
              Kembali ke Dashboard
          </a>
      </div>
      
      <!-- Footer -->
      <p class="absolute text-xs text-center text-gray-500 -translate-x-1/2 bottom-6 left-1/2 dark:text-gray-400">
          &copy; {{ $currentYear }} PT Sumber Rezeki Exata Indonesia. Seluruh hak dilindungi.
      </p>
  </div>
@endsection
