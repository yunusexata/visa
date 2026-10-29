@extends('layouts.fullscreen-layout')

@section('content')
    <div class="relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900">
        <div class="relative flex h-screen w-full flex-col justify-center sm:p-0 lg:flex-row dark:bg-gray-900">
            <!-- Form -->
            <div class="flex w-full flex-1 flex-col lg:w-1/2">
                <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
                    <div>
                        <div class="mb-5 sm:mb-8">
                            <h1 class="text-title-sm sm:text-title-md mb-2 font-semibold text-gray-800 dark:text-white/90">
                                Lupa Password
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Masukkan email Anda untuk menerima link reset password.
                            </p>
                        </div>

                        @if (session('status'))
                            <div class="mb-4 rounded-lg bg-success-50 p-4 text-sm text-success-600 dark:bg-success-500/10 dark:text-success-400">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div>
                            <form action="{{ route('password.email') }}" method="POST" novalidate="novalidate">
                                @csrf
                                <div class="space-y-5">
                                    <!-- Email -->
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            Email<span class="text-error-500">*</span>
                                        </label>
                                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="info@gmail.com"
                                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30
                                            @error('email') border-red-500 focus:ring-red-500 @enderror" required />
                                            @error('email')
                                                <p class="text-red-500 text-xs mt-1 font-medium">
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                    </div>
                                    <!-- Button -->
                                    <div>
                                        <button type="submit"
                                            class="bg-brand-orange shadow-theme-xs hover:bg-brand-orange/90 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-bold text-white transition">
                                            Kirim Link Reset
                                        </button>
                                    </div>

                                    <div class="text-center mt-4">
                                        <a href="{{ route('login') }}" class="text-sm font-bold text-brand-blue hover:underline">
                                            Kembali ke Login
                                        </a>
                                    </div>
                                </div>
                             </form>
                         </div>
                     </div>
                 </div>
             </div>

             @php
                 $waNumber = config('exata.admin_wa_number');
                 $waLink = preg_replace('/[^0-9]/', '', $waNumber);
                 if (strpos($waLink, '0') === 0) {
                     $waLink = '62' . substr($waLink, 1);
                 }
             @endphp
             <div class="bg-brand-navy relative hidden h-full w-full items-center lg:flex lg:w-1/2 justify-center p-12">
                 <div class="z-1 flex flex-col items-center justify-center text-center max-w-md">
                     <div class="mb-6">
                         <h1 class="text-white font-black text-5xl tracking-tight uppercase">Nenkin Portal</h1>
                         <p class="text-[#0049FF] font-bold text-lg mt-1">by Exata Indonesia</p>
                         {{-- Brand shortform: legal name is PT Sumber Rezeki Exata Indonesia --}}
                     </div>
                     
                     <p class="text-white text-base leading-relaxed mb-12">
                         "Platform resmi pencairan dana pensiun Jepang (Nenkin) untuk Kenshusei"
                     </p>
                     
                     <div class="mt-auto pt-6 border-t border-white/10 w-full flex flex-col items-center">
                         <span class="text-white/60 text-xs uppercase tracking-wider font-semibold">Butuh Bantuan? Hubungi WhatsApp CS</span>
                         <a href="https://wa.me/{{ $waLink }}" target="_blank" class="text-brand-orange hover:underline font-bold text-lg mt-1 flex items-center gap-1.5">
                             <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                 <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.665.989 3.3 1.489 4.965 1.493 5.485.002 9.948-4.46 9.95-9.95.002-2.657-1.03-5.158-2.905-7.034C16.824 1.786 14.325.755 11.666.755c-5.487 0-9.95 4.461-9.952 9.953-.001 1.792.483 3.548 1.401 5.12L2.094 22.07l6.553-1.717z"/>
                             </svg>
                             {{ $waNumber }}
                         </a>
                     </div>
                 </div>
             </div>
         </div>
     </div>
@endsection
