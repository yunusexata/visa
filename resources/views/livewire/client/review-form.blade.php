<div>
    @if ($showForm)
        {{--
            In popupMode: render without the outer card wrapper (the popup modal IS the card).
            In normal mode: keep the original white card wrapper with shadow.
        --}}
        <div class="{{ $popupMode ? '' : 'w-full max-w-lg mx-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl overflow-hidden p-6 md:p-8 mt-6 transition-all duration-300' }}">

            @if ($hasSubmitted)
                {{-- ── Thank You Screen ── --}}
                <div class="text-center py-4 flex flex-col items-center">
                    <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mb-3 animate-bounce">
                        <svg class="w-8 h-8 fill-green-500" viewBox="0 0 24 24">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-[#001B5E] mb-1">Terima Kasih!</h3>
                    <p class="text-gray-500 text-sm px-2 leading-relaxed">
                        {{ $submittedMessage }}
                    </p>

                    @if ($redirectUrl)
                        <div class="mt-5">
                            <a href="{{ $redirectUrl }}"
                               target="_blank"
                               class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#FF4800] hover:bg-[#cc3a00] text-white font-semibold rounded-xl shadow transition-all duration-200 text-sm">
                                Lanjutkan Ke Tautan Review
                                <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24">
                                    <path d="M19 19H5V5h7V3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/>
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            @else
                {{-- ── Review Form ── --}}
                <form wire:submit.prevent="submit" class="space-y-5">

                    {{-- ── Star Rating Picker ── --}}
                    <div class="flex flex-col items-center gap-1">
                        <div class="flex items-center gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <button
                                    type="button"
                                    wire:click="selectRating({{ $i }})"
                                    id="star-btn-{{ $i }}"
                                    title="Bintang {{ $i }}"
                                    class="focus:outline-none transition-transform duration-150 hover:scale-110 active:scale-95 touch-manipulation"
                                >
                                    {{-- Active star: brand orange (#FF4800); inactive: brand gray (#D7D7D7) --}}
                                    <svg
                                        class="w-12 h-12 transition-colors duration-150 {{ $i <= $rating ? 'fill-[#FF4800]' : 'fill-[#D7D7D7]' }}"
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                    </svg>
                                </button>
                            @endfor
                        </div>

                        {{-- Rating label --}}
                        <span class="text-xs font-medium mt-1
                            {{ $rating === 0 ? 'text-gray-400' :
                               ($rating <= 2 ? 'text-red-500' :
                               ($rating === 3 ? 'text-yellow-500' : 'text-[#FF4800]')) }}">
                            @if ($rating === 0) Pilih bintang untuk memberi nilai
                            @elseif ($rating === 1) Sangat Buruk
                            @elseif ($rating === 2) Buruk
                            @elseif ($rating === 3) Cukup
                            @elseif ($rating === 4) Baik
                            @else Sangat Baik!
                            @endif
                        </span>

                        @error('rating')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- ── Feedback Message ── --}}
                    <div class="space-y-1.5">
                        <label for="review-message-{{ $popupMode ? 'popup' : 'inline' }}"
                               class="block text-sm font-medium text-gray-700">
                            Pesan Ulasan
                            <span class="text-xs text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <textarea
                            id="review-message-{{ $popupMode ? 'popup' : 'inline' }}"
                            wire:model.defer="message"
                            rows="{{ $popupMode ? 3 : 4 }}"
                            placeholder="Ceritakan pengalaman Anda (opsional)..."
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-800 placeholder-gray-400
                                   focus:ring-2 focus:ring-[#0049FF] focus:border-[#0049FF] focus:outline-none
                                   transition duration-150 resize-none text-sm"
                        ></textarea>
                    </div>

                    {{-- ── General error ── --}}
                    @error('general')
                        <div class="bg-red-50 text-red-600 text-sm p-3 rounded-xl border border-red-200">
                            {{ $message }}
                        </div>
                    @enderror

                    {{-- ── Submit Button ── --}}
                    <button
                        type="submit"
                        id="btn-submit-review-{{ $popupMode ? 'popup' : 'inline' }}"
                        {{ $rating === 0 ? 'disabled' : '' }}
                        wire:loading.attr="disabled"
                        class="w-full flex items-center justify-center gap-2 px-6 py-3.5
                               bg-[#FF4800] hover:bg-[#cc3a00]
                               disabled:bg-[#D7D7D7] disabled:cursor-not-allowed
                               text-white font-bold rounded-xl
                               shadow-lg shadow-orange-500/20 hover:shadow-orange-500/40
                               transition-all duration-200 text-sm"
                    >
                        <span wire:loading.remove wire:target="submit">Kirim Ulasan Sekarang</span>
                        <span wire:loading wire:target="submit" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 fill-white" viewBox="0 0 24 24">
                                <path d="M12 4V2A10 10 0 002 12h2a8 8 0 018-8z"/>
                            </svg>
                            Mengirim...
                        </span>
                    </button>

                </form>
            @endif
        </div>
    @endif
</div>

{{--
    Keep the open-new-tab handler here as a safety net for the
    inline (non-popup) review form instance on the page.
    For the popup instance, client-home.blade.php already handles this event.
--}}
@once
@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('open-new-tab', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            if (data && data.url) {
                window.open(data.url, '_blank');
            }
        });
    });
</script>
@endpush
@endonce
