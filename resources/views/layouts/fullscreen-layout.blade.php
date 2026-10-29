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
    
</head>

<body x-data="{ 'loaded': true}" x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
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

    @yield('content')

</body>

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
        window.copyToClipboard = function(text)
        {
            navigator.clipboard.writeText(text)
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Copy Link Form Kandidat!',
                });
            });
        }
    </script>
@stack('scripts')

</html>
