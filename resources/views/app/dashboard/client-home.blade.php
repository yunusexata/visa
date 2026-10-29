@extends('layouts.client-app')

@section('title', 'Dashboard Saya')

@php
    $hasConfirmation = (bool) $client->confirmation;
    $hasEsign        = (bool) $client->esign;
    $hasDocuments    = $documents->isNotEmpty();
    $hasReview       = (bool) $client->review;

    // Check resolved UPOD Type
    $resolvedUpodType = $client->resolvedUpodType();

    // Check submission guard
    $hasSubmittedUpod = \App\Models\UpodSubmission::where('client_id', $client->id)->exists();

    // Popup trigger condition: all 3 required doc types present
    $hasAllDocuments = $documents->has('ichijikin')
                    && $documents->has('resi')
                    && $documents->has('kwitansi');

    // Show Google Review Popup only if: has all docs, type is google_review, and not submitted yet
    $showReviewPopup = $hasAllDocuments && !$hasSubmittedUpod && ($resolvedUpodType === 'google_review');

    // Show Satisfaction Survey Popup only if: has all docs, type is satisfaction_survey, and not submitted yet
    $showSurveyPopup = $hasAllDocuments && !$hasSubmittedUpod && ($resolvedUpodType === 'satisfaction_survey');
@endphp

@section('content')
{{-- ═══════════════════════════════════════════════════════════════
     ROOT ALPINE COMPONENT — wraps entire page so popup can overlay
     above everything including sticky header
═══════════════════════════════════════════════════════════════ --}}
<div
    id="client-dashboard-root"
    class="min-h-screen bg-[#F0F4FF]"
    x-data="{
        showReviewPopup: false,

        init() {
            @if ($showReviewPopup)
            const dismissKey = 'review_popup_dismissed_{{ $client->id }}';
            const dismissed  = localStorage.getItem(dismissKey);
            if (!dismissed) {
                setTimeout(() => { this.showReviewPopup = true; }, 800);
            }
            @endif
        },

        dismiss() {
            localStorage.setItem('review_popup_dismissed_{{ $client->id }}', '1');
            this.showReviewPopup = false;
        }
    }"
    x-init="init()"
    @close-review-popup.window="showReviewPopup = false; localStorage.removeItem('review_popup_dismissed_{{ $client->id }}')"
>

    {{-- ═══════════════════════════════════════════════════════════
         REVIEW POPUP — blocking overlay
         Visible only when showReviewPopup === true
    ═══════════════════════════════════════════════════════════════ --}}
    <div
        x-show="showReviewPopup"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[999] flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
        style="display: none;"
        {{-- No @click.self dismiss — this is a blocking popup --}}
    >
        {{-- Modal Card --}}
        <div
            x-show="showReviewPopup"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-auto overflow-hidden"
            @click.stop
        >
            {{-- Modal Header strip --}}
            <div class="h-1.5 w-full bg-gradient-to-r from-[#FF4800] via-[#FF6A00] to-[#FF4800]"></div>

            <div class="p-6">
                {{-- ── Star illustration ── --}}
                <div class="flex justify-center mb-4">
                    <div class="relative">
                        {{-- Glow ring --}}
                        <div class="absolute inset-0 rounded-full bg-[#FF4800]/20 blur-xl scale-150"></div>
                        {{-- Star cluster --}}
                        <div class="relative flex items-end gap-0.5">
                            {{-- Left smaller star --}}
                            <svg class="w-8 h-8 fill-[#FFB347] mb-1" viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                            {{-- Center big star --}}
                            <svg class="w-14 h-14 fill-[#FF4800]" viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                            {{-- Right smaller star --}}
                            <svg class="w-8 h-8 fill-[#FFB347] mb-1" viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- ── Heading ── --}}
                <h2 class="text-xl font-bold text-center text-[#001B5E] leading-tight mb-1">
                    Dokumen Anda Sudah Siap! 🎉
                </h2>
                <p class="text-center text-gray-500 text-sm leading-relaxed mb-5">
                    Bagikan pengalaman Anda untuk membantu<br>kami melayani lebih baik.
                </p>

                {{-- ── Divider ── --}}
                <div class="border-t border-gray-100 mb-5"></div>

                {{-- ── Inline Livewire Review Form ── --}}
                {{--
                    We embed the existing ReviewForm Livewire component here.
                    It already handles: rating selection, validation, submit, SwalInformation dispatch,
                    open-new-tab dispatch, and hasSubmitted state.
                    We listen to SwalInformation 'Ulasan Terkirim' to close the popup.
                --}}
                <div id="popup-review-form-wrapper">
                    @livewire('client.review-form', ['popupMode' => true])
                </div>

                {{-- ── Divider ── --}}
                <div class="border-t border-gray-100 mt-5 mb-4"></div>

                {{-- ── Skip link — only escape from blocking popup ── --}}
                <div class="text-center">
                    <button
                        id="btn-popup-skip"
                        type="button"
                        @click="dismiss()"
                        class="text-gray-400 text-xs underline underline-offset-2 hover:text-gray-600 transition-colors"
                    >
                        Lewati untuk saat ini
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- /POPUP --}}

    @if ($showSurveyPopup)
        @livewire('client.satisfaction-survey')
    @endif

    {{-- ═══════════════════════════════════════
         HEADER BAR
    ═══════════════════════════════════════ --}}
    <header class="sticky top-0 z-40 bg-[#001B5E] shadow-lg">
        <div class="flex items-center justify-between px-4 py-3 max-w-lg mx-auto">
            {{-- Brand --}}
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-[#0049FF] flex items-center justify-center shadow-inner">
                    <span class="text-white font-extrabold text-sm tracking-tight">NP</span>
                </div>
                <div>
                    <div class="text-white font-bold text-sm leading-tight">Nenkin Portal</div>
                    <div class="text-[#D7D7D7] text-[10px] leading-tight">by Exata Indonesia</div>
                    {{-- Brand shortform: legal name is PT Sumber Rezeki Exata Indonesia --}}
                </div>
            </div>

            {{-- Name + Logout + Client ID --}}
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <div class="text-white text-xs font-medium leading-tight">{{ Auth::user()->name }}</div>
                    <div class="text-[#D7D7D7] text-[10px] leading-tight">Client ID : {{ $client->no_jp ?? '-' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            id="btn-logout-header"
                            title="Keluar"
                            class="w-8 h-8 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 transition text-white">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                        </svg>
                    </button>
                </form>
            </div>
    </header>

    @if(session('success'))
        <div class="max-w-lg mx-auto px-4 mt-4">
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 3000)"
                 class="bg-green-50 border-l-4 border-green-500 p-4 text-green-700 text-sm rounded-r-lg font-semibold flex items-center justify-between transition-opacity duration-300">
                <span>✅ {{ session('success') }}</span>
                <button @click="show = false" class="text-green-500 hover:text-green-700 ml-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════
         HERO BANNER – Progress Overview
    ═══════════════════════════════════════ --}}
    <section class="bg-gradient-to-br from-[#001B5E] via-[#0028A0] to-[#0049FF] px-4 pt-5 pb-10">
        <div class="max-w-lg mx-auto">
            <p class="text-[#D7D7D7] text-xs font-medium mb-1">Selamat datang kembali,</p>
            <h1 class="text-white text-xl font-extrabold tracking-tight mb-4">{{ $client->name }}</h1>

            {{-- Progress Bar --}}
            <div class="bg-white/10 rounded-2xl p-4 backdrop-blur-sm border border-white/20">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-white text-sm font-semibold">Progress Klaim Nenkin</span>
                    <span class="text-[#FF4800] font-extrabold text-sm">{{ $completedSteps }}/{{ $totalSteps }}</span>
                </div>

                {{-- Progress Track --}}
                @php
                    $progressWidth = 0;
                    if ($completedSteps === 2) {
                        $progressWidth = 50;
                    } elseif ($completedSteps >= 3) {
                        $progressWidth = 100;
                    }
                @endphp
                <div class="w-full h-2 bg-white/20 rounded-full overflow-hidden mb-4">
                    <div class="h-full bg-gradient-to-r from-[#FF4800] to-[#FF7A00] rounded-full transition-all duration-700"
                         style="width: {{ $progressWidth }}%">
                    </div>
                </div>

                {{-- Step Dots --}}
                <div class="flex items-center justify-between">
                    @foreach ($steps as $i => $step)
                        <div class="flex flex-col items-center gap-1 flex-1">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all
                                        {{ $step['done'] ? 'bg-[#FF4800] shadow-md shadow-orange-600/40' : 'bg-white/20 border border-white/30' }}">
                                @if ($step['done'])
                                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                    </svg>
                                @else
                                    <span class="text-white/60 text-xs font-bold">{{ $i + 1 }}</span>
                                @endif
                            </div>
                            <span class="text-[10px] text-center leading-tight
                                         {{ $step['done'] ? 'text-white font-semibold' : 'text-white/50' }}">
                                {{ $step['label'] }}
                            </span>
                        </div>
                        @if (!$loop->last)
                            <div class="h-px flex-1 mx-1 mt-[-14px] {{ $step['done'] ? 'bg-[#FF4800]/60' : 'bg-white/20' }}"></div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Main Content - overlaps hero with card --}}
    <div class="max-w-lg mx-auto px-4 -mt-5 space-y-4">

        {{-- === STEP 1: Belum konfirmasi → tampilkan CTA konfirmasi === --}}
        @unless ($hasConfirmation)
        <div class="bg-white rounded-2xl shadow-md border border-[#0049FF]/20 overflow-hidden">
            <div class="bg-gradient-to-r from-[#0049FF] to-[#001B5E] px-4 py-3 flex items-center gap-2">
                <svg class="w-5 h-5 fill-white flex-shrink-0" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <span class="text-white font-bold text-sm">Aksi Diperlukan</span>
            </div>
            <div class="p-4">
                @livewire('client.form-confirmation')
            </div>
        </div>
        @endunless

        {{-- === STEP 2: Sudah konfirmasi, belum esign → tombol ke esign ATAU status "Proses Klaim" untuk tipe NORMAL === --}}
        @if ($hasConfirmation && !$hasEsign)
            @if ($client->confirmation->claim_type === \App\Models\Client\Client::CLAIM_TYPE_NORMAL)
                @if (!$hasDocuments)
                <div class="bg-white rounded-2xl shadow-md border border-blue-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#001B5E] to-[#0049FF] px-4 py-3 flex items-center gap-2">
                        <svg class="w-5 h-5 fill-white flex-shrink-0" viewBox="0 0 24 24">
                            <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z"/>
                        </svg>
                        <span class="text-white font-bold text-sm">Proses Klaim</span>
                    </div>
                    <div class="p-4 flex flex-col items-center text-center gap-3">
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Proses nenkin kamu masuk ke pencairan <strong>NORMAL</strong>. Dikarenakan kamu sudah berada di Jepang atau akan kembali ke Jepang dalam kurun waktu kurang dari 6 bulan. Kamu akan mendapatkan notifikasi dan pemberitahuan jika nenkin kamu sudah cair. Estimasi pencairan nenkin adalah 3-4 bulan sejak tanggal pencairan 80%.
                        </p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Sedang di proses ke Zeimusho / kantor pajak Jepang
                        </span>
                    </div>
                </div>
                @endif
            @else
                <div class="bg-white rounded-2xl shadow-md border border-[#FF4800]/30 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#FF4800] to-[#FF6A00] px-4 py-3 flex items-center gap-2">
                        <svg class="w-5 h-5 fill-white flex-shrink-0" viewBox="0 0 24 24">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                        <span class="text-white font-bold text-sm">
                            Tanda Tangan Diperlukan
                        </span>
                    </div>
                    <div class="p-4 flex flex-col items-center text-center gap-3">
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Data konfirmasi Anda sudah kami terima. Langkah berikutnya adalah membaca surat perjanjian dan menandatanganinya secara digital.
                        </p>
                        <a href="{{ route('client.esign.show') }}"
                           id="btn-goto-esign"
                           class="inline-flex items-center gap-2 px-6 py-3 bg-[#FF4800] hover:bg-[#e03e00] text-white font-bold rounded-xl shadow-lg shadow-orange-500/30 transition-all active:scale-95 text-sm">
                            <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24">
                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/>
                            </svg>
                            Tanda Tangan Sekarang
                        </a>
                    </div>
                </div>
            @endif
        @endif

        {{-- === STEP 2: Sudah konfirmasi AND sudah esign, belum ada dokumen === --}}
        @if ($hasConfirmation && $hasEsign && !$hasDocuments)
        <div class="bg-white rounded-2xl shadow-md border border-green-200 overflow-hidden">
            <div class="bg-gradient-to-r from-[#001B5E] to-[#0049FF] px-4 py-3 flex items-center gap-2">
                    <svg class="w-5 h-5 fill-white flex-shrink-0" viewBox="0 0 24 24">
                        <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z"/>
                    </svg>
                <span class="text-white font-bold text-sm">Nenkin 20% sedang dalam proses.</span>
            </div>
            <div class="p-4 flex flex-col items-center text-center gap-3">
                <p class="text-gray-600 text-sm leading-relaxed">
                    Estimasi proses pencairan Nenkin adalah 2-3 minggu dari tanggal konfirmasi. Kami akan menghubungi dan memberikan pemberitahuan jika uang Nenkin 20% kamu sudah ditransfer.
                </p>

                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Mohon sabar menunggu
                </span>
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════
             SECTION: STATUS TRACKER (Setelah konfirmasi)
        ═══════════════════════════════════════ --}}
        @if ($hasConfirmation)
        <div id="status" class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-5 h-5 fill-[#0049FF]" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/>
                </svg>
                <h2 class="font-bold text-[#001B5E] text-sm">Status Layanan Anda</h2>
            </div>

            <div class="p-4 space-y-3">
                {{-- Konfirmasi Data --}}
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $hasConfirmation ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }}">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0
                                {{ $hasConfirmation ? 'bg-green-500' : 'bg-gray-300' }}">
                        <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24">
                            <path d="{{ $hasConfirmation ? 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z' : 'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z' }}"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-sm {{ $hasConfirmation ? 'text-green-700' : 'text-gray-500' }}">Konfirmasi Data</div>
                        <div class="text-xs {{ $hasConfirmation ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $hasConfirmation ? 'Selesai — data berhasil dikonfirmasi' : 'Belum dikonfirmasi' }}
                        </div>
                    </div>
                    @if ($hasConfirmation)
                    <svg class="w-5 h-5 fill-green-500 flex-shrink-0" viewBox="0 0 24 24">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                    </svg>
                    @endif
                </div>

                {{-- Tanda Tangan --}}
                @if ($client->confirmation->claim_type !== \App\Models\Client\Client::CLAIM_TYPE_NORMAL)
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $hasEsign ? 'bg-green-50 border border-green-200' : ($hasConfirmation ? 'bg-blue-50 border border-[#0049FF]/30' : 'bg-gray-50 border border-gray-200') }}">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 
                                {{ $hasEsign ? 'bg-green-500' : ($hasConfirmation ? 'bg-[#0049FF]' : 'bg-gray-300') }}">
                        <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24">
                            <path d="{{ $hasEsign ? 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z' : 'M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z' }}"/>
                        </svg>
                    </div>
                    
                    <div class="flex-1">
                        <div class="font-semibold text-sm {{ $hasEsign ? 'text-green-700' : ($hasConfirmation ? 'text-[#0049FF]' : 'text-gray-400') }}">
                            Tanda Tangan Digital
                        </div>
                        <div class="text-xs {{ $hasEsign ? 'text-green-600' : ($hasConfirmation ? 'text-[#0049FF]/70' : 'text-gray-400') }}">
                            @if ($hasEsign)
                                Selesai — ditandatangani pada {{ $client->esign->signed_at?->translatedFormat('d M Y') ?? '-' }}
                            @elseif ($hasConfirmation)
                                Menunggu tanda tangan Anda
                            @else
                                Menunggu konfirmasi data terlebih dahulu
                            @endif
                        </div>
                    </div>

                    {{-- BAGIAN YANG DIPERBARUI: Mengubah ikon menjadi tombol solid --}}
                    @if ($hasEsign)
                        <a href="{{ route('agreement.download',
                        ['client_id' => auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN) ? $client_id : null]) }}" 
                        download
                        class="flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-bold px-3 py-2 rounded-lg transition-colors duration-200 shadow-sm flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            <span>Unduh</span>
                        </a>
                    @endif

                    @if (!$hasEsign && $hasConfirmation)
                        <a href="{{ route('client.esign.show') }}" 
                        id="btn-esign-status" 
                        class="text-[10px] font-bold bg-[#0049FF] text-white px-2 py-1 rounded-lg whitespace-nowrap flex-shrink-0">
                            Tanda Tangan
                        </a>
                    @endif
                </div>
                @endif

                {{-- Dokumen --}}
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $hasDocuments ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }}">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0
                                {{ $hasDocuments ? 'bg-green-500' : 'bg-gray-300' }}">
                        <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24">
                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-sm {{ $hasDocuments ? 'text-green-700' : 'text-gray-400' }}">Kwitansi Pencairan Nenkin Diterima</div>
                        <div class="text-xs {{ $hasDocuments ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $hasDocuments ? $documents->count() . ' dokumen tersedia' : 'Belum ada dokumen' }}
                        </div>
                    </div>
                    @if ($hasDocuments)
                        <svg class="w-5 h-5 fill-green-500 flex-shrink-0" viewBox="0 0 24 24">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                    @endif
                </div>

                {{-- Proses Selesai --}}
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $hasDocuments ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }}">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0
                                {{ $hasDocuments ? 'bg-green-500' : 'bg-gray-300' }}">
                        <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-sm {{ $hasDocuments ? 'text-green-700' : 'text-gray-400' }}">Proses Selesai</div>
                        <div class="text-xs {{ $hasDocuments ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $hasDocuments ? $documents->count() . ' Proses Nenkin Selesai' : 'Masih Dalam Proses' }}
                        </div>
                    </div>
                    @if ($hasDocuments)
                        <svg class="w-5 h-5 fill-green-500 flex-shrink-0" viewBox="0 0 24 24">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════
             SECTION: DOKUMEN
        ═══════════════════════════════════════ --}}
        @if ($hasDocuments)
        <div id="dokumen" class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-5 h-5 fill-[#0049FF]" viewBox="0 0 24 24">
                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                </svg>
                <h2 class="font-bold text-[#001B5E] text-sm">Dokumen Anda</h2>
                <span class="ml-auto text-xs font-semibold bg-[#0049FF]/10 text-[#0049FF] px-2 py-0.5 rounded-full">
                    {{ $documents->count() }} file
                </span>
            </div>

            <div class="p-4 space-y-2">
                @foreach ($documents as $type => $document)
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100 hover:border-[#0049FF]/30 transition-all">
                    <div class="w-9 h-9 rounded-xl bg-[#0049FF]/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 fill-[#0049FF]" viewBox="0 0 24 24">
                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm text-gray-800 truncate">
                            {{ strtoupper(str_replace('_', ' ', $type)) }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ $document->uploaded_at?->translatedFormat('d M Y') ?? 'Baru saja' }}
                        </div>
                    </div>
                    <a href="{{ route('documents.download', [
                    'type' => $type,
                    'client_id' => auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN) ? $client_id : null
                    ]) }}"
                       download
                       id="btn-download-doc-{{ Str::slug($type) }}"
                        class="flex items-center gap-1.5 bg-[#001B5E] hover:bg-[#0049FF] text-white text-xs font-bold px-3 py-2 rounded-lg transition-colors duration-200 shadow-sm flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            <span>Unduh</span>
                        </a>
                    </a>
                </div>
                @endforeach

                {{-- Download agreement jika sudah esign --}}
                @if ($hasEsign)
                <div class="flex items-center gap-3 p-3 bg-orange-50 rounded-xl border border-orange-200">
                    <div class="w-9 h-9 rounded-xl bg-[#FF4800]/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 fill-[#FF4800]" viewBox="0 0 24 24">
                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm text-orange-800">Dokumen Perjanjian — Sudah Ditandatangani</div>
                        <div class="text-xs text-orange-500">Ditandatangani</div>
                    </div>
                    <a href="{{ route('agreement.download', ['client_id' => auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN) ? $client_id : null]) }}"
                       id="btn-download-agreement"
                       class="w-8 h-8 flex items-center justify-center rounded-xl bg-[#FF4800] hover:bg-[#e03e00] text-white transition-all active:scale-95 flex-shrink-0">
                        <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24">
                            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                        </svg>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════
             SECTION: REVIEW FORM – inline fallback di bawah halaman
             (tetap ada sebagai fallback, popup adalah primary UX)
        ═══════════════════════════════════════ --}}
        @if ($hasDocuments && !$hasReview && $resolvedUpodType === 'google_review' && !$hasSubmittedUpod)
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-5 h-5 fill-[#FF4800]" viewBox="0 0 24 24">
                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
                <h2 class="font-bold text-[#001B5E] text-sm">Berikan Ulasan</h2>
            </div>
            <div class="p-4">
                <livewire:client.review-form />
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════
             SECTION: SUDAH ADA REVIEW – Tampilkan bintang
        ═══════════════════════════════════════ --}}
        @if ($hasReview || ($resolvedUpodType === 'satisfaction_survey' && $hasSubmittedUpod))
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="p-5 flex flex-col items-center text-center gap-3">
                <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-8 h-8 fill-green-500" viewBox="0 0 24 24">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-extrabold text-[#001B5E] text-base">Proses Selesai!</h3>
                    <p class="text-gray-500 text-xs mt-1">Terima kasih telah menggunakan layanan Nenkin Portal by PT Sumber Rezeki Exata Indonesia.</p>
                </div>

                @if ($hasReview)
                {{-- Stars --}}
                <div class="flex items-center gap-1 mt-1">
                    @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-7 h-7 {{ $i <= $client->review->rating ? 'fill-[#FF4800]' : 'fill-[#D7D7D7]' }}" viewBox="0 0 24 24">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                    </svg>
                    @endfor
                </div>
                @if ($client->review->message)
                <p class="text-gray-600 text-sm italic">"{{ $client->review->message }}"</p>
                @endif
                @endif
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════
             INFO BOX – Kontak CS
         ═══════════════════════════════════════ --}}
        <div class="bg-gradient-to-r from-[#001B5E] to-[#0049FF] rounded-2xl p-4 flex items-center gap-4 shadow-lg shadow-[#001B5E]/20">
            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 fill-white" viewBox="0 0 24 24">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 12h-2v-2h2v2zm0-4h-2V6h2v4z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-white font-bold text-sm">Butuh Bantuan?</div>
                <div class="text-white/70 text-xs">Hubungi CS kami via WhatsApp</div>
            </div>
            <a href="https://wa.me/{{ config('exata.admin_wa_number') }}"
               id="btn-wa-cs"
               target="_blank"
               class="flex-shrink-0 flex items-center gap-1.5 bg-[#FF4800] hover:bg-[#e03e00] text-white text-xs font-bold px-3 py-2 rounded-xl transition-all active:scale-95">
                <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                Chat CS
            </a>
        </div>

        {{-- Footer --}}
        <div class="text-center text-xs text-gray-400 py-4">
            &copy; {{ date('Y') }} PT Sumber Rezeki Exata Indonesia. Seluruh hak dilindungi.
        </div>

    </div>{{-- /max-w-lg --}}
</div>{{-- /#client-dashboard-root --}}
@endsection

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {

        // ── SwalInformation: close popup after successful review submit ──
        Livewire.on('SwalInformation', (event) => {
            const title = Array.isArray(event) ? event[1] : (event?.title || '');

            // "Ulasan Terkirim" is dispatched by ReviewForm.php after submit()
            // Use a custom DOM event so Alpine catches it via @close-review-popup.window
            if (title === 'Ulasan Terkirim') {
                window.dispatchEvent(new CustomEvent('close-review-popup'));
            }

            // Reload after confirmation save
            if (title === 'Data berhasil disimpan' || title === 'Survei Terkirim') {
                setTimeout(() => { location.reload(); }, 1600);
            }
        });

        // ── open-new-tab: dispatched by ReviewForm for Google Review redirect ──
        Livewire.on('open-new-tab', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            if (data && data.url) {
                window.open(data.url, '_blank');
            }
        });

    });
</script>
@endpush
