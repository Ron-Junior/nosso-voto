@props(['message' => null, 'listen' => false])

@if (filled($message))
    <div wire:key="toast-{{ md5($message . microtime(true)) }}"
         x-data="{ show: true }"
         x-init="setTimeout(() => show = false, 4000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         role="status"
         aria-live="polite"
         data-toast
         class="fixed bottom-20 left-4 right-4 sm:left-auto sm:right-4 lg:bottom-6 lg:right-6 z-50 sm:w-full sm:max-w-sm rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-4 shadow-lg">
        <div class="flex items-start gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#EBF3F0] text-[#5A8F7B]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </span>

            <p class="flex-1 pt-1 text-sm font-medium text-[#2C2523]">
                {{ $message }}
            </p>

            <button type="button"
                    @click="show = false"
                    aria-label="Fechar notificação"
                    class="inline-flex min-h-[44px] min-w-[44px] -my-2 -mr-2 items-center justify-center rounded-xl text-[#6E625F] hover:bg-[#FBF9F7] hover:text-[#2C2523] transition-colors cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
@endif

@if ($listen)
    <div x-data="{
            show: false,
            text: '',
            timer: null,
            showMessage(msg) {
                if (!msg) return;
                this.text = msg;
                this.show = true;
                if (this.timer) clearTimeout(this.timer);
                this.timer = setTimeout(() => { this.show = false; }, 4000);
            }
         }"
         @client-toast.window="showMessage($event.detail?.message)"
         x-cloak
         x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         role="status"
         aria-live="polite"
         data-client-toast
         class="fixed bottom-20 left-4 right-4 sm:left-auto sm:right-4 lg:bottom-6 lg:right-6 z-50 sm:w-full sm:max-w-sm rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-4 shadow-lg">
        <div class="flex items-start gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#EBF3F0] text-[#5A8F7B]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </span>

            <p x-text="text" class="flex-1 pt-1 text-sm font-medium text-[#2C2523]"></p>

            <button type="button"
                    @click="show = false"
                    aria-label="Fechar notificação"
                    class="inline-flex min-h-[44px] min-w-[44px] -my-2 -mr-2 items-center justify-center rounded-xl text-[#6E625F] hover:bg-[#FBF9F7] hover:text-[#2C2523] transition-colors cursor-pointer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
@endif
