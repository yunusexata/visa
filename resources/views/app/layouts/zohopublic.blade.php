<html>
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
         
    <!--begin::Vendor Stylesheets(used for this page only)-->
    <link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <!--end::Vendor Stylesheets-->

    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/form.css') }}" rel="stylesheet" type="text/css" />
      
      @livewireStyles

      @stack('css')
      <style>
        .sm-d-none {
            display: none !important;
        }
        .lg-d-none {
            display: none !important;
        }

        @media (min-width: 575.98px) {
            .sm-d-none {
                display: block !important;
            }
        }

        @media (max-width: 575.98px) {
            .lg-d-none {
                display: block !important;
            }
        }

       .overlay:hover .img-detail {
          filter: brightness(0.7);
       }
 
       .overlay:hover .eye-button {
          filter: none !important;
       }
       /* Bootstrap 5 CSS and icons included */
        :root {
        --colorPrimaryNormal: #00b3bb;
        --colorPrimaryDark: #00979f;
        --colorPrimaryGlare: #00cdd7;
        --colorPrimaryHalf: #80d9dd;
        --colorPrimaryQuarter: #bfecee;
        --colorPrimaryEighth: #dff5f7;
        --colorPrimaryPale: #f3f5f7;
        --colorPrimarySeparator: #f3f5f7;
        --colorPrimaryOutline: #dff5f7;
        --colorButtonNormal: #00b3bb;
        --colorButtonHover: #00cdd7;
        --colorLinkNormal: #00979f;
        --colorLinkHover: #00cdd7;
        }

        .upload_dropZone {
        color: #0f3c4b;
        background-color: var(--colorPrimaryPale, #c8dadf);
        outline: 2px dashed var(--colorPrimaryHalf, #c1ddef);
        outline-offset: -12px;
        transition:
            outline-offset 0.2s ease-out,
            outline-color 0.3s ease-in-out,
            background-color 0.2s ease-out;
        }
        .upload_dropZone.highlight {
        outline-offset: -4px;
        outline-color: var(--colorPrimaryNormal, #0576bd);
        background-color: var(--colorPrimaryEighth, #c8dadf);
        }
        .upload_svg {
        fill: var(--colorPrimaryNormal, #0576bd);
        }
        .btn-upload {
        color: #fff;
        background-color: var(--colorPrimaryNormal);
        }
        .btn-upload:hover,
        .btn-upload:focus {
        color: #fff;
        background-color: var(--colorPrimaryGlare);
        }
        .upload_img {
        width: calc(33.333% - (2rem / 3));
        object-fit: contain;
        }

    </style>
   </head>
   <body class="zf-backgroundBg">
      <!-- Change or deletion of the name attributes in the input tag will lead to empty values on record submission-->
      <div class="zf-templateWidth">
         @yield('content')
      </div>
      
    <!--begin::Javascript-->
    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    @livewireScripts
    <script>
        
        document.addEventListener('livewire:init', () => {
            
            Livewire.on("{{ Alert::EVENT_INFO }}", (event) => {
                Swal.fire({
                    icon: event[0],
                    title: event[1],
                    html: event[2],
                }).then((result) => {
                if(event[3])
                {
                    Livewire.dispatch(event[3]);
                }
                });;
            });

            Livewire.on("{{ Alert::EVENT_CONFIRMATION }}", (event) => {
                Swal.fire({
                    icon: event[0],
                    title: event[1],
                    html: event[2],
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
        });
    </script>
    @stack('js')
    <!--end::Javascript-->
   </body>