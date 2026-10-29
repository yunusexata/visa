<!DOCTYPE html>
<html class="light" lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>@yield('title', env('APP_NAME'))</title>
      <!-- Standard favicon -->
      <link rel="icon" type="image/png" sizes="32x32" href="{{ asset(config('template.logo_panel')) }}">
      <link rel="icon" href="{{ asset(config('template.logo_panel')) }}" type="image/x-icon">
      
      <!-- Apple devices -->
      <link rel="apple-touch-icon" sizes="180x180" href="{{ asset(config('template.logo_panel')) }}">

      <!-- Shortcut icon -->
      <link rel="shortcut icon" href="{{ asset(config('template.logo_panel')) }}">
      <!--begin::Fonts(mandatory for all pages)-->
      <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&amp;family=Work+Sans:wght@600;700;800&amp;display=swap" rel="stylesheet"/>
      <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
      
        <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->

        <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
        <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet" type="text/css" />
      <style>
         .material-symbols-outlined {
         font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
         }
         .form-input-focus:focus {
         outline: none;
         border-color: #00629e;
         box-shadow: 0 0 0 4px rgba(207, 229, 255, 0.5);
         }
      </style>
    @vite(['resources/js/app.js'])
    @livewireStyles

    @stack('css')

      <style>
         .material-symbols-outlined {
         font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
         }
         body { font-family: 'Inter', sans-serif; }
         h1, h2, h3 { font-family: 'Manrope', sans-serif; }
         .no-scrollbar::-webkit-scrollbar { display: none; }
         .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
      </style>
   </head>

<body id="kt_app_body" data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="false"
    data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    

    <!--begin::App-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="mb-20" id="home">
           <div class="bgi-no-repeat bgi-size-contain bgi-position-x-center bgi-position-y-bottom landing-light-bg">
              
              <div class="mb-n10 mb-lg-n20 z-index-2 mt-10 pb-10">
                  <!--begin::Container-->
                  <div class="container"> 
                     @yield('content')
                  </div>
              </div>
           </div>
        </div>
        <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
           <span class="svg-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                 <rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor"></rect>
                 <path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor"></path>
              </svg>
           </span>
        </div>
     </div>
    <!--end::App-->

{{-- </body>
   <body class="bg-slate-50 font-body text-on-surface flex items-center justify-center min-h-screen p-4 md:p-8"> --}}
      <!-- Form Container (Centered Card) -->
      

        <!--begin::Javascript-->
        <!--begin::Global Javascript Bundle(mandatory for all pages)-->
        <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
        <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
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

            Livewire.on('refresh-page', (data) => {
                location.reload();
            });

            Livewire.on('consoleLog', (data) => {
                console.log(data)
            });
        </script>
        @stack('js')
        <!--end::Javascript-->
   </body>
</html>