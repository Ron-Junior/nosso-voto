<div class="max-w-xl mx-auto space-y-6">
    <x-toast :message="$statusMessage" />

    <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-5">
        <div class="space-y-1.5">
            <p class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                PWA Web Share Target
            </p>
            <h1 class="font-serif text-2xl font-semibold text-[#2C2523]">
                Salvar Pin do Celular
            </h1>
            <p class="text-sm text-[#6E625F]">
                Confira o link extraído do app do Pinterest e toque na especialidade para salvar em 1 toque.
            </p>
        </div>

        @if (filled($text) || filled($url))
            <div class="rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] p-3.5 space-y-1">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-[#6E625F]">
                    Dados recebidos do compartilhamento
                </p>
                @if (filled($title))
                    <p class="text-xs font-medium text-[#2C2523]">{{ $title }}</p>
                @endif
                @if (filled($text))
                    <p class="text-xs text-[#6E625F] break-words">{{ $text }}</p>
                @endif
            </div>
        @endif

        {{-- One-Tap Category Quick Save Buttons --}}
        <div class="space-y-2">
            <p class="text-sm font-medium text-[#2C2523]">
                Salvar com 1 toque em:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                @foreach ($categoryLabels as $catKey => $catLabel)
                    <button type="button"
                            wire:click="quickSave('{{ $catKey }}')"
                            wire:loading.attr="disabled"
                            wire:target="quickSave,saveInspiration"
                            class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-4 py-3 min-h-[48px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                        <span wire:loading.remove wire:target="quickSave('{{ $catKey }}')">
                            Salvar em {{ $catLabel }}
                        </span>
                        <span wire:loading wire:target="quickSave('{{ $catKey }}')">Salvando...</span>
                    </button>
                @endforeach
            </div>
        </div>

        <form wire:submit="saveInspiration" class="pt-4 border-t border-[#EAE0DA] space-y-4">
            <div>
                <label for="pwa_pinterest_url" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    Link Extraído do Pinterest
                </label>
                <input id="pwa_pinterest_url"
                       type="url"
                       wire:model="pinterest_url"
                       @input="
                           try {
                               const val = ($el.value || '').trim();
                               if (val) {
                                   const u = new URL(val);
                                   const host = u.hostname.toLowerCase();
                                   const path = u.pathname.replace(/^\/+|\/+$/g, '');
                                   if (host.includes('pin.it')) {
                                       $wire.type = 'pin';
                                   } else if (host.includes('pinterest.') && !/(^|\/)pin(\/|$)/i.test(path) && path.includes('/')) {
                                       $wire.type = 'board';
                                   } else {
                                       $wire.type = 'pin';
                                   }
                               }
                           } catch (e) {}
                       "
                       placeholder="https://br.pinterest.com/pin/..."
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                @error('pinterest_url')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="pwa_category" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Categoria
                    </label>
                    <select id="pwa_category"
                            wire:model="category"
                            class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        @foreach ($categoryLabels as $catKey => $catLabel)
                            <option value="{{ $catKey }}">{{ $catLabel }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="pwa_type" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Tipo
                    </label>
                    <select id="pwa_type"
                            wire:model="type"
                            class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        @foreach ($typeLabels as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="pwa_title" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    Título (opcional)
                </label>
                <input id="pwa_title"
                       type="text"
                       wire:model="title"
                       placeholder="Ex.: Penteado semi-preso com flores"
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 min-h-[48px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                @error('title')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="pwa_bride_notes" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    Observação para o profissional (opcional)
                </label>
                <textarea id="pwa_bride_notes"
                          rows="2"
                          wire:model="bride_notes"
                          placeholder="Anote detalhes importantes sobre esta inspiração..."
                          class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none"></textarea>
                @error('bride_notes')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col sm:flex-row gap-2.5 pt-2">
                <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="quickSave,saveInspiration"
                        class="flex-1 inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-5 py-3 min-h-[48px] text-sm font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                    <span wire:loading.remove wire:target="saveInspiration">Salvar com Notas</span>
                    <span wire:loading wire:target="saveInspiration">Salvando...</span>
                </button>

                <a href="{{ route('inspirations.index') }}"
                   wire:navigate.hover
                   class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-5 py-3 min-h-[48px] text-sm font-medium text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors">
                    Ver Moodboard
                </a>
            </div>
        </form>

        @if ($savedInspiration !== null)
            <div class="rounded-xl border border-[#5A8F7B]/40 bg-[#EBF3F0] p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="space-y-0.5 min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#5A8F7B]">
                        Último pin salvo • {{ $savedInspiration->categoryLabel() }}
                    </p>
                    <p class="text-sm font-semibold text-[#2C2523] truncate">
                        {{ $savedInspiration->title }}
                    </p>
                </div>

                <a href="{{ route('inspirations.index') }}"
                   wire:navigate.hover
                   class="inline-flex items-center justify-center rounded-xl bg-[#5A8F7B] px-4 py-2 min-h-[44px] text-xs font-semibold text-white shrink-0">
                    Abrir Moodboard
                </a>
            </div>
        @endif
    </section>
</div>

