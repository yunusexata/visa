<div
    x-data="{
        copy(text, message) {
            if (!text) return;
            const title = message || 'Berhasil  Copy Link ke Clipboard';
            const showToast = () => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: title,
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text)
                    .then(showToast)
                    .catch(() => this.fallbackCopy(text, showToast));
            } else {
                this.fallbackCopy(text, showToast);
            }
        },
        fallbackCopy(text, callback) {
            const el = document.createElement('textarea');
            el.value = text;
            el.setAttribute('readonly', '');
            el.style.position = 'fixed';
            el.style.left = '-9999px';
            el.style.top = '-9999px';
            document.body.appendChild(el);
            el.focus();
            el.select();
            try {
                document.execCommand('copy');
                callback();
            } catch (e) {
                console.error('Fallback copy failed', e);
            }
            document.body.removeChild(el);
        }
    }"
    @copy-to-clipboard.window="copy($event.detail?.text ?? $event.detail?.[0]?.text ?? $event.detail, $event.detail?.message ?? $event.detail?.[0]?.message)"
>
    {{-- Trigger Button (Protected by Spatie Permission) --}}
    @can(App\Helpers\PermissionHelper::transform(App\Helpers\PermissionHelper::ACCESS_VISA_FORM_LINK, App\Helpers\PermissionHelper::TYPE_CREATE))
    <flux:modal.trigger name="create-magic-link">
        <button
            type="button"
            wire:click="openModal"
            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 active:bg-brand-700 transition cursor-pointer"
        >
            <span class="material-symbols-outlined text-lg leading-none">magic_button</span>
            <span>Buat Magic Link</span>
        </button>
    </flux:modal.trigger>
    @endcan

    {{-- Flux Modal --}}
    <flux:modal name="create-magic-link" wire:model="showModal" class="md:w-[28rem]">
        <form wire:submit="save" class="space-y-5">
            <div>
                <flux:heading size="lg">Buat Magic Link</flux:heading>
                <flux:subheading>Isi formulir untuk membuat link pendaftaran form visa baru.</flux:subheading>
            </div>

            <flux:separator />

            {{-- Nama Link --}}
            <flux:field>
                <flux:label badge="Wajib">Nama Link</flux:label>
                <flux:input wire:model="name" type="text" placeholder="Contoh: Batch Visa Juli 2026" autofocus />
                <flux:error name="name" />
            </flux:field>

            {{-- Password with Alpine Toggle --}}
            <flux:field x-data="{ showPass: false }">
                <flux:label>Password (opsional)</flux:label>
                <div class="relative">
                    <flux:input
                        wire:model="password"
                        x-bind:type="showPass ? 'text' : 'password'"
                        type="password"
                        placeholder="Password form pendaftaran"
                    />
                    <button
                        type="button"
                        @click="showPass = !showPass"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer focus:outline-none"
                        tabindex="-1"
                    >
                        <span class="material-symbols-outlined text-lg select-none" x-text="showPass ? 'visibility_off' : 'visibility'"></span>
                    </button>
                </div>
                <flux:error name="password" />
            </flux:field>

            {{-- Maks. Penggunaan --}}
            <flux:field>
                <flux:label badge="Wajib">Maks. Penggunaan</flux:label>
                <flux:input wire:model="max_usage" type="number" min="1" step="1" />
                <flux:error name="max_usage" />
            </flux:field>

            {{-- Kadaluarsa --}}
            <flux:field>
                <flux:label badge="Wajib">Kadaluarsa</flux:label>
                <flux:input wire:model="expired_at" type="date" />
                <flux:error name="expired_at" />
            </flux:field>

            <flux:separator />

            {{-- Actions --}}
            <div class="flex gap-2 justify-end pt-2">
                <flux:modal.close>
                    <flux:button type="button" wire:click="closeModal" variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="save">Buat & Copy Link</span>
                    <span wire:loading wire:target="save">Memproses...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
