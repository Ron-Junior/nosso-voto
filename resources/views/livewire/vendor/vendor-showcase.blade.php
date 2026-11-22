<div class="space-y-6"
     x-data="{
         citiesMap: @js($citiesByState),
         syncCityOptions(selectEl, uf, preferredCity = '') {
             if (!selectEl) return;
             const normalizedUf = String(uf || '').trim().toUpperCase();
             const cities = this.citiesMap[normalizedUf] || [];
             const currentCity = preferredCity !== '' ? preferredCity : ($wire.cityFilter || '');
             selectEl.innerHTML = '';
             const placeholder = document.createElement('option');
             placeholder.value = '';
             placeholder.textContent = cities.length === 0 ? 'Todas as cidades' : 'Todas as cidades da UF';
             selectEl.appendChild(placeholder);
             for (const cityName of cities) {
                 const opt = document.createElement('option');
                 opt.value = cityName;
                 opt.textContent = cityName;
                 selectEl.appendChild(opt);
             }
             selectEl.disabled = cities.length === 0;
             if (cities.includes(currentCity)) {
                 selectEl.value = currentCity;
                 $wire.cityFilter = currentCity;
             } else {
                 selectEl.value = '';
                 $wire.cityFilter = '';
             }
         }
     }"
     @keydown.escape.window="if ($wire.selectedVendorId !== null) { $wire.closeVendorDetails(); }">
    <x-toast :message="$statusMessage" />

    {{-- Header & Regional Filters --}}
    <section class="rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-sm space-y-5">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="space-y-1.5">
                <span class="inline-flex items-center rounded-full bg-[#F9EBE6] px-3 py-1 text-xs font-semibold text-[#C25E38]">
                    Vitrine Regional
                </span>
                <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-[#2C2523]">
                    Fornecedores para o seu casamento
                </h1>
                <p class="text-sm text-[#6E625F] max-w-2xl">
                    Explore profissionais por cidade, estado e área de atuação. Destaques regionais são exibidos em ordem aleatória justa a cada visita.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                @if ($preferredRegionState !== null && $preferredRegionCity !== null)
                    <button type="button"
                            wire:click="applyCoupleRegion"
                            class="inline-flex items-center justify-center rounded-xl border border-[#C25E38] bg-[#F9EBE6] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-[#C25E38] hover:bg-[#C25E38] hover:text-white transition-colors cursor-pointer">
                        Filtrar por {{ $preferredRegionCity }} - {{ $preferredRegionState }}
                    </button>
                @endif

                @if ($stateFilter !== '' || $cityFilter !== '' || $categoryFilter !== '' || filled($search))
                    <button type="button"
                            wire:click="clearFilters"
                            class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm font-medium text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer">
                        Limpar filtros
                    </button>
                @endif
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 pt-2 border-t border-[#EAE0DA]">
            <div>
                <label for="showcase_state" class="block text-xs font-semibold uppercase tracking-wider text-[#6E625F] mb-1.5">
                    Estado (UF)
                </label>
                <select id="showcase_state"
                        wire:model.live="stateFilter"
                        @change="syncCityOptions($refs.showcaseCitySelect, $el.value)"
                        class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                    <option value="">Todos os estados</option>
                    @foreach ($states as $uf => $stateName)
                        <option value="{{ $uf }}">{{ $uf }} — {{ $stateName }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="showcase_city" class="block text-xs font-semibold uppercase tracking-wider text-[#6E625F] mb-1.5">
                    Cidade
                </label>
                <select id="showcase_city"
                        x-ref="showcaseCitySelect"
                        wire:model.live="cityFilter"
                        @disabled(empty($citiesForSelectedState))
                        class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed">
                    <option value="">
                        {{ empty($citiesForSelectedState) ? 'Todas as cidades' : 'Todas as cidades da UF' }}
                    </option>
                    @foreach ($citiesForSelectedState as $cityOption)
                        <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="showcase_category" class="block text-xs font-semibold uppercase tracking-wider text-[#6E625F] mb-1.5">
                    Categoria / Área
                </label>
                <select id="showcase_category"
                        wire:model.live="categoryFilter"
                        class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
                    <option value="">Todas as áreas</option>
                    @foreach ($categoryLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="showcase_search" class="block text-xs font-semibold uppercase tracking-wider text-[#6E625F] mb-1.5">
                    Buscar profissional
                </label>
                <input id="showcase_search"
                       type="search"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Nome, estilo ou cidade..."
                       class="w-full rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2.5 min-h-[44px] text-sm text-[#2C2523] focus:border-[#C25E38] focus:bg-[#FFFFFF] focus:outline-none">
            </div>
        </div>
    </section>

    {{-- Banner Principal por Ciclo (Até 5 Vagas) --}}
    @if ($activeBannerSubmissions->isNotEmpty())
        @php
            $bannerCycle = $activeBannerSubmissions->first()->bannerCycle;
        @endphp
        <section class="rounded-2xl border border-[#C25E38]/30 bg-gradient-to-br from-[#F9EBE6]/70 via-[#FFFFFF] to-[#FBF9F7] p-5 sm:p-6 shadow-sm space-y-4"
                 aria-label="Banner principal regional"
                 x-data="{
                     activeSlide: 0,
                     total: {{ $activeBannerSubmissions->count() }},
                     prev() { this.activeSlide = (this.activeSlide - 1 + this.total) % this.total; },
                     next() { this.activeSlide = (this.activeSlide + 1) % this.total; }
                 }">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="space-y-0.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-[#C25E38] px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-white">
                            Banner Principal
                        </span>
                        <span class="text-xs font-semibold text-[#C25E38] tabular-nums">
                            {{ $activeBannerSubmissions->count() }} de {{ \App\Models\BannerCycle::HARD_MAX_SLOTS }} vagas em destaque
                        </span>
                    </div>
                    <h2 class="font-serif text-xl font-semibold text-[#2C2523]">
                        {{ $bannerCycle?->title ?? 'Destaques do Ciclo Atual' }}
                    </h2>
                    @if ($bannerCycle !== null)
                        <p class="text-xs text-[#6E625F]">
                            {{ $bannerCycle->city }} - {{ $bannerCycle->state }} • Ciclo vigente ({{ $bannerCycle->periodLabel() }})
                        </p>
                    @endif
                </div>

                @if ($activeBannerSubmissions->count() > 1)
                    <div class="flex items-center gap-2 sm:hidden">
                        <button type="button"
                                @click="prev()"
                                aria-label="Slide anterior"
                                class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] text-[#2C2523] cursor-pointer">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <span class="text-xs font-medium text-[#6E625F] tabular-nums" x-text="`${activeSlide + 1} / ${total}`"></span>
                        <button type="button"
                                @click="next()"
                                aria-label="Próximo slide"
                                class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FFFFFF] text-[#2C2523] cursor-pointer">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($activeBannerSubmissions as $index => $submission)
                    @php
                        $bannerVendor = $submission->vendorProfile;
                        $isBannerVendorLinked = $bannerVendor !== null && in_array($bannerVendor->id, $linkedVendorProfileIds, true);
                    @endphp
                    @if ($bannerVendor !== null)
                        <article wire:key="banner-slot-{{ $submission->id }}"
                                 :class="activeSlide === {{ $index }} ? 'block' : 'hidden sm:flex'"
                                 class="flex flex-col justify-between rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-4 shadow-sm space-y-3">
                            <div class="space-y-2.5">
                                @if (filled($submission->banner_image_url))
                                    <div class="overflow-hidden rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] aspect-[16/9]">
                                        <img src="{{ $submission->banner_image_url }}"
                                             alt="{{ $bannerVendor->business_name }}"
                                             class="h-full w-full object-cover"
                                             loading="lazy">
                                    </div>
                                @endif

                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center rounded-lg bg-[#F9EBE6] px-2.5 py-1 text-[11px] font-semibold text-[#C25E38] tabular-nums">
                                        Vaga #{{ $submission->slot_number ?? ($index + 1) }}
                                    </span>
                                    <span class="text-xs text-[#6E625F]">
                                        {{ $bannerVendor->city }} - {{ $bannerVendor->state }}
                                    </span>
                                </div>

                                <div>
                                    <h3 class="font-serif text-lg font-semibold text-[#2C2523]">
                                        {{ $bannerVendor->business_name }}
                                    </h3>
                                    <p class="mt-1 text-sm font-medium text-[#C25E38]">
                                        {{ $submission->headline }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($bannerVendor->categoryLabelsList() as $catSlug => $catLabel)
                                        <span class="inline-flex items-center rounded-lg bg-[#FBF9F7] border border-[#EAE0DA] px-2 py-0.5 text-[11px] font-medium text-[#2C2523]">
                                            {{ $catLabel }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="pt-2 border-t border-[#EAE0DA] flex flex-wrap gap-2">
                                @if ($canLinkVendors)
                                    @if ($isBannerVendorLinked)
                                        <span class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#EBF3F0] px-3 py-2 min-h-[44px] text-xs font-semibold text-[#5A8F7B]">
                                            Vinculado ao Casamento
                                        </span>
                                    @else
                                        <button type="button"
                                                wire:click="linkVendorToWedding({{ $bannerVendor->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="linkVendorToWedding({{ $bannerVendor->id }})"
                                                class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#C25E38] px-3 py-2 min-h-[44px] text-xs font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60">
                                            Vincular ao Meu Casamento
                                        </button>
                                    @endif
                                @endif

                                <button type="button"
                                        wire:click="openVendorDetails({{ $bannerVendor->id }})"
                                        class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer">
                                    Detalhes
                                </button>
                            </div>
                        </article>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    {{-- Regional Showcase List (Random Highlights on Top + Organic Vendors Below) --}}
    <section class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-serif text-xl font-semibold text-[#2C2523]">
                    Profissionais Encontrados
                </h2>
                <p class="text-sm text-[#6E625F] tabular-nums">
                    {{ $vendors->count() }} {{ $vendors->count() === 1 ? 'fornecedor disponível' : 'fornecedores disponíveis' }}
                    @if ($categoryFilter !== '' && isset($categoryLabels[$categoryFilter]))
                        na área de <strong class="text-[#2C2523]">{{ $categoryLabels[$categoryFilter] }}</strong>
                    @endif
                    @if ($stateFilter !== '')
                        em <strong class="text-[#2C2523]">{{ $cityFilter !== '' ? "{$cityFilter} - {$stateFilter}" : $stateFilter }}</strong>
                    @endif
                </p>
            </div>
        </div>

        @if ($vendors->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#EAE0DA] bg-[#FFFFFF] p-8 text-center space-y-3">
                <p class="font-serif text-lg font-semibold text-[#2C2523]">
                    Nenhum fornecedor encontrado para estes filtros
                </p>
                <p class="text-sm text-[#6E625F]">
                    Tente ampliar a busca removendo o filtro de cidade ou categoria.
                </p>
                @if ($stateFilter !== '' || $cityFilter !== '' || $categoryFilter !== '' || filled($search))
                    <button type="button"
                            wire:click="clearFilters"
                            class="inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-5 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer">
                        Mostrar Todos os Fornecedores
                    </button>
                @endif
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($vendors as $vendor)
                    @php
                        $isHighlighted = $vendor->hasActiveRegionalHighlight();
                        $isLinked = in_array($vendor->id, $linkedVendorProfileIds, true);
                        $whatsappUrl = $vendor->whatsappUrl();
                    @endphp

                    <article wire:key="showcase-vendor-{{ $vendor->id }}"
                             data-vendor-id="{{ $vendor->id }}"
                             data-highlight="{{ $isHighlighted ? 'true' : 'false' }}"
                             class="rounded-2xl border {{ $isHighlighted ? 'border-[#C25E38]/40 shadow-sm' : 'border-[#EAE0DA]' }} bg-[#FFFFFF] overflow-hidden flex flex-col justify-between">
                        <div>
                            @if (filled($vendor->cover_image_url))
                                <div class="aspect-[16/9] w-full bg-[#FBF9F7] border-b border-[#EAE0DA] overflow-hidden">
                                    <img src="{{ $vendor->cover_image_url }}"
                                         alt="{{ $vendor->business_name }}"
                                         class="h-full w-full object-cover"
                                         loading="lazy">
                                </div>
                            @endif

                            <div class="p-5 space-y-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-xs font-medium text-[#6E625F]">
                                        {{ $vendor->city }} - {{ $vendor->state }}
                                    </span>

                                    @if ($isHighlighted)
                                        <span class="inline-flex items-center rounded-full bg-[#F9EBE6] px-2.5 py-1 text-[11px] font-semibold text-[#C25E38]">
                                            Destaque Regional
                                        </span>
                                    @endif
                                </div>

                                <div class="space-y-1">
                                    <h3 class="font-serif text-lg font-semibold text-[#2C2523]">
                                        {{ $vendor->business_name }}
                                    </h3>

                                    @if ($vendor->price_from !== null)
                                        <p class="text-xs font-medium text-[#6E625F] tabular-nums">
                                            A partir de <strong class="text-[#2C2523]">R$ {{ number_format((float) $vendor->price_from, 2, ',', '.') }}</strong>
                                        </p>
                                    @endif
                                </div>

                                {{-- All Areas / Categories --}}
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($vendor->categoryLabelsList() as $catSlug => $catLabel)
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[11px] font-semibold {{ $catSlug === $vendor->category ? 'bg-[#F9EBE6] text-[#C25E38]' : 'bg-[#FBF9F7] border border-[#EAE0DA] text-[#6E625F]' }}">
                                            {{ $catLabel }}
                                        </span>
                                    @endforeach
                                </div>

                                @if (filled($vendor->description))
                                    <p class="text-sm text-[#6E625F] leading-relaxed line-clamp-3">
                                        {{ $vendor->description }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="px-5 pb-5 pt-3 border-t border-[#EAE0DA] space-y-2.5">
                            @if ($canLinkVendors)
                                @if ($isLinked)
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#EBF3F0] px-3 py-2.5 min-h-[44px] text-xs font-semibold text-[#5A8F7B]">
                                            Vinculado ao Casamento
                                        </span>
                                        <a href="{{ route('dashboard') }}"
                                           wire:navigate
                                           class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2.5 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors">
                                            Ver no Painel
                                        </a>
                                    </div>
                                @else
                                    <button type="button"
                                            wire:click="linkVendorToWedding({{ $vendor->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="linkVendorToWedding({{ $vendor->id }})"
                                            class="w-full inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                                        <span wire:loading.remove wire:target="linkVendorToWedding({{ $vendor->id }})">Vincular ao Meu Casamento</span>
                                        <span wire:loading wire:target="linkVendorToWedding({{ $vendor->id }})">Vinculando...</span>
                                    </button>
                                @endif
                            @elseif ($user === null)
                                <a href="{{ route('login') }}"
                                   class="w-full inline-flex items-center justify-center rounded-xl bg-[#C25E38] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors">
                                    Entrar para Vincular
                                </a>
                            @endif

                            <div class="flex flex-wrap gap-2">
                                <button type="button"
                                        wire:click="openVendorDetails({{ $vendor->id }})"
                                        class="flex-1 inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#2C2523] hover:border-[#C25E38] hover:text-[#C25E38] transition-colors cursor-pointer">
                                    Ver Perfil
                                </button>

                                @if ($whatsappUrl !== null)
                                    <a href="{{ $whatsappUrl }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center justify-center rounded-xl border border-[#5A8F7B]/30 bg-[#EBF3F0] px-3.5 py-2 min-h-[44px] text-xs font-semibold text-[#5A8F7B] hover:bg-[#5A8F7B] hover:text-white transition-colors">
                                        WhatsApp
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Vendor Detail Modal (or /vitrine/{slug}) --}}
    @if ($selectedVendor !== null)
        @php
            $isSelectedLinked = in_array($selectedVendor->id, $linkedVendorProfileIds, true);
            $selectedWhatsappUrl = $selectedVendor->whatsappUrl();
        @endphp
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-[#2C2523]/50 p-0 sm:p-4"
             role="dialog"
             aria-modal="true"
             aria-labelledby="showcase-vendor-modal-title"
             @click.self="$wire.closeVendorDetails()">
            <div class="w-full sm:max-w-xl max-h-[90vh] overflow-y-auto rounded-t-3xl sm:rounded-2xl border border-[#EAE0DA] bg-[#FFFFFF] p-6 shadow-xl space-y-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        @if ($selectedVendor->hasActiveRegionalHighlight())
                            <span class="inline-flex items-center rounded-full bg-[#F9EBE6] px-2.5 py-0.5 text-[11px] font-semibold text-[#C25E38]">
                                Destaque Regional
                            </span>
                        @endif
                        <h2 id="showcase-vendor-modal-title" class="font-serif text-2xl font-semibold text-[#2C2523]">
                            {{ $selectedVendor->business_name }}
                        </h2>
                        <p class="text-xs text-[#6E625F]">
                            {{ $selectedVendor->city }} - {{ $selectedVendor->state }}
                            @if (filled($selectedVendor->instagram))
                                • {{ $selectedVendor->instagram }}
                            @endif
                        </p>
                    </div>

                    <button type="button"
                            wire:click="closeVendorDetails"
                            aria-label="Fechar detalhes"
                            class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-[#EAE0DA] text-[#6E625F] hover:text-[#2C2523] cursor-pointer shrink-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @if (filled($selectedVendor->cover_image_url))
                    <div class="overflow-hidden rounded-2xl border border-[#EAE0DA] bg-[#FBF9F7] aspect-[16/9]">
                        <img src="{{ $selectedVendor->cover_image_url }}"
                             alt="{{ $selectedVendor->business_name }}"
                             class="h-full w-full object-cover">
                    </div>
                @endif

                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Áreas de Atuação</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($selectedVendor->categoryLabelsList() as $catSlug => $catLabel)
                            <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $catSlug === $selectedVendor->category ? 'bg-[#F9EBE6] text-[#C25E38]' : 'bg-[#FBF9F7] border border-[#EAE0DA] text-[#2C2523]' }}">
                                {{ $catLabel }}
                            </span>
                        @endforeach
                    </div>
                </div>

                @if ($selectedVendor->price_from !== null)
                    <div class="rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] p-4 flex items-center justify-between">
                        <span class="text-sm text-[#6E625F]">Pacotes a partir de</span>
                        <span class="font-serif text-lg font-semibold tabular-nums text-[#2C2523]">
                            R$ {{ number_format((float) $selectedVendor->price_from, 2, ',', '.') }}
                        </span>
                    </div>
                @endif

                @if (filled($selectedVendor->description))
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#6E625F]">Sobre o Trabalho</p>
                        <p class="text-sm text-[#2C2523] leading-relaxed">
                            {{ $selectedVendor->description }}
                        </p>
                    </div>
                @endif

                <div class="pt-2 border-t border-[#EAE0DA] flex flex-col sm:flex-row gap-2.5">
                    @if ($canLinkVendors)
                        @if ($isSelectedLinked)
                            <span class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#EBF3F0] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-[#5A8F7B]">
                                Vinculado ao Casamento
                            </span>
                        @else
                            <button type="button"
                                    wire:click="linkVendorToWedding({{ $selectedVendor->id }})"
                                    class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#C25E38] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-white hover:bg-[#A84D2B] transition-colors cursor-pointer">
                                Vincular ao Meu Casamento
                            </button>
                        @endif
                    @endif

                    @if ($selectedWhatsappUrl !== null)
                        <a href="{{ $selectedWhatsappUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center justify-center rounded-xl border border-[#5A8F7B]/30 bg-[#EBF3F0] px-4 py-2.5 min-h-[44px] text-sm font-semibold text-[#5A8F7B] hover:bg-[#5A8F7B] hover:text-white transition-colors">
                            Falar no WhatsApp
                        </a>
                    @endif

                    <button type="button"
                            wire:click="closeVendorDetails"
                            class="inline-flex items-center justify-center rounded-xl border border-[#EAE0DA] bg-[#FBF9F7] px-4 py-2.5 min-h-[44px] text-sm font-medium text-[#2C2523] cursor-pointer">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

