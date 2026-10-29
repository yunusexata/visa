<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#001B5E">

    <title>@yield('title', 'Dashboard Saya') | Nenkin Portal</title>

    {{-- Scripts & Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    @stack('css')

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Safe area for phones with home indicator */
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 16px); }
    </style>
</head>

<body class="bg-[#F0F4FF] min-h-screen antialiased">

    {{-- Main content area with bottom nav offset --}}
    <div id="client-app-root" class="min-h-screen pb-24">
        @yield('content')
    </div>

    {{-- Bottom Navigation Bar --}}
    <nav class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-[0_-4px_20px_rgba(0,27,94,0.10)] pb-safe">
        <div class="flex items-center justify-around h-16 max-w-lg mx-auto px-2">

            {{-- Home --}}
            <a href="{{ route('dashboard.index') }}"
               id="nav-home"
               class="flex flex-col items-center gap-0.5 px-4 py-1 rounded-xl transition-all group
                      {{ request()->routeIs('dashboard.index') ? 'text-[#0049FF]' : 'text-gray-400 hover:text-[#0049FF]' }}">
                <svg class="w-6 h-6 transition-transform group-hover:scale-110 {{ request()->routeIs('dashboard.index') ? 'fill-[#0049FF]' : 'fill-gray-400' }}" viewBox="0 0 24 24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
                <span class="text-[10px] font-semibold leading-tight">Beranda</span>
            </a>

            {{-- Dokumen --}}
            <a href="#dokumen"
               id="nav-dokumen"
               class="flex flex-col items-center gap-0.5 px-4 py-1 rounded-xl transition-all group text-gray-400 hover:text-[#0049FF]">
                <svg class="w-6 h-6 transition-transform group-hover:scale-110 fill-gray-400 group-hover:fill-[#0049FF]" viewBox="0 0 24 24">
                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                </svg>
                <span class="text-[10px] font-semibold leading-tight">Dokumen</span>
            </a>

            {{-- Status --}}
            <a href="#status"
               id="nav-status"
               class="flex flex-col items-center gap-0.5 px-4 py-1 rounded-xl transition-all group text-gray-400 hover:text-[#0049FF]">
                <svg class="w-6 h-6 transition-transform group-hover:scale-110 fill-gray-400 group-hover:fill-[#0049FF]" viewBox="0 0 24 24">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/>
                </svg>
                <span class="text-[10px] font-semibold leading-tight">Status</span>
            </a>

            {{-- Bantuan --}}
            <a href="https://wa.me/{{ config('exata.admin_wa_number') }}"
               id="nav-bantuan"
               target="_blank"
               class="flex flex-col items-center gap-0.5 px-4 py-1 rounded-xl transition-all group text-gray-400 hover:text-[#0049FF]">
                <svg class="w-6 h-6 transition-transform group-hover:scale-110 fill-gray-400 group-hover:fill-[#0049FF]" viewBox="0 0 24 24">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 12h-2v-2h2v2zm0-4h-2V6h2v4z"/>
                </svg>
                <span class="text-[10px] font-semibold leading-tight">Bantuan</span>
            </a>

        </div>
    </nav>

    @livewireScripts

    <script>
        // Livewire global events
        Livewire.on("{{ Alert::EVENT_INFO }}", (event) => {
            Swal.fire({
                icon: event[0],
                title: event[1],
                text: event[2],
            });
        });

        Livewire.on("{{ Alert::EVENT_INFORMATION }}", (event) => {
            Swal.fire({
                position: "top-end",
                icon: event[0],
                title: event[1],
                showConfirmButton: false,
                timer: 1500
            });
        });

        Livewire.on("{{ Alert::EVENT_CONFIRMATION }}", (event) => {
            Swal.fire({
                icon: event[0],
                title: event[1],
                text: event[2],
                showCancelButton: true,
                confirmButtonColor: "#0049FF",
                cancelButtonColor: "#d33",
                confirmButtonText: event[3],
                cancelButtonText: event[4],
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch(event[5]);
                } else {
                    Livewire.dispatch(event[6]);
                }
            });
        });

        Livewire.on('refresh-page', () => { location.reload(); });

        // Bottom nav smooth scroll
        document.querySelectorAll('nav a[href^="#"]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(link.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>

    @stack('scripts')

</body>
</html>
