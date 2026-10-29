<!DOCTYPE html>
<html class="light" lang="en">
   <head>
      <meta charset="utf-8"/>
      <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
      <title>DocuStudio | Document Management</title> 
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@700&display=swap" rel="stylesheet">
      <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
     
      <link rel="stylesheet" href="{{ asset('css/app.css') }}">
     <style>
         .material-symbols-outlined {
         font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
         vertical-align: middle;
         }
         .thumbnail-aspect {
         aspect-ratio: 3/4;
         }
         .custom-scrollbar::-webkit-scrollbar {
         width: 4px;
         }
         .custom-scrollbar::-webkit-scrollbar-track {
         background: transparent;
         }
         .custom-scrollbar::-webkit-scrollbar-thumb {
         background: #e1e3e4;
         border-radius: 10px;
         }
      </style>
   </head>
   <body class="bg-background font-bodymd text-on-background">
      <!-- Main Workspace Container -->
      <main class="min-h-screen pb-xl rounded-lg">
         <!-- Sticky Global Action Area -->
         <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-outline-variant px-margin py-md">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-md">
               <div class="flex flex-col">
                  <div class="flex items-center gap-sm">
                     <h1 class="font-h1 text-h2 text-on-surface">Document Management</h1>
                     <span class="bg-primary-container text-on-primary-container px-sm py-xs rounded text-label-caps uppercase tracking-wider">Workspace</span>
                  </div>
                  <div class="mt-sm flex items-center gap-md">
                     <div class="flex items-center gap-sm min-w-[200px]">
                        <div class="flex-1 h-1.5 bg-surface-container-high rounded-full overflow-hidden">
                           <div class="h-full bg-primary w-[75%] rounded-full"></div>
                        </div>
                        <span class="text-body-sm font-medium text-secondary">6/8 Documents</span>
                     </div>
                  </div>
               </div>
               <div class="flex items-center gap-md">
                  <button class="px-lg py-sm text-on-surface font-medium hover:bg-surface-container-low rounded-xl transition-colors">
                  Discard Changes
                  </button>
                  <button class="bg-primary-container hover:bg-primary-fixed-dim text-on-primary-fixed-variant px-xl py-sm rounded-xl font-semibold shadow-sm transition-all active:scale-95 flex items-center gap-sm">
                  <span class="material-symbols-outlined text-[20px]">save</span>
                  Save All Documents
                  </button>
               </div>
            </div>
         </header>
         <!-- Document Grid -->
         <div class="max-w-7xl mx-auto px-margin mt-xl">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-xl">
               <!-- Card: Kertas Gensen -->
               <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-md shadow-sm flex flex-col gap-md group hover:border-primary-container transition-colors">
                  <div class="flex items-start justify-between">
                     <div>
                        <h2 class="font-h2 text-body-lg text-on-surface">Kertas Gensen</h2>
                        <p class="text-body-sm text-secondary">Annual income report document</p>
                     </div>
                     <span class="bg-error-container text-on-error-container px-sm py-xs rounded text-label-caps">REQUIRED</span>
                  </div>
                  <!-- Upload Zone -->
                  <div class="border-2 border-dashed border-outline-variant hover:border-primary hover:bg-primary-fixed/10 transition-all cursor-pointer rounded-lg p-md flex flex-col items-center justify-center gap-sm text-center">
                     <span class="material-symbols-outlined text-primary text-3xl">upload_file</span>
                     <div class="text-body-sm">
                        <span class="text-primary font-semibold">Click to upload</span> or drag and drop
                     </div>
                  </div>
                  <!-- File Previews -->
                  <div class="grid grid-cols-2 gap-sm">
                                       <a
                                       data-fslightbox="test"
                  data-type="iframe"
                                       href="#vimeo"
                                       class="relative thumbnail-aspect bg-surface-container rounded-lg overflow-hidden group/thumb">
                                          <img class="w-full h-full object-cover" data-alt="Clean minimal black and white document photograph with high contrast and sharp text detail" 
                                          src="https://lh3.googleusercontent.com/aida-public/AB6AXuBx3Uwh6V9_lGF6yCknwHb0VVtfegZNV13WbBgsUZQwFU0HYK3ZfcsWBKtox2QEg9amSByvmL3XWWkuftRwuxUC7uaSkd4nfe60pcu4EiF7kpl42PWQNHinO658CMmRfvZwR_B2HJz1eezD5tJ1WJZmo8DENcwW7LWgriaACjtTCUJqCLeBAms_2O9ZYoKKlRz9xHFzZXChgEuQLkOEt7BRAPy-50UfqHgfOTcj1ngJyWUX37fq1Q3XH2cm98yICJeZx1aVxvBvJA"/>
                                          <div class="absolute inset-0 bg-on-surface/40 opacity-0 group-hover/thumb:opacity-100 transition-opacity flex items-center justify-center gap-xs">
                                             <button class="p-xs bg-white rounded-full text-on-surface hover:text-primary"><span class="material-symbols-outlined">visibility</span></button>
                                             <button class="p-xs bg-white rounded-full text-on-surface hover:text-primary"><span class="material-symbols-outlined">download</span></button>
                                             <button class="p-xs bg-white rounded-full text-on-surface hover:text-error"><span class="material-symbols-outlined">delete</span></button>
                                          </div>
                                       </a>
                                       
                                          <div style="position:absolute; left:-9999px; top:-9999px;">
                     <iframe
                           src="http://localhost:8000/gensen/attachment/6/preview?expires=1777104201&signature=759864e8f82b3761a9b9045ff47a2750dcd9c0595b13cde8372dbe9d6b48bcf6"
                        id="vimeo"
                        width="1920"
                        height="1080"
                        frameborder="0"
                     ></iframe>
                  </div>s
                     <a data-fslightbox="{{rand()}}" href="https://lh3.googleusercontent.com/aida-public/AB6AXuDcJ6UdvkMc1LKahr46UCusFOPZT3zhtHkGYfLHRdCuNGeTEQRWk4bIzc3wT-Ajvs40cmrIt6rUaqNXVX_9EAXtj0f4qFXTV6VW4cKedVkuZNBSIJP3A7JbCCuF64r4WfItHRcjWQ7vSXmN_n4q9cjZrtGC1x-97p9ehzR-gYj7OCdO4Wh8Xnr_owlUwgxBmua-DbsDErsRJk39cSe7T6LZpIKySIicfVg8-6qKFtnq5tF9GUqU6fcdeCDgMejJ2hTcQjjIwsXQ6A" class="relative thumbnail-aspect bg-surface-container rounded-lg overflow-hidden group/thumb">
                        <img class="w-full h-full object-cover" data-alt="Professional scan of a tax document on a clean desk background with soft office lighting" 
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuDcJ6UdvkMc1LKahr46UCusFOPZT3zhtHkGYfLHRdCuNGeTEQRWk4bIzc3wT-Ajvs40cmrIt6rUaqNXVX_9EAXtj0f4qFXTV6VW4cKedVkuZNBSIJP3A7JbCCuF64r4WfItHRcjWQ7vSXmN_n4q9cjZrtGC1x-97p9ehzR-gYj7OCdO4Wh8Xnr_owlUwgxBmua-DbsDErsRJk39cSe7T6LZpIKySIicfVg8-6qKFtnq5tF9GUqU6fcdeCDgMejJ2hTcQjjIwsXQ6A"/>
                        <div class="absolute inset-0 bg-on-surface/40 opacity-0 group-hover/thumb:opacity-100 transition-opacity flex items-center justify-center gap-xs">
                           <button class="p-xs bg-white rounded-full text-on-surface hover:text-primary"><span class="material-symbols-outlined">visibility</span></button>
                           <button class="p-xs bg-white rounded-full text-on-surface hover:text-primary"><span class="material-symbols-outlined">download</span></button>
                           <button class="p-xs bg-white rounded-full text-on-surface hover:text-error"><span class="material-symbols-outlined">delete</span></button>
                        </div>
                     </a>
                     <a data-fslightbox="{{rand()}}" href="https://lh3.googleusercontent.com/aida-public/AB6AXuDcJ6UdvkMc1LKahr46UCusFOPZT3zhtHkGYfLHRdCuNGeTEQRWk4bIzc3wT-Ajvs40cmrIt6rUaqNXVX_9EAXtj0f4qFXTV6VW4cKedVkuZNBSIJP3A7JbCCuF64r4WfItHRcjWQ7vSXmN_n4q9cjZrtGC1x-97p9ehzR-gYj7OCdO4Wh8Xnr_owlUwgxBmua-DbsDErsRJk39cSe7T6LZpIKySIicfVg8-6qKFtnq5tF9GUqU6fcdeCDgMejJ2hTcQjjIwsXQ6A" class="relative thumbnail-aspect bg-surface-container rounded-lg overflow-hidden group/thumb">
                        <img class="w-full h-full object-cover" data-alt="Professional scan of a tax document on a clean desk background with soft office lighting" 
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuDcJ6UdvkMc1LKahr46UCusFOPZT3zhtHkGYfLHRdCuNGeTEQRWk4bIzc3wT-Ajvs40cmrIt6rUaqNXVX_9EAXtj0f4qFXTV6VW4cKedVkuZNBSIJP3A7JbCCuF64r4WfItHRcjWQ7vSXmN_n4q9cjZrtGC1x-97p9ehzR-gYj7OCdO4Wh8Xnr_owlUwgxBmua-DbsDErsRJk39cSe7T6LZpIKySIicfVg8-6qKFtnq5tF9GUqU6fcdeCDgMejJ2hTcQjjIwsXQ6A"/>
                        <div class="absolute inset-0 bg-on-surface/40 opacity-0 group-hover/thumb:opacity-100 transition-opacity flex items-center justify-center gap-xs">
                           <button class="p-xs bg-white rounded-full text-on-surface hover:text-primary"><span class="material-symbols-outlined">visibility</span></button>
                           <button class="p-xs bg-white rounded-full text-on-surface hover:text-primary"><span class="material-symbols-outlined">download</span></button>
                           <button class="p-xs bg-white rounded-full text-on-surface hover:text-error"><span class="material-symbols-outlined">delete</span></button>
                        </div>
                     </a>
                  </div>
               </div>
               <!-- Card: Rekap Pengiriman Uang -->
               <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-md shadow-sm flex flex-col gap-md group hover:border-primary-container transition-colors">
                  <div class="flex items-start justify-between">
                     <div>
                        <h2 class="font-h2 text-body-lg text-on-surface">Rekap Pengiriman</h2>
                        <p class="text-body-sm text-secondary">Remittance record summary</p>
                     </div>
                     <span class="bg-secondary-container text-on-secondary-container px-sm py-xs rounded text-label-caps">OPTIONAL</span>
                  </div>
                  <div class="border-2 border-dashed border-outline-variant hover:border-primary hover:bg-primary-fixed/10 transition-all cursor-pointer rounded-lg p-md flex flex-col items-center justify-center gap-sm text-center">
                     <span class="material-symbols-outlined text-primary text-3xl">add_circle</span>
                     <div class="text-body-sm">Add files</div>
                  </div>
                  <div class="flex items-center justify-center h-40 bg-surface-container-low rounded-lg border border-outline-variant border-dashed">
                     <p class="text-body-sm text-secondary italic">No documents uploaded</p>
                  </div>
               </div>
               <!-- Card: Kartu Keluarga -->
               <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-md shadow-sm flex flex-col gap-md group hover:border-primary-container transition-colors">
                  <div class="flex items-start justify-between">
                     <div>
                        <h2 class="font-h2 text-body-lg text-on-surface">Kartu Keluarga</h2>
                        <p class="text-body-sm text-secondary">Family registration certificate</p>
                     </div>
                     <span class="bg-error-container text-on-error-container px-sm py-xs rounded text-label-caps">REQUIRED</span>
                  </div>
                  <div class="border-2 border-dashed border-outline-variant hover:border-primary hover:bg-primary-fixed/10 transition-all cursor-pointer rounded-lg p-md flex flex-col items-center justify-center gap-sm text-center">
                     <span class="material-symbols-outlined text-primary text-3xl">upload_file</span>
                     <div class="text-body-sm"><span class="text-primary font-semibold">Upload</span> new copy</div>
                  </div>
                  <div class="grid grid-cols-2 gap-sm">
                     <div class="relative thumbnail-aspect bg-surface-container rounded-lg overflow-hidden group/thumb">
                        <img class="w-full h-full object-cover" data-alt="Close up view of a legal paper document with official stamps in a brightly lit environment" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA-ABHY0spSy7Jpykpd5Weiqe_bvWqQv0IBH_GP2Ssy3Y8PXUHR3eP50ARK8LEcHapJBeFnBsnPllxmGtxT_uIIasI4oeIBIc8GnQ9LZ73a4RuWuyXkweqizHG6tBZqY8XptVyy7jFZJryABHexVK2LgfOAXz9K84b2z3-myUJKlbOLaEa3icH4OxXmvl1i3VmXT_-lymsAeaeVuUhA1SYtA1RRIqvLU8L1zGuw1UddydU2j1S-VAcPUlxtuyTpoLWvnUa0mC-KrA"/>
                        <div class="absolute inset-0 bg-on-surface/40 opacity-0 group-hover/thumb:opacity-100 transition-opacity flex items-center justify-center gap-xs">
                           <button class="p-xs bg-white rounded-full text-on-surface hover:text-primary"><span class="material-symbols-outlined">visibility</span></button>
                           <button class="p-xs bg-white rounded-full text-on-surface hover:text-error"><span class="material-symbols-outlined">delete</span></button>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- Card: Zairyou Card (Front/Back) -->
               <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-md shadow-sm flex flex-col gap-md group hover:border-primary-container transition-colors">
                  <div class="flex items-start justify-between">
                     <div>
                        <h2 class="font-h2 text-body-lg text-on-surface">Zairyou Card</h2>
                        <p class="text-body-sm text-secondary">Residence card (Front &amp; Back)</p>
                     </div>
                     <span class="bg-error-container text-on-error-container px-sm py-xs rounded text-label-caps">REQUIRED</span>
                  </div>
                  <div class="grid grid-cols-2 gap-sm">
                     <div class="flex flex-col gap-xs">
                        <span class="text-label-caps text-[10px] text-secondary">FRONT SIDE</span>
                        <div class="relative aspect-video bg-surface-container rounded-lg overflow-hidden group/thumb">
                           <img class="w-full h-full object-cover" data-alt="Microchip texture close up representing identification card security features in cool blue lighting" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBsL5spKkRLjZxCphW08eRMNA0bfEZfevq5JoONgc78_P3wXr0OMwJXj9MrQVsKyts82OoN5IEFbJrqS-2rNOxVigeD9JHBO-tZam3Zo6umVyGUngCxVjVDNRahTa3PpahmN-BXEHmj53lGp4mI5egqjwuoZcn2NqXYRoaVrGP5ShsyLMJpFNJgl2yKh3RvKXQCTM0_OcP0aZaGG9dBguSkinYG6N1HdunlhW46A7ITRjZGbirriR4N8pCSvUBdhOvTeHXHFcN3sg"/>
                           <div class="absolute inset-0 bg-on-surface/40 opacity-0 group-hover/thumb:opacity-100 transition-opacity flex items-center justify-center gap-xs">
                              <button class="p-xs bg-white rounded-full text-on-surface hover:text-primary"><span class="material-symbols-outlined">refresh</span></button>
                              <button class="p-xs bg-white rounded-full text-on-surface hover:text-error"><span class="material-symbols-outlined">delete</span></button>
                           </div>
                        </div>
                     </div>
                     <div class="flex flex-col gap-xs">
                        <span class="text-label-caps text-[10px] text-secondary">BACK SIDE</span>
                        <div class="relative aspect-video bg-surface-container rounded-lg overflow-hidden group/thumb">
                           <img class="w-full h-full object-cover" data-alt="Reverse side of a clean white credit card with magnetic strip and signature area" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDYIH5-Agt8jSD7NSf-LHtk62N6t1jXxeYhrt9sXncrdxXjoYKqlnQO143jkKu0J0IqOUw6Oyrm6Ut_yrWdQPm25o3xyEvV6mmkCuDzkkBrJ7jnxFHbvxS4LBuFb0u8j9VNdUB15iHNO0d0kRQtkGUKdDIiZYNis1-eWmRH5NN88ecxb70udkJaWa_XFqRTGKHCCwEr5u7Gj6sFgdON2zei0cES1Pwpd1P-8SoLTDZ2Qta0FTMNF9ymZrLK8Jrn8qgn64Ban4jFsw"/>
                           <div class="absolute inset-0 bg-on-surface/40 opacity-0 group-hover/thumb:opacity-100 transition-opacity flex items-center justify-center gap-xs">
                              <button class="p-xs bg-white rounded-full text-on-surface hover:text-primary"><span class="material-symbols-outlined">refresh</span></button>
                              <button class="p-xs bg-white rounded-full text-on-surface hover:text-error"><span class="material-symbols-outlined">delete</span></button>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="border-2 border-dashed border-outline-variant hover:border-primary hover:bg-primary-fixed/10 transition-all cursor-pointer rounded-lg p-sm flex items-center justify-center gap-sm">
                     <span class="material-symbols-outlined text-primary">add</span>
                     <span class="text-body-sm">Replace images</span>
                  </div>
               </div>
               <!-- Card: My Number (Front/Back) -->
               <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-md shadow-sm flex flex-col gap-md group hover:border-primary-container transition-colors">
                  <div class="flex items-start justify-between">
                     <div>
                        <h2 class="font-h2 text-body-lg text-on-surface">My Number</h2>
                        <p class="text-body-sm text-secondary">Individual number card</p>
                     </div>
                     <span class="bg-error-container text-on-error-container px-sm py-xs rounded text-label-caps">REQUIRED</span>
                  </div>
                  <div class="grid grid-cols-2 gap-sm">
                     <div class="flex flex-col gap-xs">
                        <span class="text-label-caps text-[10px] text-secondary uppercase">Front</span>
                        <div class="aspect-video bg-surface-container-low rounded-lg border border-dashed border-outline-variant flex items-center justify-center">
                           <span class="material-symbols-outlined text-secondary">add_photo_alternate</span>
                        </div>
                     </div>
                     <div class="flex flex-col gap-xs">
                        <span class="text-label-caps text-[10px] text-secondary uppercase">Back</span>
                        <div class="aspect-video bg-surface-container-low rounded-lg border border-dashed border-outline-variant flex items-center justify-center">
                           <span class="material-symbols-outlined text-secondary">add_photo_alternate</span>
                        </div>
                     </div>
                  </div>
                  <button class="w-full bg-primary text-white py-sm rounded-lg text-body-sm font-semibold hover:bg-on-primary-container transition-colors">
                  Upload Card
                  </button>
               </div>
               <!-- Card: Rekening Indonesia -->
               <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-md shadow-sm flex flex-col gap-md group hover:border-primary-container transition-colors">
                  <div class="flex items-start justify-between">
                     <div>
                        <h2 class="font-h2 text-body-lg text-on-surface">Rekening Indonesia</h2>
                        <p class="text-body-sm text-secondary">Indonesian bank account book</p>
                     </div>
                     <span class="bg-secondary-container text-on-secondary-container px-sm py-xs rounded text-label-caps">OPTIONAL</span>
                  </div>
                  <div class="relative thumbnail-aspect bg-surface-container rounded-lg overflow-hidden group/thumb max-w-[160px] mx-auto">
                     <img class="w-full h-full object-cover" data-alt="A clean bank statement with blue accents and professional typography on premium paper" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCydTpEznDJ0u_PKye97ujOGvzo5DomBMi8fK0LGUrYqNy_gWUyCzNcj6BO3CUB4mAA8FcEn8zpaQ32-s18f3o3pPal1LUX5FS834UICQXgEePRjbG9zjEykwwlaSvzbwwoWg96jfjE0Cu8gWIj1JzGQam7XZzpSah6K4c27aCU2SBaevbv1MlMX9cdF0LTyFCB9Trbnlb2gGqoyO6B69t29TcoyYSFq7YUeH_XjJNZWQOfky_ZlYPZhoEFzPZjuXrygXzO8uaRLQ"/>
                     <div class="absolute inset-0 bg-on-surface/40 opacity-0 group-hover/thumb:opacity-100 transition-opacity flex items-center justify-center gap-xs">
                        <button class="p-xs bg-white rounded-full text-on-surface hover:text-primary"><span class="material-symbols-outlined">visibility</span></button>
                        <button class="p-xs bg-white rounded-full text-on-surface hover:text-error"><span class="material-symbols-outlined">delete</span></button>
                     </div>
                  </div>
                  <div class="mt-auto pt-sm flex items-center justify-between border-t border-surface-container-high">
                     <span class="text-body-sm font-medium text-on-surface">bank_account_01.pdf</span>
                     <span class="text-[10px] text-secondary">1.2 MB</span>
                  </div>
               </div>
            </div>
         </div>
         <!-- Sticky Footer Note (Floating Action Style) -->
         <!-- <div class="fixed bottom-lg left-1/2 -translate-x-1/2 z-40 bg-on-surface text-white px-xl py-md rounded-2xl shadow-xl flex items-center gap-md">
            <span class="material-symbols-outlined text-primary-container">info</span>
            <p class="text-body-sm font-medium">All uploads are encrypted and secure. Supported: PDF, JPG, PNG (Max 10MB)</p>
            <button class="ml-xl bg-white/10 hover:bg-white/20 p-xs rounded-full"><span class="material-symbols-outlined">close</span></button>
         </div> -->
      </main>

   {{-- <script src="{{ asset('assets/plugins/custom/fslightbox-basic-3.5.1/fslightbox.js') }}"></script> --}}
   <script src="https://cdn.jsdelivr.net/npm/fslightbox/index.js"></script>
   </body>
</html>