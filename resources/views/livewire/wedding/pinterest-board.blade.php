<div class="space-y-6"
     x-data="{
         activeCategory: $wire.entangle('activeCategory'),
         startEdit(item) {
             $wire.editingInspirationId = item.id;
             $wire.category = item.category || 'decoracao';
             $wire.type = item.type || 'pin';
             $wire.title = item.title || '';
             $wire.pinterest_url = item.pinterest_url || '';
             $wire.image_url = item.image_url ?? null;
             $wire.bride_notes = item.bride_notes ?? null;
             document.getElementById('inspiration-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
         },
         cancelEdit() {
             $wire.editingInspirationId = null;
             $wire.category = ['decoracao', 'maquiagem', 'penteado'].includes(this.activeCategory) ? this.activeCategory : 'decoracao';
             $wire.type = 'pin';
             $wire.title = '';
             $wire.pinterest_url = '';
             $wire.image_url = null;
             $wire.bride_notes = null;
         }
     }">
    <x-toast :message="$statusMessage" />

    {{-- Header --}}
    <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="space-y-1.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                    Moodboard Segmentado • Pinterest
                </p>
                <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-[#2C2523]">
                    Inspirações do Pinterest
                </h1>
                <p class="text-sm text-[#6E625F] max-w-2xl">
                    Centralize referências de <strong class="font-medium text-[#2C2523]">Decoração</strong>, <strong class="font-medium text-[#2C2523]">Maquiagem</strong> e <strong class="font-medium text-[#2C2523]">Penteado</strong> pelo painel web, pela Extensão do Chrome ou compartilhando direto do app do Pinterest no celular.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2.5 shrink-0">
                <a href="{{ route('inspirations.pwa-share') }}"
                   wire:navigate.hover
                   class="inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors">
                    Captura Rápida (PWA)
                </a>
                <a href="{{ route('dashboard') }}"
                   wire:navigate.hover
                   class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm font-medium text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors">
                    Voltar ao Painel
                </a>
            </div>
        </div>

        {{-- Category Tabs --}}
        <div class="flex flex-wrap gap-2 pt-2 border-t border-[#EAE0DA]" role="tablist" aria-label="Categorias de inspiração">
            <button type="button"
                    role="tab"
                    aria-selected="{{ $activeCategory === 'all' ? 'true' : 'false' }}"
                    :aria-selected="(activeCategory === 'all').toString()"
                    @click="activeCategory = 'all'"
                    wire:click="selectCategory('all')"
                    :class="activeCategory === 'all' ? 'bg-[#C25E38] text-white' : 'border border-[#EAE0DA] bg-[#FBF9F7] text-[#6E625F] hover:border-[#C25E38] hover:text-[#C25E38]'"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 min-h-[44px] text-sm font-semibold transition-colors cursor-pointer">
                <span>Todas</span>
                <span :class="activeCategory === 'all' ? 'bg-white/20 text-white' : 'bg-[#FFFFFF] text-[#2C2523]'"
                      class="rounded-lg px-2 py-0.5 text-xs tabular-nums">
                    {{ $totalCount }}
                </span>
            </button>

            @foreach ($categoryLabels as $catKey => $catLabel)
                <button type="button"
                        role="tab"
                        aria-selected="{{ $activeCategory === $catKey ? 'true' : 'false' }}"
                        :aria-selected="(activeCategory === '{{ $catKey }}').toString()"
                        @click="activeCategory = '{{ $catKey }}'"
                        wire:click="selectCategory('{{ $catKey }}')"
                        :class="activeCategory === '{{ $catKey }}' ? 'bg-[#C25E38] text-white' : 'border border-[#EAE0DA] bg-[#FBF9F7] text-[#6E625F] hover:border-[#C25E38] hover:text-[#C25E38]'"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 min-h-[44px] text-sm font-semibold transition-colors cursor-pointer">
                    <span>{{ $catLabel }}</span>
                    <span :class="activeCategory === '{{ $catKey }}' ? 'bg-white/20 text-white' : 'bg-[#FFFFFF] text-[#2C2523]'"
                          class="rounded-lg px-2 py-0.5 text-xs tabular-nums">
                        {{ $countsByCategory[$catKey] ?? 0 }}
                    </span>
                </button>
            @endforeach
        </div>
    </section>

    {{-- Manual Pin/Board Capture Form --}}
    <section id="inspiration-form" class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 x-text="$wire.editingInspirationId !== null ? 'Editar Referência do Pinterest' : 'Adicionar Pin ou Pasta Manualmente'"
                    class="font-serif text-xl font-semibold text-[#2C2523]">
                    {{ $editingInspirationId !== null ? 'Editar Referência do Pinterest' : 'Adicionar Pin ou Pasta Manualmente' }}
                </h2>
                <p class="text-sm text-[#6E625F]">
                    Cole o link de um Pin ou de uma Pasta completa do Pinterest, escolha a especialidade e anote o que mais gostou.
                </p>
            </div>

            <button type="button"
                    x-cloak
                    x-show="$wire.editingInspirationId !== null"
                    @click="cancelEdit()"
                    class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] cursor-pointer self-start">
                Cancelar Edição
            </button>
        </div>

        <form wire:submit="saveInspiration" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2">
                    <label for="inspiration_pinterest_url" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Link do Pin ou Pasta (Pinterest)
                    </label>
                    <input id="inspiration_pinterest_url"
                           type="url"
                           wire:model="pinterest_url"
                           placeholder="https://br.pinterest.com/pin/123456789/"
                           class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                    @error('pinterest_url')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="inspiration_category" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Categoria / Especialidade
                    </label>
                    <select id="inspiration_category"
                            wire:model="category"
                            class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        @foreach ($categoryLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="inspiration_type" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Tipo de Referência
                    </label>
                    <select id="inspiration_type"
                            wire:model="type"
                            class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        @foreach ($typeLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="inspiration_title" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        Título da Referência (opcional)
                    </label>
                    <input id="inspiration_title"
                           type="text"
                           wire:model="title"
                           placeholder="Ex.: Arco floral terracota para cerimônia ao ar livre"
                           class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                    @error('title')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="inspiration_image_url" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                        URL da Imagem de Capa (opcional)
                    </label>
                    <input id="inspiration_image_url"
                           type="url"
                           wire:model="image_url"
                           placeholder="https://i.pinimg.com/736x/..."
                           class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                    @error('image_url')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="inspiration_bride_notes" class="block text-sm font-medium text-[#2C2523] mb-1.5">
                    Notas da Noiva para o Profissional (opcional)
                </label>
                <textarea id="inspiration_bride_notes"
                          rows="2"
                          wire:model="bride_notes"
                          placeholder="Ex.: Quero exatamente essa paleta com flores secas e iluminação quente nas mesas..."
                          class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none"></textarea>
                @error('bride_notes')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-end gap-2.5">
                <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="saveInspiration"
                        class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-6 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                    <span wire:loading.remove wire:target="saveInspiration"
                          x-text="$wire.editingInspirationId !== null ? 'Atualizar Inspiração' : 'Salvar Inspiração'">
                        {{ $editingInspirationId !== null ? 'Atualizar Inspiração' : 'Salvar Inspiração' }}
                    </span>
                    <span wire:loading wire:target="saveInspiration">Salvando...</span>
                </button>
            </div>
        </form>
    </section>

    {{-- Saved Inspirations Grid --}}
    <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl font-semibold text-[#2C2523]">
                    Referências Salvas
                </h2>
                <p class="text-sm text-[#6E625F]">
                    Exibição segmentada automaticamente para cada especialista convidado.
                </p>
            </div>

            <div class="grid gap-2.5 sm:grid-cols-2 w-full sm:w-auto">
                <div>
                    <label for="inspiration_search" class="sr-only">Buscar inspiração</label>
                    <input id="inspiration_search"
                           type="search"
                           wire:model.live.debounce.350ms="search"
                           placeholder="Buscar por título ou nota..."
                           class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                </div>

                <div>
                    <label for="inspiration_type_filter" class="sr-only">Filtrar por tipo</label>
                    <select id="inspiration_type_filter"
                            wire:model.live="typeFilter"
                            class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                        <option value="all">Pins e Pastas</option>
                        @foreach ($typeLabels as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}">Somente {{ $typeLabel }}s</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div wire:loading.class="opacity-60 transition-opacity" wire:target="selectCategory,search,typeFilter">
        @if ($inspirations->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#EAE0DA] bg-[#FBF9F7] p-8 text-center space-y-2">
                <p class="font-serif text-lg font-semibold text-[#2C2523]">
                    {{ $totalCount === 0 ? 'Nenhuma inspiração salva ainda' : 'Nenhuma inspiração encontrada neste filtro' }}
                </p>
                <p class="text-sm text-[#6E625F] max-w-md mx-auto">
                    {{ $totalCount === 0
                        ? 'Cole um link do Pinterest acima, use o botão flutuante da Extensão Chrome ou compartilhe pelo app do Pinterest no celular.'
                        : 'Limpe a busca ou alterne entre as abas Decoração, Maquiagem e Penteado.' }}
                </p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($inspirations as $inspiration)
                    <article wire:key="inspiration-{{ $inspiration->id }}"
                             class="flex flex-col rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] overflow-hidden shadow-sm">
                        @if (filled($inspiration->image_url))
                            <div class="aspect-[4/3] w-full overflow-hidden bg-[#FBF9F7] border-b border-[#EAE0DA]">
                                <img src="{{ $inspiration->image_url }}"
                                     alt="{{ $inspiration->title }}"
                                     loading="lazy"
                                     class="h-full w-full object-cover">
                            </div>
                        @endif

                        <div class="flex-1 p-5 flex flex-col justify-between gap-4">
                            <div class="space-y-2.5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="inline-flex items-center rounded-lg bg-[#F9EBE6] px-2.5 py-1 text-xs font-semibold text-[#C25E38]">
                                        {{ $inspiration->categoryLabel() }}
                                    </span>
                                    <span class="inline-flex items-center rounded-lg border border-[#EAE0DA] bg-[#FBF9F7] px-2.5 py-1 text-xs font-medium text-[#2C2523]">
                                        {{ $inspiration->typeLabel() }}
                                    </span>
                                    <span class="inline-flex items-center rounded-lg bg-[#EBF3F0] px-2.5 py-1 text-xs font-medium text-[#5A8F7B]">
                                        {{ $inspiration->sourceLabel() }}
                                    </span>
                                </div>

                                <h3 class="font-serif text-lg font-semibold text-[#2C2523] leading-snug">
                                    {{ $inspiration->title }}
                                </h3>

                                @if (filled($inspiration->bride_notes))
                                    <p class="rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] p-3 text-sm text-[#6E625F] leading-relaxed">
                                        {{ $inspiration->bride_notes }}
                                    </p>
                                @endif

                                <p class="text-xs font-mono text-[#6E625F] truncate">
                                    {{ $inspiration->pinterest_url }}
                                </p>
                            </div>

                            <div class="pt-3 border-t border-[#EAE0DA] flex flex-wrap items-center gap-2">
                                <a href="{{ $inspiration->pinterest_url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="flex-1 inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors">
                                    Abrir no Pinterest
                                </a>

                                <button type="button"
                                        @click="startEdit(@js([
                                            'id' => $inspiration->id,
                                            'category' => $inspiration->category,
                                            'type' => $inspiration->type,
                                            'title' => $inspiration->title,
                                            'pinterest_url' => $inspiration->pinterest_url,
                                            'image_url' => $inspiration->image_url,
                                            'bride_notes' => $inspiration->bride_notes,
                                        ]))"
                                        class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer">
                                    Editar
                                </button>

                                <button type="button"
                                        wire:click="deleteInspiration({{ $inspiration->id }})"
                                        wire:confirm="Remover esta inspiração do moodboard?"
                                        wire:loading.attr="disabled"
                                        wire:target="deleteInspiration({{ $inspiration->id }})"
                                        class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-3.5 py-2 min-h-[44px] text-xs font-medium text-red-700 hover:bg-red-50 hover:border-red-300 transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
        </div>
    </section>

    {{-- Specialty Vendors Sharing & Authorization --}}
    <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-4">
        <div>
            <h2 class="font-serif text-xl font-semibold text-[#2C2523]">
                Compartilhamento com Decorador, Maquiadora e Penteadista
            </h2>
            <p class="text-sm text-[#6E625F]">
                Cada profissional acessa apenas as referências da sua especialidade pelo link exclusivo em <code class="font-mono text-xs text-[#2C2523]">/acesso/profissional/{token}</code>.
            </p>
        </div>

        @if ($specialtyVendors->isEmpty())
            <div class="rounded-xl border border-dashed border-[#EAE0DA] bg-[#FBF9F7] p-5 text-sm text-[#6E625F]">
                Cadastre um fornecedor nas categorias <strong class="font-medium text-[#2C2523]">Decoração</strong>, <strong class="font-medium text-[#2C2523]">Maquiagem</strong> ou <strong class="font-medium text-[#2C2523]">Penteado</strong> no <a href="{{ route('dashboard') }}" wire:navigate.hover class="font-semibold text-[#C25E38] underline">Painel dos Noivos</a> para gerar o link exclusivo do moodboard.
            </div>
        @else
            <div class="space-y-3">
                @foreach ($specialtyVendors as $vendor)
                    <article wire:key="pinterest-vendor-{{ $vendor->id }}"
                             class="rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] p-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                        <div class="space-y-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-lg bg-[#FFFFFF] border border-[#EAE0DA] px-2.5 py-1 text-xs font-semibold text-[#2C2523]">
                                    {{ $vendor->categoryLabel() }}
                                </span>
                                <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $vendor->is_pinterest_authorized ? 'bg-[#EBF3F0] text-[#5A8F7B]' : 'bg-[#F9EBE6] text-[#C25E38]' }}">
                                    {{ $vendor->is_pinterest_authorized ? 'Acesso autorizado pelo casal' : 'Acesso pausado pelo casal' }}
                                </span>
                                @if ($settings->paywall_pinterest_inspirations_enabled)
                                    <span class="inline-flex items-center rounded-lg bg-[#FFFFFF] border border-[#EAE0DA] px-2.5 py-1 text-xs font-medium text-[#6E625F]">
                                        {{ $vendor->has_paid_pinterest_unlock ? 'Desbloqueio pago ativo' : 'Paywall ativo para desbloqueio' }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="font-serif text-base font-semibold text-[#2C2523]">
                                {{ $vendor->vendor_name }}
                            </h3>

                            <p class="text-xs font-mono text-[#6E625F] truncate">
                                {{ $vendor->inviteUrl() }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <a href="{{ $vendor->inviteUrl() }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] px-4 py-2 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors">
                                Abrir Link
                            </a>

                            <button type="button"
                                    wire:click="toggleVendorPinterestAuthorization({{ $vendor->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleVendorPinterestAuthorization({{ $vendor->id }})"
                                    class="inline-flex items-center justify-center rounded-xl px-4 py-2 min-h-[44px] text-xs font-semibold transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait {{ $vendor->is_pinterest_authorized ? 'border border-[#EAE0DA] bg-[#FFFFFF] text-[#6E625F] hover:border-[#C25E38] hover:text-[#C25E38]' : 'bg-[#C25E38] text-white hover:bg-[#A84D2B]' }}">
                                {{ $vendor->is_pinterest_authorized ? 'Pausar Acesso' : 'Autorizar Acesso' }}
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Chrome Extension MV3 & API Token Instructions --}}
    <section id="extensao-chrome" class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#C25E38]">
                    Captura em 1 Clique • Extensão Chrome MV3 & Celular PWA
                </p>
                <h2 class="font-serif text-xl font-semibold text-[#2C2523]">
                    Seu Token de API e Instruções da Extensão
                </h2>
                <p class="text-sm text-[#6E625F]">
                    Configure este token na extensão do Google Chrome (<code class="font-mono text-xs text-[#2C2523]">chrome-extension/</code>) para salvar pins direto de <code class="font-mono text-xs text-[#2C2523]">pinterest.com</code> e <code class="font-mono text-xs text-[#2C2523]">pinterest.com.br</code>.
                </p>
            </div>

            <button type="button"
                    wire:click="regenerateToken"
                    wire:loading.attr="disabled"
                    wire:target="regenerateToken"
                    class="inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors cursor-pointer shrink-0 disabled:opacity-60 disabled:cursor-wait">
                <span wire:loading.remove wire:target="regenerateToken">Gerar Novo Token</span>
                <span wire:loading wire:target="regenerateToken">Gerando...</span>
            </button>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
            <div class="flex-1 rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-3 font-mono text-xs sm:text-sm text-[#2C2523] break-all select-all">
                {{ $user->api_token }}
            </div>

            <button type="button"
                    x-data="{ token: @js($user->api_token) }"
                    @click="
                        (async () => {
                            try {
                                if (navigator.clipboard && window.isSecureContext) {
                                    await navigator.clipboard.writeText(token);
                                } else {
                                    const el = document.createElement('textarea');
                                    el.value = token;
                                    el.style.position = 'fixed';
                                    el.style.opacity = '0';
                                    document.body.appendChild(el);
                                    el.select();
                                    document.execCommand('copy');
                                    document.body.removeChild(el);
                                }
                            } catch (e) {}
                            window.dispatchEvent(new CustomEvent('client-toast', { detail: { message: 'Token de API copiado para a área de transferência.' } }));
                        })()
                    "
                    class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer shrink-0">
                Copiar Token
            </button>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] p-4 space-y-2">
                <h3 class="font-serif text-base font-semibold text-[#2C2523]">
                    Como usar a Extensão Chrome (Manifest V3)
                </h3>
                <ol class="list-decimal list-inside space-y-1.5 text-sm text-[#6E625F]">
                    <li>Abra <code class="font-mono text-xs text-[#2C2523]">chrome://extensions</code> no Google Chrome e ative o <strong class="font-medium text-[#2C2523]">Modo do desenvolvedor</strong>.</li>
                    <li>Clique em <strong class="font-medium text-[#2C2523]">Carregar sem compactação</strong> e selecione a pasta <code class="font-mono text-xs text-[#2C2523]">chrome-extension/</code>.</li>
                    <li>No popup da extensão, cole seu <code class="font-mono text-xs text-[#2C2523]">api_token</code> acima e informe a URL do servidor (<code class="font-mono text-xs text-[#2C2523]">{{ url('/') }}</code>).</li>
                    <li>Navegue pelo Pinterest e clique no botão flutuante <strong class="font-medium text-[#2C2523]">"Salvar no Nosso Voto"</strong> sobre qualquer Pin para enviar via <code class="font-mono text-xs text-[#2C2523]">POST /api/v1/inspirations/clip</code>.</li>
                </ol>
            </div>

            <div class="rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] p-4 space-y-2">
                <h3 class="font-serif text-base font-semibold text-[#2C2523]">
                    Como salvar pelo app Pinterest no Celular (PWA)
                </h3>
                <ol class="list-decimal list-inside space-y-1.5 text-sm text-[#6E625F]">
                    <li>Instale o <strong class="font-medium text-[#2C2523]">Nosso Voto</strong> na tela inicial pelo navegador do celular (PWA).</li>
                    <li>No app oficial do Pinterest, toque em <strong class="font-medium text-[#2C2523]">Compartilhar</strong> em qualquer Pin ou Pasta.</li>
                    <li>Escolha <strong class="font-medium text-[#2C2523]">Nosso Voto</strong> para abrir <code class="font-mono text-xs text-[#2C2523]">/compartilhar-pin</code> com o link já extraído.</li>
                    <li>Toque na categoria desejada (<strong class="font-medium text-[#2C2523]">Decoração</strong>, <strong class="font-medium text-[#2C2523]">Maquiagem</strong> ou <strong class="font-medium text-[#2C2523]">Penteado</strong>) para salvar em 1 toque.</li>
                </ol>
            </div>
        </div>
    </section>
</div>

