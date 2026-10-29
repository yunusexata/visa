@extends('layouts.app')

@section('content')
<div class="container mx-auto py-4 sm:py-8 px-4 animate-fade-in" 
     x-data="esignApp(
         {{ Js::from($nik) }}, 
         {{ Js::from($address) }}, 
         {{ Js::from(Str::of($client->name)->replaceMatches('/[^A-Za-z0-9\s]/u', '')->squish()->toString()) }}, 
         {{ Js::from($letterNumber) }}, 
         {{ Js::from($dateFormatted) }}, 
         {{ Js::from(\Carbon\Carbon::parse($client->dob)->format('d-m-Y')) }}
     )">
    
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
            <p class="font-bold mb-1">Terjadi kesalahan saat menyimpan:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form POST Utama --}}
    <form action="{{ route('client.esign.store') }}" method="POST" id="esignForm" @submit.prevent="submitForm">
        @csrf
        <input type="hidden" name="nik" x-model="nik">
        <input type="hidden" name="address" x-model="address">
        <input type="hidden" name="signature_data" x-model="signature_data">

        {{-- STATE LOADING --}}
        <div x-show="isSubmitting" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 py-16 text-center max-w-xl mx-auto mt-12">
            <div class="inline-block w-12 h-12 border-4 border-gray-200 border-t-[#F97316] rounded-full animate-spin mb-4"></div>
            <h3 class="text-xl font-bold text-gray-800">Memproses Tanda Tangan...</h3>
            <p class="text-sm text-gray-500 mt-2">Harap tunggu sebentar, kami sedang mengamankan dokumen perjanjian Anda.</p>
        </div>

        {{-- KONTEN UTAMA (TIDAK SEDANG LOADING) --}}
        <div x-show="!isSubmitting" class="max-w-4xl mx-auto space-y-6 sm:space-y-8">
            
            {{-- HEADER PROGRESS --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-400 uppercase tracking-widest text-[10px] sm:text-xs">Sistem E-Sign Terpusat</h2>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-800 mt-1" 
                        x-text="currentStep === 1 ? 'Langkah 1 dari 2 — Baca Perjanjian' : 'Langkah 2 dari 2 — Tanda Tangan'">
                    </h1>
                </div>
                
                {{-- Progress Badge --}}
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-8 rounded-full transition-all duration-300"
                          :class="currentStep === 1 ? 'bg-[#F97316]' : 'bg-green-200'"></span>
                    <span class="h-2.5 w-8 rounded-full transition-all duration-300"
                          :class="currentStep === 2 ? 'bg-[#F97316]' : 'bg-gray-200'"></span>
                </div>
            </div>

            {{-- STEP 1: BACA SURAT --}}
            <div x-show="currentStep === 1" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="space-y-6">
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden relative">
                    {{-- Scroll Progress Bar --}}
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gray-100 z-10">
                        <div class="h-full bg-[#F97316] transition-all duration-200" :style="`width: ${scrollPercent}%`"></div>
                    </div>

                    {{-- Scroll Indicator Floating --}}
                    <div x-show="!hasScrolledToBottom" 
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 pointer-events-none">
                        <div class="bg-gray-900/80 backdrop-blur-sm text-white px-4 py-2 rounded-full text-[11px] sm:text-xs font-bold shadow-xl flex items-center gap-2 animate-bounce">
                            <span>Scroll ke bawah untuk membaca</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </div>
                    </div>

                    {{-- Agreement Scrollable Container --}}
                    <div id="agreementContainer" 
                         @scroll="handleScroll($event)"
                         class="h-auto overflow-visible lg:h-[650px] lg:overflow-y-auto p-2 sm:p-4 bg-white">
                        <div id="agreementHtml" class="prose max-w-none text-sm sm:text-base leading-relaxed text-gray-800">
                            @include('client.partials.agreement-content', [
                                'client' => $client,
                                'nik' => $nik, 
                                'address' => $address,
                                'dateFormatted' => $dateFormatted,
                                'letterNumber' => $letterNumber
                            ])
                        </div>
                    </div>
                </div>

                {{-- Action Button Step 1 --}}
                <div class="text-center space-y-3">
                    <button type="button"
                    @click="goToStep2"
                    :class="'bg-[#F97316] hover:bg-orange-600 shadow-orange-100 shadow-lg cursor-pointer'"
                    class="w-full py-4 px-6 text-white font-bold rounded-xl text-base sm:text-lg transition-all duration-300 uppercase tracking-wider flex items-center justify-center gap-3 text-white">
                        <span>Saya Sudah Membaca dan Memahami Perjanjian</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    
                    <p x-show="!canProceedStep1" class="text-xs sm:text-sm font-semibold text-gray-400">
                        * Silakan scroll hingga bagian akhir dokumen untuk menyetujui (Progress: <span x-text="Math.round(scrollPercent) + '%'"></span>)
                    </p>
                </div>
            </div>

            {{-- STEP 2: TANDA TANGAN --}}
            <div x-show="currentStep === 2" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="space-y-6">
                
                {{-- Tombol Kembali --}}
                <div>
                    <button type="button" @click="goBackToStep1" 
                            class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-4 py-2 rounded-xl text-sm transition-all active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l-7-7m7 7h18" />
                        </svg>
                        <span>Kembali baca perjanjian</span>
                    </button>
                </div>

                {{-- Card Ringkasan Identitas --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-8 space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3">Ringkasan Identitas Penerima</h3>
                    
                    <div class="divide-y divide-gray-100 text-sm sm:text-base">
                        <div class="grid grid-cols-12 py-3 gap-2">
                            <span class="col-span-4 sm:col-span-3 text-gray-400 font-medium">Nama</span>
                            <span class="col-span-8 sm:col-span-9 font-bold text-gray-800 uppercase" x-text="clientName"></span>
                        </div>
                        <div class="grid grid-cols-12 py-3 gap-2">
                            <span class="col-span-4 sm:col-span-3 text-gray-400 font-medium">NIK</span>
                            <span class="col-span-8 sm:col-span-9 font-bold text-gray-800" x-text="nik"></span>
                        </div>
                        <div class="grid grid-cols-12 py-3 gap-2">
                            <span class="col-span-4 sm:col-span-3 text-gray-400 font-medium">Tgl. Lahir</span>
                            <span class="col-span-8 sm:col-span-9 font-bold text-gray-800" x-text="clientDob"></span>
                        </div>
                        <div class="grid grid-cols-12 py-3 gap-2">
                            <span class="col-span-4 sm:col-span-3 text-gray-400 font-medium">Alamat</span>
                            <span class="col-span-8 sm:col-span-9 font-bold text-gray-800 text-justify" x-text="address"></span>
                        </div>
                        <div class="grid grid-cols-12 py-3 gap-2">
                            <span class="col-span-4 sm:col-span-3 text-gray-400 font-medium">No. Surat</span>
                            <span class="col-span-8 sm:col-span-9 font-bold text-gray-800" x-text="letterNumber"></span>
                        </div>
                        <div class="grid grid-cols-12 py-3 gap-2">
                            <span class="col-span-4 sm:col-span-3 text-gray-400 font-medium">Tgl. Surat</span>
                            <span class="col-span-8 sm:col-span-9 font-bold text-gray-800" x-text="dateFormatted"></span>
                        </div>
                    </div>
                </div>

                {{-- Card Canvas Tanda Tangan --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-8 space-y-6">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-xl font-bold text-gray-800">Tanda Tangan Digital</h3>
                        <p class="text-sm text-gray-500 mt-1">Gambar tanda tangan Anda pada area putih di bawah ini menggunakan jari atau mouse.</p>
                    </div>

                    {{-- Canvas Area --}}
                    <div class="relative group">
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none transition-opacity duration-300" 
                             :class="!isCanvasEmpty ? 'opacity-0' : 'opacity-100'">
                            <span class="text-gray-300 font-bold tracking-wider uppercase text-xs sm:text-sm">Tanda tangan di sini</span>
                        </div>
                        <div class="rounded-xl overflow-hidden transition-all duration-300 bg-white"
                             :class="!isCanvasEmpty ? 'border-2 border-gray-800 shadow-inner' : 'border-2 border-dashed border-gray-300'">
                            <canvas id="signatureCanvas" 
                                    class="w-full h-48 sm:h-56 cursor-crosshair touch-none bg-white" 
                                    @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"
                                    @touchstart="startDrawing" @touchmove="draw" @touchend="stopDrawing">
                            </canvas>
                        </div>
                        <button type="button" @click="clearSignature" 
                                class="absolute bottom-3 right-3 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider shadow-sm transition active:scale-95">
                            Bersihkan
                        </button>
                    </div>

                    {{-- Checkbox Persetujuan --}}
                    <div class="pt-4 border-t border-gray-100">
                        <label class="flex items-start gap-4 cursor-pointer group">
                            <div class="relative flex items-center mt-1">
                               <input type="checkbox" 
                                name="agreement_accepted"
                                value="1"
                                x-model="isChecked" 
                                required
                                class="peer h-6 w-6 rounded border-gray-300 text-[#F97316] focus:ring-[#F97316] transition-all cursor-pointer">
                            </div>
                            <span class="text-xs sm:text-sm text-gray-600 leading-relaxed group-hover:text-gray-900 transition-colors">
                                Saya telah membaca, memahami, dan menyetujui seluruh ketentuan Perjanjian Layanan Percepatan Pencairan Nenkin yang telah saya baca pada langkah sebelumnya.
                            </span>
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit" 
                                :disabled="!canSubmit"
                                :class="canSubmit ? 'bg-[#F97316] hover:bg-orange-600 shadow-orange-200 shadow-xl' : 'bg-gray-300 cursor-not-allowed opacity-60 text-gray-500'"
                                class="w-full py-4 px-6 text-white font-bold rounded-xl text-base sm:text-lg transition-all duration-300 uppercase tracking-widest flex items-center justify-center gap-3 active:scale-[0.98]">
                            <span>Tandatangani & Kirim</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function esignApp(initialNik, initialAddress, clientName, letterNumber, dateFormatted, clientDob) {
    return {
        currentStep: 1,
        hasScrolledToBottom: false,
        scrollPercent: 0,
        isSubmitting: false,
        nik: initialNik,
        address: initialAddress,
        clientName: clientName,
        letterNumber: letterNumber,
        dateFormatted: dateFormatted,
        clientDob: clientDob,
        isChecked: false,
        isCanvasEmpty: true,
        signature_data: '',
        isDrawing: false,
        ctx: null,
        canvas: null,

        init() {
            this.canvas = document.getElementById('signatureCanvas');
            
            // Inisialisasi canvas context dengan opsi performance 'willReadFrequently'
            if (this.canvas) {
                this.ctx = this.canvas.getContext('2d', { willReadFrequently: true });
                this.resizeCanvas();
                window.addEventListener('resize', () => this.resizeCanvas());
            }
            
            // Periksa scroll pada pemuatan awal (Step 1)
            setTimeout(() => {
                this.checkScrollState();
            }, 100);

            // Deteksi scroll halaman untuk mobile di Step 1
            window.addEventListener('scroll', () => {
                if (this.currentStep === 1 && window.innerWidth < 1024) {
                    const scrollTop = window.scrollY;
                    const clientHeight = window.innerHeight;
                    const scrollHeight = document.documentElement.scrollHeight;
                    
                    const isAtBottom = (scrollTop + clientHeight) >= (scrollHeight - 80);
                    
                    const maxScroll = scrollHeight - clientHeight;
                    if (maxScroll > 0) {
                        this.scrollPercent = Math.min(100, (scrollTop / maxScroll) * 100);
                        if (isAtBottom) {
                            if (!this.hasScrolledToBottom) {
                                this.hasScrolledToBottom = true;
                                this.scrollPercent = 100;
                            }
                        }
                    } else {
                        // Tidak ada scrollbar di halaman mobile (muat penuh)
                        this.hasScrolledToBottom = true;
                        this.scrollPercent = 100;
                    }
                }
            });
        },

        // Helper aman untuk memanggil nextTick dengan fallback setTimeout
        nextTick(callback) {
            if (typeof this.$nextTick === 'function') {
                this.$nextTick(callback);
            } else {
                setTimeout(callback, 50);
            }
        },

        setupCanvas() {
            if (!this.ctx) return;
            this.ctx.strokeStyle = '#000000';
            this.ctx.lineWidth = 2.5;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
        },

        resizeCanvas() {
            if (!this.canvas) return;
            const rect = this.canvas.getBoundingClientRect();
            if (!rect || rect.width === 0 || rect.height === 0) {
                return;
            }
            
            // Simpan image data sebelum resize
            const imageData = this.canvas.toDataURL('image/png');
            const hadSignature = !this.isCanvasEmpty;
            
            const dpr = window.devicePixelRatio || 1;
            this.canvas.width = rect.width * dpr;
            this.canvas.height = rect.height * dpr;
            if (this.ctx) {
                this.ctx.scale(dpr, dpr);
            }
            this.setupCanvas();
            
            // Redraw signature setelah resize jika ada
            if (hadSignature) {
                this.isCanvasEmpty = false;
                const img = new Image();
                img.onload = () => {
                    if (this.ctx) {
                        this.ctx.drawImage(img, 0, 0, rect.width, rect.height);
                    }
                };
                img.src = imageData;
            } else {
                this.checkIfCanvasEmpty();
            }
        },

        checkScrollState() {
            if (window.innerWidth >= 1024) {
                // Desktop: Cek jika container surat fit tanpa perlu scroll
                const element = document.getElementById('agreementContainer');
                if (element && element.scrollHeight <= element.clientHeight) {
                    this.hasScrolledToBottom = true;
                    this.scrollPercent = 100;
                }
            } else {
                // Mobile: Cek jika halaman fit tanpa perlu scroll
                const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
                if (scrollHeight <= 10) {
                    this.hasScrolledToBottom = true;
                    this.scrollPercent = 100;
                }
            }
        },

        handleScroll(e) {
            // Hanya deteksi container scroll di layar desktop
            if (window.innerWidth >= 1024 && this.currentStep === 1) {
                const element = e.target;
                const scrollHeight = element.scrollHeight - element.clientHeight;
                const currentScroll = element.scrollTop;
                
                if (scrollHeight <= 0) {
                    this.scrollPercent = 100;
                    this.hasScrolledToBottom = true;
                    return;
                }

                this.scrollPercent = (currentScroll / scrollHeight) * 100;

                if (scrollHeight - currentScroll <= 25) { // toleransi 25px
                    if (!this.hasScrolledToBottom) {
                        this.hasScrolledToBottom = true;
                        this.scrollPercent = 100;
                    }
                }
            }
        },

        // Navigasi Antar Step
        goToStep2() {
            if (this.canProceedStep1) {
                this.currentStep = 2;
                // Gunakan nextTick aman untuk memastikan DOM Step 2 selesai render sebelum canvas diakses
                this.nextTick(() => {
                    // Dapatkan ulang element canvas setelah dirender oleh Alpine
                    this.canvas = document.getElementById('signatureCanvas');
                    if (this.canvas) {
                        this.ctx = this.canvas.getContext('2d', { willReadFrequently: true });
                        this.resizeCanvas();
                    }
                    // Scroll ke atas halaman saat masuk Step 2
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }
        },

        goBackToStep1() {
            this.currentStep = 1;
            this.nextTick(() => {
                this.checkScrollState();
            });
        },

        // Drawing Logic (Canvas)
        startDrawing(e) {
            if (!this.canvas || !this.ctx) return;
            this.isDrawing = true;
            const pos = this.getPointerPos(e);
            this.ctx.beginPath();
            this.ctx.moveTo(pos.x, pos.y);
            e.preventDefault();
        },

        draw(e) {
            if (!this.isDrawing || !this.ctx) return;
            const pos = this.getPointerPos(e);
            this.ctx.lineTo(pos.x, pos.y);
            this.ctx.stroke();
            e.preventDefault();
        },

        stopDrawing() {
            if (this.isDrawing) {
                this.isDrawing = false;
                if (this.canvas) {
                    this.signature_data = this.canvas.toDataURL('image/png');
                }
                this.checkIfCanvasEmpty();
            }
        },

        getPointerPos(e) {
            if (!this.canvas) return { x: 0, y: 0 };
            const rect = this.canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - (rect ? rect.left : 0),
                y: clientY - (rect ? rect.top : 0)
            };
        },

        clearSignature() {
            if (this.canvas && this.ctx) {
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            }
            this.signature_data = '';
            this.isCanvasEmpty = true;
        },

        // Menggunakan data pixel canvas untuk mendeteksi apakah benar-benar kosong
        checkIfCanvasEmpty() {
            if (!this.canvas) {
                this.isCanvasEmpty = true;
                return;
            }
            
            try {
                const context = this.canvas.getContext('2d', { willReadFrequently: true });
                if (!context) {
                    this.isCanvasEmpty = true;
                    return;
                }
                const imgData = context.getImageData(0, 0, this.canvas.width, this.canvas.height);
                const buffer = new Uint32Array(imgData.data.buffer);
                
                // Jika ada pixel yang memiliki alpha/warna yang tidak kosong, canvas tidak kosong
                const hasDrawnPixels = buffer.some(color => color !== 0);
                this.isCanvasEmpty = !hasDrawnPixels;
            } catch (error) {
                // Fallback jika getImageData gagal karena alasan tertentu
                this.isCanvasEmpty = (this.signature_data === '');
            }
        },

        // Computed State
        get canProceedStep1() {
            return this.hasScrolledToBottom;
        },

        get canSubmit() {
            return !this.isCanvasEmpty && this.isChecked && !this.isSubmitting;
        },

        submitForm() {
            if (this.canSubmit) {
                this.isSubmitting = true;
                document.getElementById('esignForm').submit();
            }
        }
    }
}
</script>

<style>
#agreementContainer::-webkit-scrollbar {
    width: 6px;
}
#agreementContainer::-webkit-scrollbar-track {
    background: transparent;
}
#agreementContainer::-webkit-scrollbar-thumb {
    background: #E2E8F0;
    border-radius: 10px;
}
#agreementContainer::-webkit-scrollbar-thumb:hover {
    background: #CBD5E0;
}
canvas {
    touch-action: none;
}

/* Obyektifitas Mobile-First: Perbaiki Padding Surat yang terjepit di mobile */
@media (max-width: 640px) {
    .agreement-content {
        padding: 1rem !important; /* Mereduksi p-8 (32px) bawaan menjadi 16px di mobile */
    }
    
    /* Ganti kolom tanda tangan di surat menjadi stack vertikal di mobile agar tidak terjepit */
    .agreement-content .grid-cols-2 {
        grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
        gap: 2.5rem !important;
    }
}
</style>
@endsection
