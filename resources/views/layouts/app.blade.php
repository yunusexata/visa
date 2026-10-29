<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Nenkin Portal') | by PT Sumber Rezeki Exata Indonesia</title>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @fluxStyles
    @livewireStyles

    @stack('css')

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

            Alpine.store('sidebar', {
                // Initialize based on screen size
                isExpanded: window.innerWidth >= 1280, // true for desktop, false for mobile
                isMobileOpen: false,
                isHovered: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    // When toggling desktop sidebar, ensure mobile menu is closed
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                    // Don't modify isExpanded when toggling mobile menu
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    // Only allow hover effects on desktop when sidebar is collapsed
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark', 'bg-gray-900');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark', 'bg-gray-900');
            }
        })();
    </script>
    
</head>

<body
    x-data="{ 'loaded': true}"
    x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
    const checkMobile = () => {
        if (window.innerWidth < 1280) {
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = true;
        }
    };
    window.addEventListener('resize', checkMobile);">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 transition-all duration-300 ease-in-out"
            :class="{
                'xl:ml-[20vw]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                // 'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[6vw]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                // 'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">
            <!-- app header start -->
            @include('layouts.app-header')
            <!-- app header end -->
            <div class="p-4 mx-auto lg:max-w-[76vw] md:p-6">
                @yield('header')
            </div>
            <div class="p-4 mx-auto lg:max-w-[76vw] md:p-6">
                @yield('content')
            </div>
        </div>

    </div>

</body>
    @fluxScripts
    @livewireScripts
<script>
    
        Livewire.on("{{ Alert::EVENT_INFO }}", (event) => {
            Swal.fire({
                icon: event[0],
                title: event[1],
                text: event[2],
            });
        });

        Livewire.on("{{ Alert::EVENT_CONSOLE_LOG }}", (event) => {
            console.log(event[0])
        });

        Livewire.on("{{ Alert::EVENT_CONFIRMATION }}", (event) => {
            Swal.fire({
                icon: event[0],
                title: event[1],
                text: event[2],
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
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
        Livewire.on("{{ Alert::EVENT_INFORMATION }}", (event) => {
            console.log(event);
            Swal.fire({
                position: "top-end",
                icon: event[0],
                title: event[1],
                showConfirmButton: false,
                timer: 1500
            });
            // Swal.fire({
            //     icon: event[0],
            //     title: event[1],
            //     text: event[2],
            //     showCancelButton: true,
            //     confirmButtonColor: "#3085d6",
            //     cancelButtonColor: "#d33",
            //     confirmButtonText: event[3],
            //     cancelButtonText: event[4],
            // }).then((result) => {
            //     if (result.isConfirmed) {
            //         Livewire.dispatch(event[5]);
            //     } else {
            //         Livewire.dispatch(event[6]);
            //     }
            // });
        });

        Livewire.on('refresh-page', (data) => {
            location.reload();
        });

        Livewire.on('consoleLog', (data) => {
            console.log(data)
        });

        Livewire.on('copy-to-clipboard', (event) => {
            const payload = Array.isArray(event) ? event[0] : event;
            const text = typeof payload === 'string' ? payload : (payload?.text ?? payload?.url);
            const message = payload?.message || 'Berhasil  Copy Link ke Clipboard';

            if (!text) return;

            const showToast = () => {
                Swal.fire({
                    position: "top-end",
                    icon: 'success',
                    title: message,
                    showConfirmButton: false,
                    timer: 2000
                });
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(showToast).catch(() => {
                    const ta = document.createElement('textarea');
                    ta.value = text;
                    ta.style.position = 'fixed';
                    ta.style.left = '-9999px';
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                    showToast();
                });
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToast();
            }
        });

        window.copyToClipboard = function(text, customTitle = 'Berhasil Copy Data!')
        {
            navigator.clipboard.writeText(text)
            .then(() => {
                Swal.fire({
                    position: "top-end",
                    icon: 'success',
                    title: customTitle,
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        }
</script>
@stack('scripts')

</html>
